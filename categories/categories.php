<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* ADD CATEGORY */
if(isset($_POST['add_category'])){
    $name = trim($_POST['name']);
    $check = $conn->prepare("SELECT * FROM categories WHERE name=?");
    $check->execute([$name]);
    if($check->rowCount() > 0){
        $error = "Category already exists";
    }else{
        $stmt = $conn->prepare("INSERT INTO categories(name) VALUES(?)");
        $stmt->execute([$name]);
        $success = "Category added successfully";
    }
}

/* DELETE CATEGORY */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$id]);
    header("Location: categories.php");
    exit();
}

/* FETCH CATEGORIES */
$categories = $conn->query("SELECT * FROM categories ORDER BY id ASC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Categories - CoreInventory</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;transition:0.3s;}

/* Sidebar */
.sidebar{
    width:230px;height:100vh;background:#1f2937;color:white;padding:25px;
    display:flex;flex-direction:column;
}
.sidebar h2{
    margin-bottom:40px;text-align:center;font-size:24px;
}
.sidebar a{
    display:block;color:white;text-decoration:none;margin:12px 0;padding:12px;border-radius:8px;
    transition:0.3s;
}
.sidebar a:hover,.sidebar a.active{
    background: linear-gradient(90deg,#4f46e5,#6366f1);
    padding-left:25px;
    box-shadow:0 2px 8px rgba(0,0,0,0.2);
}

/* Main */
.main{
    flex:1;padding:30px;transition:0.3s;
}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.topbar h1{font-size:28px;color:#111827;}
.add-btn{
    display:inline-block;margin-bottom:20px;padding:10px 15px;background:#3b82f6;color:white;border-radius:8px;
    text-decoration:none;font-weight:bold;transition:0.3s;
}
.add-btn:hover{background:#2563eb;transform:translateY(-2px);box-shadow:0 4px 15px rgba(0,0,0,0.2);}

/* Messages */
.error, .success{
    padding:12px;margin-bottom:15px;border-radius:6px;transition:0.3s;
}
.error{background:#ffe5e5;color:#c00;}
.success{background:#e5ffe7;color:#0a7a25;}

/* Form */
.form-box{
    background:white;padding:20px;border-radius:10px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);width:400px;margin-bottom:30px;
    transition:0.3s;
}
.form-box input{
    width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:6px;
}
.form-box button{
    padding:10px 15px;border:none;background:#6366f1;color:white;border-radius:6px;cursor:pointer;
    transition:0.3s;
}
.form-box button:hover{background:#4f46e5;transform:translateY(-2px);}

/* Table */
.table-container{overflow-x:auto;}
table{
    width:100%;border-collapse:collapse;background:white;box-shadow:0 8px 20px rgba(0,0,0,0.08);border-radius:10px;overflow:hidden;
}
table th{background:#111827;color:white;padding:12px;text-align:center;}
table td{padding:12px;border-bottom:1px solid #eee;text-align:center;transition:0.3s;}
table tr:hover td{background:#f0f4ff;}
.delete{
    color:white;background:#ef4444;padding:6px 10px;border-radius:6px;text-decoration:none;
    transition:0.3s;
}
.delete:hover{background:#dc2626;transform:translateY(-1px);}
</style>
</head>
<body>

<div class="sidebar">
<h2>CoreInventory</h2>
<a href="../dashboard/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
<a href="../categories/categories.php" class="active"><i class="fa-solid fa-tags"></i> Categories</a>
<a href="../products/products.php"><i class="fa-solid fa-boxes-stacked"></i> Products</a>
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
    <h1>Product Categories</h1>
    <div>Welcome, <strong><?php echo $_SESSION['user']; ?></strong></div>
</div>

<?php if(isset($error)){ echo "<div class='error'>$error</div>"; } ?>
<?php if(isset($success)){ echo "<div class='success'>$success</div>"; } ?>

<!-- ADD CATEGORY -->
<div class="form-box">
<form method="POST">
<input type="text" name="name" placeholder="Enter Category Name" required>
<button name="add_category">Add Category</button>
</form>
</div>

<!-- CATEGORY TABLE -->
<div class="table-container">
<table>
<tr>
<th>ID</th>
<th>Category Name</th>
<th>Action</th>
</tr>
<?php while($row = $categories->fetch(PDO::FETCH_ASSOC)){ ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td>
<a class="delete" href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this category?')">Delete</a>
</td>
</tr>
<?php } ?>
</table>
</div>
</div>
</body>
</html>