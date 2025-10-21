<?php
require("config.php");
$department = $_GET['department'] ?? '';

try {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "SELECT id, firstName FROM users WHERE department = :department";
    $st = $conn->prepare($sql);
    $st->bindValue(":department", $department, PDO::PARAM_STR);
    $st->execute();
    $users = $st->fetchAll(PDO::FETCH_ASSOC);
    $conn = null;
    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode([]);
}
