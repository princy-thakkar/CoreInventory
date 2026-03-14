<?php
session_start();
require '../config/db.php';

/* redirect if otp not set */

if(!isset($_SESSION['otp'])){
header("Location: signup.php");
exit();
}

if(isset($_POST['verify'])){

$user_otp = $_POST['otp'];

/* check OTP */

if($user_otp == $_SESSION['otp']){

$name = $_SESSION['name'];
$email = $_SESSION['email'];
$password = $_SESSION['password'];

/* insert verified user */

$sql = "INSERT INTO users(name,email,password) VALUES(?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$name,$email,$password]);

/* clear session */

unset($_SESSION['otp']);
unset($_SESSION['name']);
unset($_SESSION['email']);
unset($_SESSION['password']);

/* redirect to login */

header("Location: login.php?verified=1");
exit();

}else{

$error = "Invalid OTP. Please try again.";

}

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Verify OTP</title>

<style>

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
}

.otp-box{

width:360px;
padding:40px;

background:white;
border-radius:20px;

box-shadow:0 20px 40px rgba(0,0,0,0.2);

text-align:center;

}

h2{
margin-bottom:15px;
color:#333;
}

p{
margin-bottom:20px;
color:#555;
font-size:14px;
}

input{

width:100%;
padding:13px;

border:none;
border-radius:10px;

margin-bottom:15px;

background:#f2f4f7;
font-size:16px;
text-align:center;
letter-spacing:5px;

}

input:focus{
outline:none;
background:white;
box-shadow:0 0 0 2px #6366f1;
}

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

.login-link{
margin-top:15px;
font-size:14px;
}

.login-link a{
color:#6366f1;
text-decoration:none;
font-weight:600;
}

</style>

</head>

<body>

<div class="otp-box">

<h2>OTP Verification</h2>

<p>Enter the OTP sent to your email</p>

<?php if(isset($error)){ ?>
<div class="error"><?php echo $error; ?></div>
<?php } ?>

<form method="POST">

<input type="text" name="otp" placeholder="Enter OTP" required>

<button name="verify">Verify OTP</button>

</form>

<div class="login-link">
<a href="login.php">Back to Login</a>
</div>

</div>

</body>
</html>