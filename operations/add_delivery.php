<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* FETCH SUPPLIERS */
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

/* FETCH PRODUCTS */
$products = $conn->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

/* ADD DELIVERY */
if(isset($_POST['add_delivery'])){
    $supplier_id = intval($_POST['supplier']);
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];

    if(!$supplier_id || empty($product_ids)){
        $error = "Please select supplier and at least one product!";
    } else {
        // Insert into deliveries
        $stmt = $conn->prepare("INSERT INTO deliveries(supplier_id) VALUES(?)");
        $stmt->execute([$supplier_id]);
        $delivery_id = $conn->lastInsertId();

        for($i=0; $i<count($product_ids); $i++){
            $pid = intval($product_ids[$i]);
            $qty = intval($quantities[$i]);

            if($qty > 0){
                // 1️⃣ Insert into delivery_items
                $conn->prepare("INSERT INTO delivery_items(delivery_id, product_id, quantity) VALUES(?,?,?)")
                     ->execute([$delivery_id, $pid, $qty]);

                // 2️⃣ Reduce stock
                $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?")
                     ->execute([$qty, $pid]);

                // 3️⃣ Log in stock ledger
                $conn->prepare("
                    INSERT INTO stock_ledger (product_id, type, quantity, reference_id)
                    VALUES (?, 'Delivery', ?, ?)
                ")->execute([$pid, $qty, $delivery_id]);
            }
        }
        $success = "Delivery added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Delivery - CoreInventory</title>

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
    width:600px;
}

.form-box select, .form-box input{
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

.product-row{
    display:flex;
    gap:10px;
    margin-bottom:5px;
}

.product-row select, .product-row input{
    flex:1;
}

.add-product{
    margin-bottom:15px;
    cursor:pointer;
    color:#1d4ed8;
    text-decoration:underline;
}
.add-product:hover{color:#0d3a9c;}
</style>

<script>
function addProductRow(){
    const container = document.getElementById('products-container');
    const row = document.createElement('div');
    row.className = 'product-row';

    row.innerHTML = `
        <select name="product_id[]" required>
            <?php foreach($products as $p): ?>
            <option value="<?php echo $p['id']; ?>">
                <?php echo htmlspecialchars($p['name']); ?> (<?php echo $p['sku']; ?>)
            </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="quantity[]" placeholder="Quantity" min="1" required>
    `;

    container.appendChild(row);
}
</script>

</head>
<body>

<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php">Dashboard</a>
<a href="../categories/categories.php">Categories</a>
<a href="../products/products.php">Products</a>
<a href="../operations/receipts.php">Receipts</a>
<a href="../operations/delivery.php" class="active">Delivery Orders</a>
<a href="../operations/transfers.php">Internal Transfers</a>
<a href="../operations/adjustments.php">Stock Adjustment</a>
<a href="../settings/warehouse.php">Warehouse</a>
<a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
<h1>Add Delivery</h1>
<br>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<div class="form-box">
<form method="POST">

<label>Supplier</label>
<select name="supplier" required>
    <option value="">-- Select Supplier --</option>
    <?php foreach($suppliers as $s): ?>
    <option value="<?php echo $s['id']; ?>">
        <?php echo htmlspecialchars($s['name']); ?>
    </option>
    <?php endforeach; ?>
</select>

<label>Products</label>

<div id="products-container">
    <div class="product-row">
        <select name="product_id[]" required>
            <?php foreach($products as $p): ?>
            <option value="<?php echo $p['id']; ?>">
                <?php echo htmlspecialchars($p['name']); ?> (<?php echo $p['sku']; ?>)
            </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="quantity[]" placeholder="Quantity" min="1" required>
    </div>
</div>

<div class="add-product" onclick="addProductRow()">+ Add Another Product</div>

<button name="add_delivery">Add Delivery</button>

</form>
</div>

<br>
<a href="delivery.php">⬅ Back to Delivery Orders</a>

</div>
</body>
</html>