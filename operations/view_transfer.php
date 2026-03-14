<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$transfer_id = intval($_GET['id'] ?? 0);
if(!$transfer_id) die("Transfer ID missing");

/* Fetch transfer with warehouse info */
$transfer = $conn->prepare("
    SELECT t.*, 
           w1.name AS from_warehouse_name,
           w2.name AS to_warehouse_name
    FROM transfers t
    LEFT JOIN warehouses w1 ON t.from_warehouse = w1.id
    LEFT JOIN warehouses w2 ON t.to_warehouse = w2.id
    WHERE t.id = ?
");
$transfer->execute([$transfer_id]);
$transfer = $transfer->fetch(PDO::FETCH_ASSOC);
if(!$transfer) die("Transfer not found");

/* Fetch all products in this transfer */
$products = $conn->prepare("
    SELECT p.name, p.sku, t.quantity
    FROM transfers t
    LEFT JOIN products p ON t.product_id = p.id
    WHERE t.id = ?
");
$products->execute([$transfer_id]);
$products = $products->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>View Transfer - CoreInventory</title>
<style>
body{font-family:Segoe UI;background:#f4f6f9;padding:30px;}
.container{background:white;padding:20px;border-radius:10px;box-shadow:0 6px 15px rgba(0,0,0,0.1);width:800px;margin:auto;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
table th, table td{padding:10px;border:1px solid #ddd;text-align:center;}
table th{background:#111827;color:white;}
</style>
</head>
<body>
<div class="container">
<h1>View Internal Transfer</h1>
<p><strong>Transfer ID:</strong> <?php echo $transfer['id']; ?></p>
<p><strong>Status:</strong> <?php echo $transfer['status']; ?></p>
<p><strong>From Warehouse:</strong> <?php echo $transfer['from_warehouse_name']; ?></p>
<p><strong>To Warehouse:</strong> <?php echo $transfer['to_warehouse_name']; ?></p>
<p><strong>From Location:</strong> <?php echo htmlspecialchars($transfer['from_location']); ?></p>
<p><strong>To Location:</strong> <?php echo htmlspecialchars($transfer['to_location']); ?></p>
<p><strong>Date:</strong> <?php echo $transfer['date']; ?></p>

<h3>Products</h3>
<table>
<tr>
<th>#</th>
<th>Name</th>
<th>SKU</th>
<th>Quantity</th>
</tr>
<?php foreach($products as $index => $p): ?>
<tr>
<td><?php echo $index+1; ?></td>
<td><?php echo htmlspecialchars($p['name']); ?></td>
<td><?php echo $p['sku']; ?></td>
<td><?php echo $p['quantity']; ?></td>
</tr>
<?php endforeach; ?>
</table>

<br>
<a href="transfers.php">⬅ Back to Transfers</a>
</div>
</body>
</html>