<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* GET RECEIPT ID */
if(!isset($_GET['id'])){
    header("Location: receipts.php");
    exit();
}

$receipt_id = intval($_GET['id']);

/* FETCH RECEIPT INFO */
$stmt = $conn->prepare("
    SELECT r.id, r.date, r.status, s.name AS supplier
    FROM receipts r
    JOIN suppliers s ON s.id=r.supplier_id
    WHERE r.id=?
");
$stmt->execute([$receipt_id]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$receipt){
    die("Receipt not found!");
}

/* FETCH RECEIPT ITEMS */
$stmt_items = $conn->prepare("
    SELECT p.name, p.sku, p.unit, ri.quantity
    FROM receipt_items ri
    JOIN products p ON p.id=ri.product_id
    WHERE ri.receipt_id=?
");
$stmt_items->execute([$receipt_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>View Receipt - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
table{width:100%;border-collapse:collapse;background:white;box-shadow:0 6px 15px rgba(0,0,0,0.1);}
table th{background:#111827;color:white;padding:12px;text-align:center;}
table td{padding:12px;border-bottom:1px solid #eee;text-align:center;}
</style>
</head>
<body>

<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php">Dashboard</a>
<a href="../categories/categories.php">Categories</a>
<a href="../products/products.php">Products</a>
<a href="receipts.php" class="active">Receipts</a>
<a href="../operations/delivery.php">Delivery Orders</a>
<a href="../operations/transfers.php">Internal Transfers</a>
<a href="../operations/adjustments.php">Stock Adjustment</a>
<a href="../settings/warehouse.php">Warehouse</a>
<a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
<h1>Receipt Details</h1>
<br>

<p><strong>Receipt ID:</strong> <?php echo $receipt['id']; ?></p>
<p><strong>Supplier:</strong> <?php echo htmlspecialchars($receipt['supplier']); ?></p>
<p><strong>Date:</strong> <?php echo $receipt['date']; ?></p>
<p><strong>Status:</strong> <?php echo $receipt['status']; ?></p>
<br>

<h2>Products in Receipt</h2>
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>SKU</th>
<th>Unit</th>
<th>Quantity</th>
</tr>

<?php if(!empty($items)): ?>
    <?php foreach($items as $index => $item): ?>
        <tr>
            <td><?php echo $index+1; ?></td>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td><?php echo htmlspecialchars($item['sku']); ?></td>
            <td><?php echo htmlspecialchars($item['unit']); ?></td>
            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="5">No products found in this receipt.</td></tr>
<?php endif; ?>
</table>

<br>
<a href="receipts.php">⬅ Back to Receipts</a>
</div>
</body>
</html>