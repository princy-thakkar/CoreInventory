<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch products & suppliers */
$products = $conn->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['add_delivery'])){
    $supplier_id = intval($_POST['supplier']);
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];

    if(!$supplier_id || empty($product_ids)){
        $error = "Select supplier and products!";
    } else {
        $stmt = $conn->prepare("INSERT INTO deliveries(supplier_id) VALUES(?)");
        $stmt->execute([$supplier_id]);
        $delivery_id = $conn->lastInsertId();

        for($i=0; $i<count($product_ids); $i++){
            $pid = intval($product_ids[$i]);
            $qty = intval($quantities[$i]);
            if($qty > 0){
                $conn->prepare("INSERT INTO delivery_items(delivery_id, product_id, quantity) VALUES(?,?,?)")
                     ->execute([$delivery_id, $pid, $qty]);

                // Reduce stock (delivery decreases stock)
                $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?")
                     ->execute([$qty, $pid]);

                // Log in stock ledger
                $conn->prepare("INSERT INTO stock_ledger(product_id,type,quantity,reference_id) VALUES(?,?,?,?)")
                     ->execute([$pid,'Delivery',$qty,$delivery_id]);
            }
        }
        $success = "Delivery order added!";
    }
}
?>