<?php
session_start();
require '../config/db.php';
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* ADD PRODUCT */
if(isset($_POST['add_product'])){
    $name = trim($_POST['name']);
    $sku = trim($_POST['sku']);
    $category = trim($_POST['category']);
    $unit = trim($_POST['unit']);
    $stock = intval($_POST['stock']);

    // Check SKU uniqueness
    $check = $conn->prepare("SELECT * FROM products WHERE sku=?");
    $check->execute([$sku]);
    if($check->rowCount() > 0){
        $error = "SKU already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO products(name, sku, category, unit, stock) VALUES(?,?,?,?,?)");
        $stmt->execute([$name,$sku,$category,$unit,$stock]);
        $success = "Product added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Product - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
.error{background:#ffe5e5;color:#c00;padding:10px;margin-bottom:15px;border-radius:6px;}
.success{background:#e5ffe7;color:#0a7a25;padding:10px;margin-bottom:15px;border-radius:6px;}
.form-box{background:white;padding:20px;border-radius:10px;box-shadow:0 6px 15px rgba(0,0,0,0.1);width:500px;}
.form-box input{width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:6px;}
.form-box button{padding:10px 15px;border:none;background:#6366f1;color:white;border-radius:6px;cursor:pointer;}
.form-box button:hover{background:#4f46e5;}
</style>
</head>
<body>
<div class="sidebar">
<h2>CoreInventory</h2>
<a href="products.php" class="active">Products</a>
<a href="../dashboard/dashboard.php">Dashboard</a>
</div>

<div class="main">
<h1>Add Product</h1>
<br>

<?php if(isset($error)){ echo "<div class='error'>$error</div>"; } ?>
<?php if(isset($success)){ echo "<div class='success'>$success</div>"; } ?>

<div class="form-box">
<form method="POST">
<input type="text" name="name" placeholder="Product Name" required>
<input type="text" name="sku" placeholder="SKU / Code" required>
<input type="text" name="category" placeholder="Category Name" required>
<input type="text" name="unit" placeholder="Unit (pcs, kg, etc.)" required>
<input type="number" name="stock" placeholder="Initial Stock" value="0" min="0" required>
<button name="add_product">Add Product</button>
</form>
</div>

<a href="products.php">⬅ Back to Products</a>
</div>
</body>
</html>