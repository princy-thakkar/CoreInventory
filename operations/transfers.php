<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch all transfers with warehouse info */
$transfers = $conn->query("
    SELECT t.id, t.from_warehouse, t.to_warehouse, t.status, t.date,
           w1.name AS from_name, w2.name AS to_name
    FROM transfers t
    JOIN warehouses w1 ON w1.id = t.from_warehouse
    JOIN warehouses w2 ON w2.id = t.to_warehouse
    ORDER BY t.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Internal Transfers - CoreInventory</title>

<!-- Font Awesome for icons -->
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
    background:#3b82f6; /* dashboard blue hover */
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

/* Action Buttons */
.button{
    padding:5px 10px;
    border:none;
    border-radius:5px;
    color:white;
    cursor:pointer;
    text-decoration:none;
    font-weight:bold;
    transition: all 0.3s;
}
.validate{background:#10b981;}
.validate:hover{background:#047857;}
.view{background:#3b82f6;}
.view:hover{background:#1e40af;}
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
<a href="../operations/transfers.php" class="active"><i class="fa-solid fa-exchange-alt"></i> Internal Transfers</a>
<a href="../operations/adjustments.php"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a>
<a href="../operations/stock_ledger.php"><i class="fa-solid fa-book"></i> Stock Ledger</a>
<a href="../settings/warehouse.php"><i class="fa-solid fa-warehouse"></i> Warehouse</a>
<a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
<h1>Internal Transfers</h1>
<a href="add_transfer.php" class="add-button">➕ Add New Transfer</a>

<table class="table">
<tr>
<th>ID</th>
<th>From Warehouse</th>
<th>To Warehouse</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php foreach($transfers as $t): ?>
<tr>
<td><?php echo $t['id']; ?></td>
<td><?php echo htmlspecialchars($t['from_name']); ?></td>
<td><?php echo htmlspecialchars($t['to_name']); ?></td>
<td><?php echo $t['status']; ?></td>
<td><?php echo $t['date']; ?></td>
<td>
    <?php if($t['status'] == 'Pending'): ?>
        <a class="button validate" href="validate_transfer.php?id=<?php echo $t['id']; ?>">Validate</a>
    <?php endif; ?>
    <a class="button view" href="view_transfer.php?id=<?php echo $t['id']; ?>">View</a>
</td>
</tr>
<?php endforeach; ?>

</table>
</div>
</body>
</html>