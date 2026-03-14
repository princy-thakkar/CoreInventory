<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* GET PRODUCT BY ID */
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

/* If product does not exist, redirect with error */
if(!$product){
    $_SESSION['error'] = "Product not found!";
    header("Location: products.php");
    exit();
}

/* UPDATE PRODUCT */
if(isset($_POST['update_product'])){
    $name = trim($_POST['name']);
    $sku = trim($_POST['sku']);
    $category = trim($_POST['category']);
    $unit = trim($_POST['unit']);
    $stock = intval($_POST['stock']);

    // Check SKU uniqueness only if changed
    if($sku != $product['sku']){
        $check = $conn->prepare("SELECT * FROM products WHERE sku=?");
        $check->execute([$sku]);
        if($check->rowCount() > 0){
            $error = "SKU already exists!";
        }
    }

    if(!isset($error)){
        $stmt = $conn->prepare("UPDATE products SET name=?, sku=?, category=?, unit=?, stock=? WHERE id=?");
        $stmt->execute([$name, $sku, $category, $unit, $stock, $id]);
        $success = "Product updated successfully!";

        // Refresh product data
        $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Product - CoreInventory</title>
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
<h1>Edit Product</h1>
<br>

<?php
if(isset($error)){ echo "<div class='error'>$error</div>"; }
if(isset($success)){ echo "<div class='success'>$success</div>"; }

// Flash message from redirect
if(isset($_SESSION['error'])){ 
    echo "<div class='error'>".$_SESSION['error']."</div>"; 
    unset($_SESSION['error']); 
}
?>

<div class="form-box">
<form method="POST">
<input type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
<input type="text" name="sku" placeholder="SKU / Code" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
<input type="text" name="category" placeholder="Category Name" value="<?php echo htmlspecialchars($product['category']); ?>" required>
<input type="text" name="unit" placeholder="Unit (pcs, kg, etc.)" value="<?php echo htmlspecialchars($product['unit']); ?>" required>
<input type="number" name="stock" placeholder="Stock" value="<?php echo $product['stock']; ?>" min="0" required>
<button name="update_product">Update Product</button>
</form>
</div>

<a href="products.php">⬅ Back to Products</a>
</div>
</body>
</html>