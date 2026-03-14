<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* DELETE RECEIPT */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->prepare("DELETE FROM receipt_items WHERE receipt_id=?")->execute([$id]);
    $conn->prepare("DELETE FROM receipts WHERE id=?")->execute([$id]);
    header("Location: receipts.php");
    exit();
}

/* FETCH RECEIPTS WITH SUPPLIER AND TOTAL PRODUCTS */
$stmt = $conn->prepare("
    SELECT r.id, r.date, r.status, s.name AS supplier,
           (SELECT COUNT(*) FROM receipt_items ri WHERE ri.receipt_id=r.id) AS total_products
    FROM receipts r
    JOIN suppliers s ON s.id=r.supplier_id
    ORDER BY r.id DESC
");
$stmt->execute();
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipts - CoreInventory</title>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Reset & font */
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}

/* Layout */
body{display:flex;background:#f4f6f9;}

/* Sidebar */
.sidebar{
    width:230px;height:100vh;background:#1f2937;color:white;padding:25px;
}
.sidebar h2{margin-bottom:40px;}
.sidebar a{
    display:flex;align-items:center;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;
    transition: all 0.3s ease;
}
.sidebar a i{margin-right:10px;}
.sidebar a:hover, .sidebar a.active{
    background:#3b82f6; /* blue */
    transform: translateX(5px);
}

/* Main content */
.main{
    flex:1;
    padding:30px;
    animation: fadeIn 0.8s ease-in-out;
}
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

/* Add Button */
.add-btn{
    display:inline-block;
    background:#3b82f6;
    color:white;
    padding:8px 15px;
    border-radius:6px;
    text-decoration:none;
    margin-bottom:15px;
    transition: background 0.3s;
}
.add-btn:hover{background:#1e40af;}

/* Table */
table{
    width:100%;
    border-collapse:collapse;
    background:white;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
}
table th{
    background:#111827;color:white;padding:12px;text-align:center;
}
table td{
    padding:12px;border-bottom:1px solid #eee;text-align:center;
    transition: background 0.3s, transform 0.3s;
}
table tr:hover td{
    background:#f3f4f6;
    transform: scale(1.02);
}

/* Action links */
.view{color:#3b82f6;font-weight:bold;text-decoration:none;transition: color 0.3s;}
.view:hover{color:#1e40af;}
.delete{color:#ef4444;font-weight:bold;text-decoration:none;transition: color 0.3s;}
.delete:hover{color:#b91c1c;}
.validate{color:#10b981;font-weight:bold;text-decoration:none;transition: color 0.3s;}
.validate:hover{color:#047857;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
<a href="../categories/categories.php"><i class="fa-solid fa-tags"></i> Categories</a>
<a href="../products/products.php"><i class="fa-solid fa-boxes-stacked"></i> Products</a>
<a href="receipts.php" class="active"><i class="fa-solid fa-receipt"></i> Receipts</a>
<a href="../operations/delivery.php"><i class="fa-solid fa-truck"></i> Delivery Orders</a>
<a href="../operations/transfers.php"><i class="fa-solid fa-exchange-alt"></i> Internal Transfers</a>
<a href="../operations/adjustments.php"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a>
<a href="../operations/stock_ledger.php"><i class="fa-solid fa-book"></i> Stock Ledger</a>
<a href="../settings/warehouse.php"><i class="fa-solid fa-warehouse"></i> Warehouse</a>
<a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
<h1>Receipts</h1>
<a href="add_receipt.php" class="add-btn">➕ Add New Receipt</a>

<table>
<tr>
<th>ID</th>
<th>Supplier</th>
<th>Date</th>
<th>Status</th>
<th>Total Products</th>
<th>Action</th>
</tr>

<?php if(!empty($receipts)): ?>
    <?php foreach($receipts as $r): ?>
    <tr>
        <td><?php echo $r['id']; ?></td>
        <td><?php echo htmlspecialchars($r['supplier']); ?></td>
        <td><?php echo $r['date']; ?></td>
        <td><?php echo $r['status']; ?></td>
        <td><?php echo $r['total_products']; ?></td>
        <td>
            <a class="view" href="view_receipt.php?id=<?php echo $r['id']; ?>">View</a> |
            <?php if($r['status']=='Pending'): ?>
                <a class="validate" href="validate_receipt.php?id=<?php echo $r['id']; ?>">Validate</a> |
            <?php endif; ?>
            <a class="delete" href="?delete=<?php echo $r['id']; ?>" onclick="return confirm('Delete this receipt?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="6">No receipts found.</td></tr>
<?php endif; ?>
</table>
</div>

</body>
</html>