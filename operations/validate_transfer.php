<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$transfer_id = intval($_GET['id'] ?? 0);
if(!$transfer_id) die("Transfer ID missing");

/* Fetch transfer info */
$transfer = $conn->prepare("SELECT * FROM transfers WHERE id = ?");
$transfer->execute([$transfer_id]);
$transfer = $transfer->fetch(PDO::FETCH_ASSOC);
if(!$transfer) die("Transfer not found");
if($transfer['status'] == 'Done') die("This transfer is already validated");

/* Fetch products in this transfer */
$products = $conn->prepare("
    SELECT p.id AS product_id, p.name, p.sku, t.quantity
    FROM transfers t
    LEFT JOIN products p ON t.product_id = p.id
    WHERE t.id = ?
");
$products->execute([$transfer_id]);
$products = $products->fetchAll(PDO::FETCH_ASSOC);

/* Validate Transfer */
if(isset($_POST['validate'])){
    foreach($products as $p){
        $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")
             ->execute([$p['quantity'], $p['product_id']]);
    }
    $conn->prepare("UPDATE transfers SET status='Done' WHERE id=?")
         ->execute([$transfer_id]);
    header("Location: transfers.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Validate Transfer - CoreInventory</title>
<style>
body{font-family:Segoe UI;background:#f4f6f9;padding:30px;}
.container{background:white;padding:20px;border-radius:10px;box-shadow:0 6px 15px rgba(0,0,0,0.1);width:800px;margin:auto;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
table th, table td{padding:10px;border:1px solid #ddd;text-align:center;}
table th{background:#111827;color:white;}
button{padding:10px 15px;border:none;background:#10b981;color:white;border-radius:6px;cursor:pointer;}
</style>
</head>
<body>
<div class="container">
<h1>Validate Transfer</h1>
<p><strong>Transfer ID:</strong> <?php echo $transfer['id']; ?></p>
<p><strong>Status:</strong> <?php echo $transfer['status']; ?></p>
<p><strong>From Warehouse:</strong> <?php echo $transfer['from_warehouse']; ?></p>
<p><strong>To Warehouse:</strong> <?php echo $transfer['to_warehouse']; ?></p>
<p><strong>From Location:</strong> <?php echo htmlspecialchars($transfer['from_location']); ?></p>
<p><strong>To Location:</strong> <?php echo htmlspecialchars($transfer['to_location']); ?></p>

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
<form method="POST">
<button name="validate">✅ Validate Transfer & Update Stock</button>
</form>
<br>
<a href="transfers.php">⬅ Back to Transfers</a>
</div>
</body>
</html>