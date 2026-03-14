<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch all stock ledger entries with product names */
$ledger = $conn->query("
    SELECT s.id, p.name AS product_name, p.sku, s.type, s.quantity, s.reference_id, s.created_at
    FROM stock_ledger s
    LEFT JOIN products p ON s.product_id = p.id
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock Ledger - CoreInventory</title>

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
    background:#3b82f6; /* blue hover like dashboard */
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

/* Table */
.table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:white;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
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

/* Optional: add-btn style for future use */
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
<a href="../operations/adjustments.php"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a>
<a href="../operations/stock_ledger.php" class="active"><i class="fa-solid fa-book"></i> Stock Ledger</a>
<a href="../settings/warehouse.php"><i class="fa-solid fa-warehouse"></i> Warehouse</a>
<a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
<h1>Stock Ledger</h1>

<table class="table">
<tr>
<th>ID</th>
<th>Product</th>
<th>SKU</th>
<th>Type</th>
<th>Quantity</th>
<th>Reference ID</th>
<th>Date</th>
</tr>

<?php foreach($ledger as $l): ?>
<tr>
<td><?php echo $l['id']; ?></td>
<td><?php echo htmlspecialchars($l['product_name']); ?></td>
<td><?php echo $l['sku']; ?></td>
<td><?php echo $l['type']; ?></td>
<td><?php echo $l['quantity']; ?></td>
<td><?php echo $l['reference_id']; ?></td>
<td><?php echo $l['created_at']; ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

</body>
</html>