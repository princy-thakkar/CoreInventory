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

/* Add Transfer */
if(isset($_POST['add_transfer'])){
    $from_warehouse = intval($_POST['from_warehouse']);
    $to_warehouse = intval($_POST['to_warehouse']);
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $from_locations = $_POST['from_location'];
    $to_locations = $_POST['to_location'];

    if(!$from_warehouse || !$to_warehouse || empty($product_ids)){
        $error = "Please select warehouses and at least one product!";
    } else {
        // Insert into transfers table
        $stmt = $conn->prepare("INSERT INTO transfers(from_warehouse, to_warehouse) VALUES(?,?)");
        $stmt->execute([$from_warehouse, $to_warehouse]);
        $transfer_id = $conn->lastInsertId();

        // Insert each product into transfer_items and update stock
        for($i=0; $i<count($product_ids); $i++){
            $pid = intval($product_ids[$i]);
            $qty = intval($quantities[$i]);
            $from_loc = trim($from_locations[$i]);
            $to_loc = trim($to_locations[$i]);

            if($qty > 0){
                // Insert into transfer_items
                $conn->prepare("INSERT INTO transfer_items(transfer_id, product_id, quantity, from_location, to_location) VALUES(?,?,?,?,?)")
                     ->execute([$transfer_id, $pid, $qty, $from_loc, $to_loc]);

                // Decrease stock from "from warehouse"
                $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?")
                     ->execute([$qty, $pid]);
                // Log Transfer Out
                $conn->prepare("INSERT INTO stock_ledger(product_id, type, quantity, reference_id) VALUES(?,?,?,?)")
                     ->execute([$pid, 'Transfer Out', -$qty, $transfer_id]);

                // Increase stock in "to warehouse"
                $conn->prepare("UPDATE products SET stock = stock + ? WHERE id=?")
                     ->execute([$qty, $pid]);
                // Log Transfer In
                $conn->prepare("INSERT INTO stock_ledger(product_id, type, quantity, reference_id) VALUES(?,?,?,?)")
                     ->execute([$pid, 'Transfer In', $qty, $transfer_id]);
            }
        }

        $success = "Transfer added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Internal Transfer - CoreInventory</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:230px;height:100vh;background:#1f2937;color:white;padding:25px;}
.sidebar a{display:block;color:white;text-decoration:none;margin:12px 0;padding:10px;border-radius:6px;}
.sidebar a:hover,.sidebar a.active{background:#374151;}
.main{flex:1;padding:30px;}
.form-box{background:white;padding:20px;border-radius:10px;box-shadow:0 6px 15px rgba(0,0,0,0.1);width:600px;}
.form-box select, .form-box input{width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:6px;}
.form-box button{padding:10px 15px;border:none;background:#6366f1;color:white;border-radius:6px;cursor:pointer;}
.form-box button:hover{background:#4f46e5;}
.error{background:#ffe5e5;color:#c00;padding:10px;margin-bottom:15px;border-radius:6px;}
.success{background:#e5ffe7;color:#0a7a25;padding:10px;margin-bottom:15px;border-radius:6px;}
.product-row{display:flex; gap:10px; margin-bottom:5px;}
.product-row select, .product-row input{flex:1;}
.add-product{margin-bottom:15px; cursor:pointer; color:#1d4ed8; text-decoration:underline;}
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
            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo $p['sku']; ?>)</option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="quantity[]" placeholder="Quantity" min="1" required>
        <input type="text" name="from_location[]" placeholder="From Location" required>
        <input type="text" name="to_location[]" placeholder="To Location" required>
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
<a href="../operations/delivery.php">Delivery Orders</a>
<a href="../operations/transfers.php" class="active">Internal Transfers</a>
<a href="../operations/adjustments.php">Stock Adjustment</a>
<a href="../settings/warehouse.php">Warehouse</a>
<a href="../auth/logout.php">Logout</a>
</div>

<div class="main">
<h1>Add Internal Transfer</h1>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<div class="form-box">
<form method="POST">
<label>From Warehouse</label>
<select name="from_warehouse" required>
    <option value="">-- Select From Warehouse --</option>
    <?php foreach($warehouses as $w): ?>
    <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option>
    <?php endforeach; ?>
</select>

<label>To Warehouse</label>
<select name="to_warehouse" required>
    <option value="">-- Select To Warehouse --</option>
    <?php foreach($warehouses as $w): ?>
    <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option>
    <?php endforeach; ?>
</select>

<label>Products</label>
<div id="products-container">
    <div class="product-row">
        <select name="product_id[]" required>
            <?php foreach($products as $p): ?>
            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo $p['sku']; ?>)</option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="quantity[]" placeholder="Quantity" min="1" required>
        <input type="text" name="from_location[]" placeholder="From Location" required>
        <input type="text" name="to_location[]" placeholder="To Location" required>
    </div>
</div>
<div class="add-product" onclick="addProductRow()">+ Add Another Product</div>

<button name="add_transfer">Add Transfer</button>
</form>
</div>

<a href="transfers.php">⬅ Back to Transfers</a>
</div>
</body>
</html>