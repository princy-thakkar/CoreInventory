<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch products and warehouses */
$products = $conn->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$warehouses = $conn->query("SELECT * FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

/* Add Adjustment */
if(isset($_POST['add_adjustment'])){
    $product_id = intval($_POST['product_id']);
    $warehouse_id = intval($_POST['warehouse_id']);
    $new_quantity = intval($_POST['new_quantity']);
    $reason = trim($_POST['reason']);

    if(!$product_id || !$warehouse_id || $new_quantity < 0 || $reason == ""){
        $error = "Please fill all fields correctly.";
    } else {
        /* Fetch current stock */
        $current_stock = $conn->prepare("SELECT stock FROM products WHERE id=?");
        $current_stock->execute([$product_id]);
        $current_stock = $current_stock->fetchColumn();

        /* Update stock */
        $conn->prepare("UPDATE products SET stock=? WHERE id=?")->execute([$new_quantity, $product_id]);

        // Log in stock ledger
        // Log in stock ledger
        $conn->prepare("INSERT INTO stock_ledger(product_id, type, quantity, reference_id) VALUES(?,?,?,?)")
        ->execute([$product_id, 'Adjustment', $new_quantity - $current_stock, $conn->lastInsertId()]);
        /* Log adjustment */
        $stmt = $conn->prepare("INSERT INTO adjustments(product_id, warehouse_id, old_quantity, new_quantity, reason) VALUES(?,?,?,?,?)");
        $stmt->execute([$product_id, $warehouse_id, $current_stock, $new_quantity, $reason]);

        $success = "Stock adjusted successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Stock Adjustment - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
.form-box{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
    width:500px;
}
.form-box select, .form-box input, .form-box textarea{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border:1px solid #ddd;
    border-radius:6px;
}
.form-box button{
    padding:10px 15px;
    border:none;
    background:#6366f1;
    color:white;
    border-radius:6px;
    cursor:pointer;
}
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
<a href="adjustments.php" class="active">Stock Adjustment</a>
<a href="../settings/warehouse.php">Warehouse</a>
<a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
<h1>Add Stock Adjustment</h1>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<div class="form-box">
<form method="POST">
<label>Product</label>
<select name="product_id" required>
    <option value="">-- Select Product --</option>
    <?php foreach($products as $p): ?>
    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo $p['sku']; ?>)</option>
    <?php endforeach; ?>
</select>

<label>Warehouse</label>
<select name="warehouse_id" required>
    <option value="">-- Select Warehouse --</option>
    <?php foreach($warehouses as $w): ?>
    <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option>
    <?php endforeach; ?>
</select>

<label>New Quantity</label>
<input type="number" name="new_quantity" placeholder="Enter new stock quantity" min="0" required>

<label>Reason</label>
<textarea name="reason" placeholder="Enter reason for adjustment" required></textarea>

<button name="add_adjustment">Adjust Stock</button>
</form>
</div>

<a href="adjustments.php">⬅ Back to Adjustments</a>
</div>
</body>
</html>