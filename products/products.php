<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* DELETE PRODUCT */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$id]);
    header("Location: products.php");
    exit();
}

/* FETCH PRODUCTS */
$stmt = $conn->prepare("SELECT id, name, sku, category, unit, stock FROM products ORDER BY id ASC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Products - CoreInventory</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;transition:0.3s;}

/* Sidebar */
.sidebar{
    width:230px;height:100vh;background:#1f2937;color:white;padding:25px;display:flex;flex-direction:column;
}
.sidebar h2{margin-bottom:40px;text-align:center;font-size:24px;}
.sidebar a{
    display:block;color:white;text-decoration:none;margin:12px 0;padding:12px;border-radius:8px;transition:0.3s;
}
.sidebar a:hover,.sidebar a.active{
    background: linear-gradient(90deg,#4f46e5,#6366f1);
    padding-left:25px;box-shadow:0 2px 8px rgba(0,0,0,0.2);
}

/* Main */
.main{flex:1;padding:30px;transition:0.3s;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.topbar h1{font-size:28px;color:#111827;}
.add-btn{
    display:inline-block;margin-bottom:20px;padding:10px 15px;background:#3b82f6;color:white;border-radius:8px;
    text-decoration:none;font-weight:bold;transition:0.3s;
}
.add-btn:hover{background:#2563eb;transform:translateY(-2px);box-shadow:0 4px 15px rgba(0,0,0,0.2);}

/* Table */
.table-container{overflow-x:auto;}
table{
    width:100%;border-collapse:collapse;background:white;box-shadow:0 8px 20px rgba(0,0,0,0.08);
    border-radius:10px;overflow:hidden;
}
table th{background:#111827;color:white;padding:12px;text-align:center;}
table td{padding:12px;border-bottom:1px solid #eee;text-align:center;transition:0.3s;}
table tr:hover td{background:#f0f4ff;}

/* Buttons */
.delete, .edit{
    padding:5px 10px;border-radius:6px;text-decoration:none;font-weight:bold;transition:0.3s;
}
.edit{background:#1d4ed8;color:white;}
.edit:hover{background:#2563eb;transform:translateY(-1px);}
.delete{background:#ef4444;color:white;}
.delete:hover{background:#dc2626;transform:translateY(-1px);}
</style>
</head>
<body>

<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
<a href="../categories/categories.php"><i class="fa-solid fa-tags"></i> Categories</a>
<a href="../products/products.php" class="active"><i class="fa-solid fa-boxes-stacked"></i> Products</a>
<a href="../operations/receipts.php"><i class="fa-solid fa-receipt"></i> Receipts</a>
<a href="../operations/delivery.php"><i class="fa-solid fa-truck"></i> Delivery Orders</a>
<a href="../operations/transfers.php"><i class="fa-solid fa-exchange-alt"></i> Internal Transfers</a>
<a href="../operations/adjustments.php"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a>
<a href="../operations/stock_ledger.php"><i class="fa-solid fa-book"></i> Stock Ledger</a>
<a href="../settings/warehouse.php"><i class="fa-solid fa-warehouse"></i> Warehouse</a>
<a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">
<div class="topbar">
    <h1>Products</h1>
    <div>Welcome, <strong><?php echo $_SESSION['user']; ?></strong></div>
</div>

<a class="add-btn" href="add_product.php">➕ Add New Product</a>

<div class="table-container">
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>SKU</th>
<th>Category</th>
<th>Unit</th>
<th>Stock</th>
<th>Action</th>
</tr>
<?php if(!empty($products)): ?>
    <?php foreach($products as $row): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['sku']); ?></td>
        <td><?php echo htmlspecialchars($row['category']); ?></td>
        <td><?php echo htmlspecialchars($row['unit']); ?></td>
        <td><?php echo htmlspecialchars($row['stock']); ?></td>
        <td>
            <a class="edit" href="edit_product.php?id=<?php echo $row['id']; ?>">Edit</a>
            <a class="delete" href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7">No products found.</td></tr>
<?php endif; ?>
</table>
</div>
</div>

</body>
</html>