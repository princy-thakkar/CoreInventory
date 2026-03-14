<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Add Warehouse */
if(isset($_POST['add_warehouse'])){
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);

    if($name == ''){
        $error = "Warehouse name is required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO warehouses(name, location) VALUES(?, ?)");
        $stmt->execute([$name, $location]);
        $success = "Warehouse added successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Warehouse - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
.form-box{background:white;padding:20px;border-radius:10px;box-shadow:0 6px 15px rgba(0,0,0,0.1);width:500px;}
.form-box input{width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:6px;}
.form-box button{padding:10px 15px;border:none;background:#6366f1;color:white;border-radius:6px;cursor:pointer;}
.form-box button:hover{background:#4f46e5;}
.error{background:#ffe5e5;color:#c00;padding:10px;margin-bottom:15px;border-radius:6px;}
.success{background:#e5ffe7;color:#0a7a25;padding:10px;margin-bottom:15px;border-radius:6px;}
</style>
</head>
<body>
<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php">Dashboard</a>
<a href="../categories/categories.php">Categories</a>
<a href="../products/products.php">Products</a>
<a href="../operations/receipts.php">Receipts</a>
<a href="../operations/delivery.php">Delivery Orders</a>
<a href="../operations/transfers.php">Internal Transfers</a>
<a href="../operations/adjustments.php">Stock Adjustment</a>
<a href="warehouse.php" class="active">Warehouse</a>
<a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
<h1>Add Warehouse</h1>
<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<div class="form-box">
<form method="POST">
<label>Warehouse Name</label>
<input type="text" name="name" placeholder="Enter warehouse name" required>
<label>Location</label>
<input type="text" name="location" placeholder="Enter location (optional)">
<button name="add_warehouse">Add Warehouse</button>
</form>
</div>
<a href="warehouse.php">⬅ Back to Warehouses</a>
</div>
</body>
</html>