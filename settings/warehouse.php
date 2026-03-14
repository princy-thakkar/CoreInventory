<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* ADD WAREHOUSE */
if(isset($_POST['add_warehouse'])){
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);

    if(!$name){
        $error = "Warehouse name is required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO warehouses(name, location) VALUES(?,?)");
        $stmt->execute([$name, $location]);
        $success = "Warehouse added successfully!";
    }
}

/* DELETE WAREHOUSE */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->prepare("DELETE FROM warehouses WHERE id=?")->execute([$id]);
    header("Location: warehouse.php");
    exit();
}

/* FETCH WAREHOUSES */
$warehouses = $conn->query("SELECT * FROM warehouses ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Warehouses - CoreInventory</title>

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

/* Main */
.main{
    flex:1;
    padding:30px;
    animation: fadeIn 0.8s ease-in-out;
}
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

/* Form Box */
.form-box{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
    width:500px;
    margin-bottom:20px;
}
.form-box input{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border:1px solid #ddd;
    border-radius:6px;
}
.form-box button{
    padding:10px 15px;
    border:none;
    background:#3b82f6;
    color:white;
    border-radius:6px;
    cursor:pointer;
    transition: background 0.3s;
}
.form-box button:hover{background:#1e40af;}

/* Messages */
.error{
    background:#ffe5e5;
    color:#c00;
    padding:10px;
    margin-bottom:15px;
    border-radius:6px;
}
.success{
    background:#e5ffe7;
    color:#0a7a25;
    padding:10px;
    margin-bottom:15px;
    border-radius:6px;
}

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

/* Action Links */
.edit{color:#3b82f6;font-weight:bold;text-decoration:none;transition:color 0.3s;}
.edit:hover{color:#1e40af;}
.delete{color:#ef4444;font-weight:bold;text-decoration:none;transition:color 0.3s;}
.delete:hover{color:#b91c1c;}
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
<a href="../operations/stock_ledger.php"><i class="fa-solid fa-book"></i> Stock Ledger</a>
<a href="../settings/warehouse.php" class="active"><i class="fa-solid fa-warehouse"></i> Warehouse</a>
<a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main">
<h1>Warehouses</h1>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<!-- ADD WAREHOUSE FORM -->
<div class="form-box">
<form method="POST">
<label>Warehouse Name</label>
<input type="text" name="name" placeholder="Enter warehouse name" required>
<label>Location</label>
<input type="text" name="location" placeholder="Enter location (optional)">
<button name="add_warehouse">➕ Add Warehouse</button>
</form>
</div>

<!-- WAREHOUSE TABLE -->
<table class="table">
<tr>
<th>ID</th>
<th>Name</th>
<th>Location</th>
<th>Action</th>
</tr>
<?php foreach($warehouses as $w): ?>
<tr>
<td><?php echo $w['id']; ?></td>
<td><?php echo htmlspecialchars($w['name']); ?></td>
<td><?php echo htmlspecialchars($w['location']); ?></td>
<td>
<a class="edit" href="edit_warehouse.php?id=<?php echo $w['id']; ?>">Edit</a> |
<a class="delete" href="?delete=<?php echo $w['id']; ?>" onclick="return confirm('Delete this warehouse?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>

</div>
</body>
</html>