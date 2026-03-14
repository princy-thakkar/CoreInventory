<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Get warehouse ID */
if(!isset($_GET['id'])){
    header("Location: warehouse.php");
    exit();
}
$id = intval($_GET['id']);

/* Fetch warehouse details */
$stmt = $conn->prepare("SELECT * FROM warehouses WHERE id=?");
$stmt->execute([$id]);
$warehouse = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$warehouse){
    header("Location: warehouse.php");
    exit();
}

/* Update warehouse */
if(isset($_POST['update_warehouse'])){
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);

    if(!$name){
        $error = "Warehouse name is required!";
    } else {
        $stmt = $conn->prepare("UPDATE warehouses SET name=?, location=? WHERE id=?");
        $stmt->execute([$name, $location, $id]);
        $success = "Warehouse updated successfully!";
        /* Refresh data */
        $stmt = $conn->prepare("SELECT * FROM warehouses WHERE id=?");
        $stmt->execute([$id]);
        $warehouse = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Warehouse - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
.form-box{background:white;padding:20px;border-radius:10px;box-shadow:0 6px 15px rgba(0,0,0,0.1);width:500px;margin-bottom:20px;}
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
<h1>Edit Warehouse</h1>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<div class="form-box">
<form method="POST">
<label>Warehouse Name</label>
<input type="text" name="name" value="<?php echo htmlspecialchars($warehouse['name']); ?>" required>
<label>Location</label>
<input type="text" name="location" value="<?php echo htmlspecialchars($warehouse['location']); ?>">

<button name="update_warehouse">Update Warehouse</button>
</form>
</div>

<a href="warehouse.php">⬅ Back to Warehouses</a>
</div>
</body>
</html>