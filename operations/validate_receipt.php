<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Get receipt ID from URL */
$receipt_id = intval($_GET['id'] ?? 0);

if(!$receipt_id){
    header("Location: receipts.php");
    exit();
}

/* Fetch receipt info */
$stmt = $conn->prepare("SELECT r.id, r.status, r.date, s.name AS supplier_name 
                        FROM receipts r 
                        JOIN suppliers s ON r.supplier_id = s.id 
                        WHERE r.id=?");
$stmt->execute([$receipt_id]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$receipt){
    header("Location: receipts.php");
    exit();
}

/* Fetch products in this receipt */
$items = $conn->prepare("SELECT ri.id, ri.product_id, ri.quantity, p.name, p.sku 
                         FROM receipt_items ri
                         JOIN products p ON ri.product_id = p.id
                         WHERE ri.receipt_id=?");
$items->execute([$receipt_id]);
$products = $items->fetchAll(PDO::FETCH_ASSOC);

/* Validate Receipt */
if(isset($_POST['validate'])){
    if($receipt['status'] == 'Pending'){
        // Update receipt status
        $conn->prepare("UPDATE receipts SET status='Done' WHERE id=?")->execute([$receipt_id]);
        
        // Update stock for each product
        foreach($products as $p){
            $conn->prepare("UPDATE products SET stock = stock + ? WHERE id=?")
                 ->execute([$p['quantity'], $p['product_id']]);
        }
        $success = "Receipt validated successfully and stock updated!";
        $receipt['status'] = 'Done';
    } else {
        $error = "Receipt has already been validated!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Validate Receipt - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
.table{width:100%;border-collapse:collapse;margin-top:20px;}
.table th, .table td{padding:10px;border:1px solid #ddd;text-align:left;}
button{padding:10px 15px;border:none;background:#10b981;color:white;border-radius:6px;cursor:pointer;}
button:hover{background:#059669;}
.error{background:#ffe5e5;color:#c00;padding:10px;margin-bottom:15px;border-radius:6px;}
.success{background:#e5ffe7;color:#0a7a25;padding:10px;margin-bottom:15px;border-radius:6px;}
</style>
</head>
<body>

<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php">Dashboard</a>
<a href="receipts.php" class="active">Receipts</a>
<a href="../products/products.php">Products</a>
<a href="../operations/delivery.php">Delivery Orders</a>
<a href="../operations/transfers.php">Internal Transfers</a>
<a href="../operations/adjustments.php">Stock Adjustment</a>
<a href="../settings/warehouse.php">Warehouse</a>
<a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
<h1>Validate Receipt</h1>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<p><strong>Receipt ID:</strong> <?php echo $receipt['id']; ?></p>
<p><strong>Supplier:</strong> <?php echo htmlspecialchars($receipt['supplier_name']); ?></p>
<p><strong>Status:</strong> <?php echo $receipt['status']; ?></p>
<p><strong>Date:</strong> <?php echo $receipt['date']; ?></p>

<h2>Products</h2>
<table class="table">
<tr>

<th>Product ID</th>
<th>SKU</th>
<th>Name</th>
<th>Quantity</th>
</tr>
<?php foreach($products as $i => $p): ?>
<tr>

<td><?php echo $p['product_id']; ?></td>
<td><?php echo htmlspecialchars($p['sku']); ?></td>
<td><?php echo htmlspecialchars($p['name']); ?></td>
<td><?php echo $p['quantity']; ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php if($receipt['status'] == 'Pending'): ?>
<form method="POST" style="margin-top:20px;">
<button name="validate">✅ Validate Receipt & Update Stock</button>
</form>
<?php endif; ?>

<br>
<a href="receipts.php">⬅ Back to Receipts</a>
</div>

</body>
</html>