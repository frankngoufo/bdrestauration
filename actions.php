<?php
require "db.php";

$action = $_POST['action'] ?? "";

if ($action == "tables") {

    $db = $_POST['db'];
    $res = $conn->query("SHOW TABLES FROM `$db`");
    while ($row = $res->fetch_array()) {
        echo "<option value='{$row[0]}'>{$row[0]}</option>";
    }
}

/* ================== SOURCE ================== */
if ($action == "source") {

    $db = $_POST['db'];
    $conn->select_db($db);

    $tables = $_POST['tables'];
    $list = "'" . implode("','", $tables) . "'";

    $sql = "
    SELECT 
        table_name AS table_name,
        table_rows AS table_rows,
        ROUND((data_length+index_length)/1024/1024,2) AS size_mb
    FROM information_schema.tables
    WHERE table_schema='$db'
    AND table_name IN ($list)
    ";

    $conn->query("DROP TABLE IF EXISTS temp_source");
    $conn->query("
        CREATE TABLE temp_source (
            table_name VARCHAR(255),
            table_rows BIGINT,
            size_mb DECIMAL(10,2)
        )
    ");

    $conn->query("INSERT INTO temp_source $sql");

    $res = $conn->query("SELECT * FROM temp_source");

    echo "<table border='1' cellpadding='5'>
    <tr><th>Table</th><th>Lignes</th><th>Taille (MB)</th></tr>";

    while ($r = $res->fetch_assoc()) {
        echo "<tr>
            <td>{$r['table_name']}</td>
            <td>{$r['table_rows']}</td>
            <td>{$r['size_mb']}</td>
        </tr>";
    }
    echo "</table>";
}



/* ================== RESTORED ================== */
if ($action == "restored") {

    if (empty($_POST['db'])) {
        die("Base RESTORED non fournie");
    }

    $db = $_POST['db'];
    $conn->select_db($db);

    $tables = $_POST['tables'];
    if (empty($tables)) {
        die("Aucune table sélectionnée");
    }

    $list = "'" . implode("','", $tables) . "'";

    $sql = "
    SELECT 
        table_name AS table_name,
        table_rows AS table_rows,
        ROUND((data_length+index_length)/1024/1024,2) AS size_mb
    FROM information_schema.tables
    WHERE table_schema='$db'
    AND table_name IN ($list)
    ";

    $conn->query("DROP TABLE IF EXISTS temp_restored");
    $conn->query("
        CREATE TABLE temp_restored (
            table_name VARCHAR(255),
            table_rows BIGINT,
            size_mb DECIMAL(10,2)
        )
    ");

    $conn->query("INSERT INTO temp_restored $sql");

    $res = $conn->query("SELECT * FROM temp_restored");

    echo "<table border='1' cellpadding='5'>
    <tr><th>Table</th><th>Lignes</th><th>Taille (MB)</th></tr>";

    while ($r = $res->fetch_assoc()) {
        echo "<tr>
            <td>{$r['table_name']}</td>
            <td>{$r['table_rows']}</td>
            <td>{$r['size_mb']}</td>
        </tr>";
    }
    echo "</table>";
}




/* ================== COMPARAISON ================== */
if ($action == "compare") {

    // Vérifie que la base SOURCE est fournie
    $db = $_POST['db_source'] ?? '';
    if (empty($db)) {
        die("Base SOURCE non fournie pour la comparaison");
    }

    $conn->select_db($db); // <<< important !
    
    $sql = "
    SELECT s.table_name,
           s.size_mb AS source_size,
           r.size_mb AS restored_size,
           CASE
             WHEN ABS(s.size_mb - r.size_mb) <= (s.size_mb * 0.02)
             THEN 'OK'
             ELSE 'NON OK'
           END AS statut
    FROM temp_source s
    JOIN temp_restored r ON s.table_name = r.table_name
    ";

    $res = $conn->query($sql);

    echo "<table border='1' cellpadding='5'>
    <tr>
        <th>Table</th>
        <th>Taille SOURCE</th>
        <th>Taille RESTORED</th>
        <th>Statut</th>
    </tr>";

    while ($r = $res->fetch_assoc()) {
        echo "<tr>
            <td>{$r['table_name']}</td>
            <td>{$r['source_size']}</td>
            <td>{$r['restored_size']}</td>
            <td><b>{$r['statut']}</b></td>
        </tr>";
    }
    echo "</table>";
}

?>
