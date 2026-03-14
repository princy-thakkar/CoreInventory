<?php
require '../config/db.php'; // adjust path

if(!isset($_GET['term'])) exit;

$term = "%".trim($_GET['term'])."%";
$stmt = $conn->prepare("SELECT name, sku, stock FROM products WHERE name LIKE ? OR sku LIKE ? LIMIT 10");
$stmt->execute([$term,$term]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);