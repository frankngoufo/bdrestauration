<?php
require "db.php";

$dbname = $_POST['dbname'];

$sql = "SHOW TABLES FROM `$dbname`";
$res = $conn->query($sql);

while ($row = $res->fetch_array()) {
    echo "<option value='{$row[0]}'>{$row[0]}</option>";
}
?>
