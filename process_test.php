<?php
require "db.php";

$dbname = $_POST['dbname'];
$tables = $_POST['tables']; // tableau de tables sélectionnées

$tableList = "'" . implode("','", $tables) . "'";

// Création table snapshot (sécurité)
$conn->query("
CREATE TABLE IF NOT EXISTS $dbname.table_size_snapshot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    db_type VARCHAR(20),
    table_name VARCHAR(100),
    table_rows BIGINT,
    size_mb DECIMAL(10,2),
    snapshot_date DATETIME DEFAULT CURRENT_TIMESTAMP
)
");

// PHASE 1 : SOURCE
$conn->query("
INSERT INTO $dbname.table_size_snapshot (db_type, table_name, table_rows, size_mb)
SELECT 'SOURCE', table_name, table_rows,
ROUND((data_length + index_length)/1024/1024,2)
FROM information_schema.tables
WHERE table_schema='$dbname'
AND table_name IN ($tableList)
");

// PHASE 2 : RESTORED
$conn->query("
INSERT INTO $dbname.table_size_snapshot (db_type, table_name, table_rows, size_mb)
SELECT 'RESTORED', table_name, table_rows,
ROUND((data_length + index_length)/1024/1024,2)
FROM information_schema.tables
WHERE table_schema='$dbname'
AND table_name IN ($tableList)
");

// PHASE 3 : COMPARAISON
$result = $conn->query("
SELECT 
    s.table_name,
    s.size_mb AS source_size,
    r.size_mb AS restored_size,
    CASE
        WHEN ABS(s.size_mb - r.size_mb) <= (s.size_mb * 0.02)
        THEN 'OK'
        ELSE 'NON OK'
    END AS statut
FROM $dbname.table_size_snapshot s
JOIN $dbname.table_size_snapshot r
ON s.table_name = r.table_name
WHERE s.db_type='SOURCE'
AND r.db_type='RESTORED'
ORDER BY s.table_name
");

// AFFICHAGE
echo "<table border='1' cellpadding='5'>
<tr>
<th>Table</th>
<th>Taille source (MB)</th>
<th>Taille restaurée (MB)</th>
<th>Statut</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['table_name']}</td>
        <td>{$row['source_size']}</td>
        <td>{$row['restored_size']}</td>
        <td><b>{$row['statut']}</b></td>
    </tr>";
}

echo "</table>";
?>
