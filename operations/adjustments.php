<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch all adjustments with product & warehouse names */
$adjustments = $conn->query("
    SELECT a.*, p.name AS product_name, p.sku AS product_sku, w.name AS warehouse_name
    FROM adjustments a
    LEFT JOIN products p ON a.product_id = p.id
    LEFT JOIN warehouses w ON a.warehouse_id = w.id
    ORDER BY a.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock Adjustments - CoreInventory</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}

/* Body & Layout */
body{display:flex;background:#f4f6f9;}

/* Sidebar */
.sidebar{
    width:230px;height:100vh;background:#1f2937;color:white;padding:25px;
}
.sidebar h2{margin-bottom:40px;}
.sidebar a{
    display:flex;
    align-items:center;
    color:white;
    text-decoration:none;
    margin:12px 0;
    padding:10px;
    border-radius:6px;
    transition: all 0.3s ease;
}
.sidebar a i{margin-right:10px;}
.sidebar a:hover, .sidebar a.active{
    background:#3b82f6; /* blue hover */
    transform: translateX(5px);
}

/* Main Content */
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
.add-button{
    display:inline-block;
    background:#3b82f6;
    color:white;
    padding:8px 15px;
    border-radius:6px;
    text-decoration:none;
    margin-bottom:15px;
    transition: background 0.3s;
}
.add-button:hover{background:#1e40af;}

/* Table */
.table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:white;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
    table-layout:fixed;
}
.table th{
    background:#111827;
    color:white;
    padding:12px;
    text-align:center;
}
.table td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:center;
    transition: background 0.3s, transform 0.3s;
}
.table tr:hover td{
    background:#f3f4f6;
    transform: scale(1.02);
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
<a href="../categories/categories.php"><i class="fa-solid fa-tags"></i> Categories</a>
<a href="../products/products.php"><i class="fa-solid fa-boxes-stacked"></i> Products</a>
<a href="../operations/receipts.php"><i class="fa-solid fa-receipt"></i> Receipts</a>
<a href="../operations/delivery.php"><i class="fa-solid fa-truck"></i> Delivery Orders</a>
<a href="../operations/transfers.php"><i class="fa-solid fa-exchange-alt"></i> Internal Transfers</a>
<a href="../operations/adjustments.php" class="active"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a>
<a href="../operations/stock_ledger.php"><i class="fa-solid fa-book"></i> Stock Ledger</a>
<a href="../settings/warehouse.php"><i class="fa-solid fa-warehouse"></i> Warehouse</a>
<a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
<h1>Stock Adjustments</h1>
<a href="add_adjustment.php" class="add-button">➕ Add New Adjustment</a>

<table class="table">
<tr>
<th>ID</th>
<th>Product</th>
<th>SKU</th>
<th>Warehouse</th>
<th>Old Quantity</th>
<th>New Quantity</th>
<th>Reason</th>
<th>Date</th>
</tr>

<?php foreach($adjustments as $a): ?>
<tr>
<td><?php echo $a['id']; ?></td>
<td><?php echo htmlspecialchars($a['product_name']); ?></td>
<td><?php echo $a['product_sku']; ?></td>
<td><?php echo htmlspecialchars($a['warehouse_name']); ?></td>
<td><?php echo $a['old_quantity']; ?></td>
<td><?php echo $a['new_quantity']; ?></td>
<td><?php echo htmlspecialchars($a['reason']); ?></td>
<td><?php echo $a['date']; ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

</body>
</html>