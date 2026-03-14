<?php
session_start();
require '../config/db.php';

/* PHPMailer */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* Load PHPMailer */
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

/* Load .env */
$env = parse_ini_file("../.env");

$message = "";
$error = "";

// Handle form submission
if(isset($_POST['reset'])) {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user){
        // Generate token & expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Save token in DB
        $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $stmt->execute([$token, $expiry, $email]);

        // Reset link
        $resetLink = "http://".$_SERVER['HTTP_HOST']."/auth/reset_password.php?token=".$token;

        // Send email via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $env['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $env['MAIL_USER'];
            $mail->Password = $env['MAIL_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $env['MAIL_PORT'];

            $mail->setFrom($env['MAIL_USER'], 'CoreInventory');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "CoreInventory Password Reset";
            $mail->Body = "
                <h3>Hello {$user['name']}</h3>
                <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
                <a href='{$resetLink}' style='padding:10px 20px;background:#6366f1;color:white;text-decoration:none;border-radius:6px;'>Reset Password</a>
                <p>If you didn't request this, ignore this email.</p>
            ";

            $mail->send();
            $message = "If this email exists in our system, a reset link has been sent.";
        } catch(Exception $e){
            $error = "Mailer Error: " . $mail->ErrorInfo;
        }

    } else {
        $message = "If this email exists in our system, a reset link has been sent.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password - CoreInventory</title>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{height:100vh;display:flex;justify-content:center;align-items:center;background:linear-gradient(135deg,#eef2f3,#dfe9f3);overflow:hidden;}
.container{display:flex;align-items:center;gap:70px;}
.animation{animation:slideIn 1.4s ease;}
.forgot-box{
    width:380px;padding:45px;background:rgba(255,255,255,0.9);backdrop-filter:blur(12px);
    border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,0.2);text-align:center;animation:fadeIn 1.2s ease;
}
.logo{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:25px;}
.logo-icon{width:50px;height:50px;background:linear-gradient(135deg,#6366f1,#06b6d4);border-radius:12px;display:flex;justify-content:center;align-items:center;position:relative;box-shadow:0 8px 20px rgba(0,0,0,0.15);}
.box{width:18px;height:18px;background:white;border-radius:3px;position:absolute;top:10px;left:14px;}
.bars{position:absolute;bottom:6px;right:6px;display:flex;gap:2px;}
.bars div{width:3px;background:white;border-radius:2px;}
.bars div:nth-child(1){height:6px;} .bars div:nth-child(2){height:9px;} .bars div:nth-child(3){height:12px;}
.logo-text{font-size:26px;font-weight:700;color:#1f2937;}
.logo-text span{background:linear-gradient(90deg,#6366f1,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.subtitle{color:#555;margin-bottom:25px;font-size:15px;}
input{width:100%;padding:13px;border:none;border-radius:10px;margin:10px 0;background:#f2f4f7;font-size:14px;transition:0.3s;}
input:focus{outline:none;background:white;box-shadow:0 0 0 2px #6366f1;}
button{width:100%;padding:13px;border:none;border-radius:10px;background:linear-gradient(90deg,#6366f1,#06b6d4);color:white;font-size:16px;cursor:pointer;transition:0.3s;}
button:hover{transform:translateY(-2px);box-shadow:0 6px 15px rgba(0,0,0,0.25);}
.message{color:green;margin-bottom:10px;font-size:14px;}
.error{color:red;margin-bottom:10px;font-size:14px;}
.back-login{margin-top:15px;font-size:14px;}
.back-login a{color:#6366f1;text-decoration:none;font-weight:600;transition:0.3s;}
.back-login a:hover{color:#4f46e5;text-decoration:underline;}
@keyframes slideIn{from{transform:translateX(-200px);opacity:0;}to{transform:translateX(0);opacity:1;}}
@keyframes fadeIn{from{opacity:0;transform:translateY(40px);}to{opacity:1;transform:translateY(0);}}
</style>
</head>
<body>

<div class="container">
<div class="animation">
<lottie-player src="https://assets2.lottiefiles.com/packages/lf20_puciaact.json" background="transparent" speed="1" style="width:300px;height:300px" loop autoplay></lottie-player>
</div>

<div class="forgot-box">
<div class="logo">
<div class="logo-icon"><div class="box"></div><div class="bars"><div></div><div></div><div></div></div></div>
<div class="logo-text">Core<span>Inventory</span></div>
</div>

<p class="subtitle">Forgot Your Password?</p>

<?php if($message){ ?><div class="message"><?php echo $message; ?></div><?php } ?>
<?php if($error){ ?><div class="error"><?php echo $error; ?></div><?php } ?>

<form method="POST">
<input type="email" name="email" placeholder="Enter your email" required>
<button name="reset">Send Reset Link</button>
</form>

<div class="back-login">
<a href="login.php">← Back to Login</a>
</div>
</div>
</div>

</body>
</html>