<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Get delivery ID */
if(!isset($_GET['id'])){
    header("Location: delivery.php");
    exit();
}

$delivery_id = intval($_GET['id']);

/* Fetch delivery */
$stmt = $conn->prepare("
    SELECT *
    FROM deliveries
    WHERE id = ?
");
$stmt->execute([$delivery_id]);
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$delivery){
    die("Delivery not found!");
}

/* Fetch delivery items with product info */
$products = $conn->prepare("
    SELECT di.product_id, di.quantity, p.name, p.sku
    FROM delivery_items di
    JOIN products p ON p.id = di.product_id
    WHERE di.delivery_id = ?
");
$products->execute([$delivery_id]);
$products = $products->fetchAll(PDO::FETCH_ASSOC);

/* Validate delivery */
if(isset($_POST['validate'])){
    if($delivery['status'] == 'Pending'){
        foreach($products as $p){
            $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")
                 ->execute([$p['quantity'], $p['product_id']]);
        }
        $conn->prepare("UPDATE deliveries SET status = 'Done' WHERE id = ?")
             ->execute([$delivery_id]);

        $success = "Delivery validated and stock updated successfully!";
        // Refresh delivery data
        $stmt->execute([$delivery_id]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Validate Delivery - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
.error{background:#ffe5e5;color:#c00;padding:10px;margin-bottom:15px;border-radius:6px;}
.success{background:#e5ffe7;color:#0a7a25;padding:10px;margin-bottom:15px;border-radius:6px;}
.card{background:white;padding:20px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.1);margin-bottom:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
table th, table td{padding:12px;border:1px solid #ddd;text-align:center;}
table th{background:#111827;color:white;}
button{padding:10px 15px;border:none;border-radius:6px;background:#10b981;color:white;cursor:pointer;}
button:hover{background:#059669;}
</style>
</head>

<body>
<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php">Dashboard</a>
<a href="../categories/categories.php">Categories</a>
<a href="../products/products.php">Products</a>
<a href="receipts.php">Receipts</a>
<a href="delivery.php" class="active">Delivery Orders</a>
<a href="transfers.php">Internal Transfers</a>
<a href="adjustments.php">Stock Adjustment</a>
<a href="../settings/warehouse.php">Warehouse</a>
<a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
<h1>Validate Delivery</h1>

<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<div class="card">
<p><strong>Delivery ID:</strong> <?php echo $delivery['id']; ?></p>
<p><strong>Status:</strong> <?php echo $delivery['status']; ?></p>
<p><strong>Date:</strong> <?php echo $delivery['date']; ?></p>
</div>

<div class="card">
<h2>Products</h2>
<table>
<tr>
<th>#</th>
<th>Product ID</th>
<th>SKU</th>
<th>Name</th>
<th>Quantity</th>
</tr>
<?php foreach($products as $i => $p): ?>
<tr>
<td><?php echo $i+1; ?></td>
<td><?php echo $p['product_id']; ?></td>
<td><?php echo htmlspecialchars($p['sku']); ?></td>
<td><?php echo htmlspecialchars($p['name']); ?></td>
<td><?php echo $p['quantity']; ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php if($delivery['status'] == 'Pending'): ?>
<form method="POST">
<button name="validate">✅ Validate Delivery & Update Stock</button>
</form>
<?php endif; ?>

<br>
<a href="delivery.php">⬅ Back to Delivery Orders</a>
</div>
</body>
</html>