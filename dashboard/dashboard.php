<?php
session_start();
require '../config/db.php';

/* Protect page */
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Dashboard KPIs */
$kpis = [
    'Total Products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'Low Stock' => $conn->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn(),
    'Receipts' => $conn->query("SELECT COUNT(*) FROM receipts")->fetchColumn(),
    'Deliveries' => $conn->query("SELECT COUNT(*) FROM deliveries")->fetchColumn(),
    'Transfers' => $conn->query("SELECT COUNT(*) FROM transfers")->fetchColumn(),
    'Categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'Warehouses' => $conn->query("SELECT COUNT(*) FROM warehouses")->fetchColumn(),
    'Adjustments' => $conn->query("SELECT COUNT(*) FROM adjustments")->fetchColumn()
];

/* Fetch Low Stock Items */
$lowStockItems = $conn->query("SELECT name, stock FROM products WHERE stock < 10")->fetchAll(PDO::FETCH_ASSOC);

/* Handle search (if user clicks Search button) */
$searchResults = [];
if(isset($_GET['search']) && !empty(trim($_GET['search']))){
    $term = "%".trim($_GET['search'])."%";
    $stmt = $conn->prepare("SELECT name, sku, stock FROM products WHERE name LIKE ? OR sku LIKE ?");
    $stmt->execute([$term,$term]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CoreInventory Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:#f4f6f9;transition: background 0.3s;}
/* Sidebar */
.sidebar{
    width:230px;height:100vh;background:#1f2937;color:white;padding:25px;
}
.sidebar h2{margin-bottom:40px;text-align:center;font-size:24px;}
.sidebar a{
    display:block;color:white;text-decoration:none;margin:12px 0;padding:12px;border-radius:8px;
    font-weight:500;transition: all 0.2s;
}
.sidebar a:hover,.sidebar a.active{
    background: linear-gradient(90deg,#4f46e5,#6366f1);
    padding-left:25px;
}
/* Main */
.main{flex:1;padding:30px;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.topbar h1{font-size:28px;color:#111827;}
/* Cards */
.cards{
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}
.card{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    display:flex;
    align-items:center;
    gap:15px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover{
    transform: translateY(-5px);
    box-shadow:0 12px 25px rgba(0,0,0,0.12);
}
.card i{
    font-size:32px;
    color:white;
    padding:15px;
    border-radius:50%;
}
.card-gradient-0{i{background: linear-gradient(135deg,#6366f1,#818cf8);} }
.card-gradient-1{i{background: linear-gradient(135deg,#ef4444,#f87171);} }
.card-gradient-2{i{background: linear-gradient(135deg,#10b981,#34d399);} }
.card-gradient-3{i{background: linear-gradient(135deg,#f59e0b,#fbbf24);} }
.card-gradient-4{i{background: linear-gradient(135deg,#3b82f6,#60a5fa);} }
.card-gradient-5{i{background: linear-gradient(135deg,#8b5cf6,#a78bfa);} }
.card-gradient-6{i{background: linear-gradient(135deg,#06b6d4,#22d3ee);} }
.card-gradient-7{i{background: linear-gradient(135deg,#f97316,#fb923c);} }
.card-content{flex:1;}
.card-content h3{font-size:16px;color:#555;margin-bottom:8px;}
.card-content p{font-size:24px;font-weight:bold;color:#111827;}
/* Alerts */
.alert{
    margin-top:25px;
    padding:15px 20px;
    background:#fee2e2;
    color:#b91c1c;
    border-radius:8px;
    cursor:pointer;
    transition: all 0.2s;
}
.alert:hover{background:#fca5a5;}
.alert-content{display:none;margin-top:10px;}
/* Search Box */
.search-box{
    position:relative;
    margin:20px 0;
    width:300px;
}
.search-box form{
    display:flex;         /* Make input and button inline */
    gap:5px;              /* Space between input and button */
}
#product-search{
    flex:1;
    width:100%;
    padding:10px 15px;
    border-radius:6px;
    border:1px solid #ccc;
    outline:none;
}
#search-suggestions{
    position:absolute;
    top:100%;
    left:0;
    width:100%;
    background:white;
    border:1px solid #ccc;
    border-top:none;
    border-radius:0 0 6px 6px;
    box-shadow:0 4px 8px rgba(0,0,0,0.1);
    max-height:200px;
    overflow-y:auto;
    display:none;
    z-index:100;
}
#search-suggestions div{
    padding:10px 15px;
    cursor:pointer;
    transition: background 0.2s;
}
#search-suggestions div:hover{background:#3b82f6;color:white;}
/* Blue Search Button */
.search-box button{
    padding:10px 15px;
    border:none;
    background:#3b82f6;
    color:white;
    border-radius:6px;
    cursor:pointer;
    margin-left:5px;
    transition: background 0.3s;
}
.search-box button:hover{background:#1e40af;}
/* Search Results Table */
.search-results{
    width:100%;
    border-collapse:collapse;
    background:white;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
    margin-top:10px;
}
.search-results th,.search-results td{
    padding:12px;
    border:1px solid #ddd;
    text-align:center;
}
.search-results th{background:#111827;color:white;}
</style>
</head>
<body>

<div class="sidebar">
    <h2>CoreInventory</h2>
    <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="../categories/categories.php"><i class="fa-solid fa-tags"></i> Categories</a>
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
        <h1>Dashboard</h1>
        <div>Welcome, <strong><?php echo $_SESSION['user']; ?></strong></div>
    </div>

    <!-- KPI Cards -->
    <div class="cards">
        <?php 
        $icons = ['fa-box','fa-box-open','fa-receipt','fa-truck','fa-exchange-alt','fa-tags','fa-warehouse','fa-sliders'];
        $i=0;
        foreach($kpis as $title=>$value){
            echo "<div class='card card-gradient-$i'>
                    <i class='fa-solid {$icons[$i]}'></i>
                    <div class='card-content'>
                        <h3>$title</h3>
                        <p>$value</p>
                    </div>
                  </div>";
            $i++;
        }
        ?>
    </div>

    <!-- Search Box -->
    <div class="search-box">
        <form method="GET" id="search-form">
            <input type="text" id="product-search" name="search" placeholder="Search products by name or SKU" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" autocomplete="off">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            <div id="search-suggestions"></div>
        </form>
    </div>

    <!-- Search Results -->
    <div id="search-results-container">
        <?php if(count($searchResults) > 0): ?>
        <table class="search-results">
            <tr><th>Product Name</th><th>SKU</th><th>Stock</th></tr>
            <?php foreach($searchResults as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['name']); ?></td>
                <td><?php echo htmlspecialchars($s['sku']); ?></td>
                <td><?php echo $s['stock']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Low Stock Alert -->
    <?php if(count($lowStockItems) > 0): ?>
    <div class="alert" onclick="toggleAlert()">
        ⚠ Low Stock Alert! Click to view
        <div id="alert-content" class="alert-content">
            <?php foreach($lowStockItems as $item){
                echo "{$item['name']} - {$item['stock']} units<br>";
            } ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleAlert(){
    const content = document.getElementById('alert-content');
    content.style.display = content.style.display==='block'?'none':'block';
}

// Live search suggestions
const searchInput = document.getElementById('product-search');
const suggestionBox = document.getElementById('search-suggestions');

searchInput.addEventListener('input', function(){
    const term = this.value.trim();
    if(term.length === 0){
        suggestionBox.style.display='none';
        return;
    }
    fetch('search_products.php?term='+encodeURIComponent(term))
    .then(res => res.json())
    .then(data=>{
        suggestionBox.innerHTML='';
        if(data.length>0){
            data.forEach(item=>{
                const div=document.createElement('div');
                div.innerHTML=`${item.name} (SKU: ${item.sku}, Stock: ${item.stock})`;
                div.addEventListener('click', ()=>{
                    searchInput.value = item.name;
                    suggestionBox.style.display='none';
                });
                suggestionBox.appendChild(div);
            });
            suggestionBox.style.display='block';
        } else { suggestionBox.style.display='none'; }
    });
});

document.addEventListener('click', e=>{
    if(!searchInput.contains(e.target) && !suggestionBox.contains(e.target)){
        suggestionBox.style.display='none';
    }
});
</script>

</body>
</html>