<?php
session_start();
require '../config/db.php';

if(isset($_SESSION['user'])){
header("Location: ../dashboard/dashboard.php");
exit();
}

if(isset($_POST['login'])){

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch();

if($user && password_verify($password,$user['password'])){

$_SESSION['user']=$user['name'];
header("Location: ../dashboard/dashboard.php");
exit();

}else{
$error="Invalid Email or Password";
}

}
?>

<!DOCTYPE html>
<html>

<head>

<title>CoreInventory Login</title>

<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<style>
.forgot{
    text-align:right;
    margin:10px 0 20px 0;
}

.forgot a{
    font-size:14px;
    color:#6366f1;
    text-decoration:none;
    font-weight:500;
    transition:0.3s;
}

.forgot a:hover{
    color:#4f46e5;
    text-decoration:underline;
}
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(135deg,#eef2f3,#dfe9f3);
overflow:hidden;
}

.container{
display:flex;
align-items:center;
gap:70px;
}

/* animation */

.animation{
animation:slideIn 1.4s ease;
}

/* login card */

.login-box{

width:380px;
padding:45px;

background:rgba(255,255,255,0.9);
backdrop-filter:blur(12px);

border-radius:20px;

box-shadow:0 20px 40px rgba(0,0,0,0.2);

text-align:center;

animation:fadeIn 1.2s ease;
}

/* ===== LOGO ===== */

.logo{
display:flex;
align-items:center;
justify-content:center;
gap:12px;
margin-bottom:25px;
}

/* logo icon */

.logo-icon{

width:50px;
height:50px;

background:linear-gradient(135deg,#6366f1,#06b6d4);

border-radius:12px;

display:flex;
justify-content:center;
align-items:center;

position:relative;

box-shadow:0 8px 20px rgba(0,0,0,0.15);
}

/* inventory box */

.box{
width:18px;
height:18px;
background:white;
border-radius:3px;
position:absolute;
top:10px;
left:14px;
}

/* stock bars */

.bars{
position:absolute;
bottom:6px;
right:6px;
display:flex;
gap:2px;
}

.bars div{
width:3px;
background:white;
border-radius:2px;
}

.bars div:nth-child(1){height:6px;}
.bars div:nth-child(2){height:9px;}
.bars div:nth-child(3){height:12px;}

/* logo text */

.logo-text{
font-size:26px;
font-weight:700;
color:#1f2937;
}

.logo-text span{
background:linear-gradient(90deg,#6366f1,#06b6d4);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

/* subtitle */

.subtitle{
color:#555;
margin-bottom:25px;
font-size:15px;
}

/* input */

input{

width:100%;
padding:13px;

border:none;
border-radius:10px;

margin:10px 0;

background:#f2f4f7;

font-size:14px;

transition:0.3s;
}

input:focus{
outline:none;
background:white;
box-shadow:0 0 0 2px #6366f1;
}

/* button */

button{

width:100%;
padding:13px;

border:none;
border-radius:10px;

background:linear-gradient(90deg,#6366f1,#06b6d4);

color:white;

font-size:16px;

cursor:pointer;

transition:0.3s;
}

button:hover{

transform:translateY(-2px);

box-shadow:0 6px 15px rgba(0,0,0,0.25);
}

.error{
color:red;
margin-bottom:10px;
}

/* signup link */

.signup{
margin-top:15px;
font-size:14px;
}

.signup a{
color:#6366f1;
text-decoration:none;
font-weight:600;
}

/* animations */

@keyframes slideIn{
from{
transform:translateX(-200px);
opacity:0;
}
to{
transform:translateX(0);
opacity:1;
}
}

@keyframes fadeIn{
from{
opacity:0;
transform:translateY(40px);
}
to{
opacity:1;
transform:translateY(0);
}
}

</style>

</head>

<body>

<div class="container">

<!-- animation -->

<div class="animation">

<lottie-player
src="https://assets2.lottiefiles.com/packages/lf20_jcikwtux.json"
background="transparent"
speed="1"
style="width:340px;height:340px"
loop
autoplay>
</lottie-player>

</div>

<!-- login card -->

<div class="login-box">

<div class="logo">

<div class="logo-icon">

<div class="box"></div>

<div class="bars">
<div></div>
<div></div>
<div></div>
</div>

</div>

<div class="logo-text">
Core<span>Inventory</span>
</div>

</div>

<p class="subtitle">Inventory Management System</p>

<?php if(isset($error)){ ?>
<div class="error"><?php echo $error; ?></div>
<?php } ?>

<form method="POST">

<input type="email" name="email" placeholder="Email Address" required>

<input type="password" name="password" placeholder="Password" required>

<div class="forgot">
<a href="forgot_password.php">Forgot Password?</a>
</div>


<button name="login">Login</button>


</form>

<div class="signup">
Don't have an account?
<a href="signup.php">Sign Up</a>
</div>

</div>

</div>

</body>
</html>