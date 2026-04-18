<?php
include 'db.php';

// যদি অলরেডি লগইন থাকে তবে ড্যাশবোর্ডে পাঠিয়ে দাও
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // ১. ডাটাবেস থেকে ইউজারের তথ্য আনা
    $query = "SELECT * FROM admins WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // ২. password_verify ফাংশন দিয়ে পাসওয়ার্ড চেক করা
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $row['username'];
            header("Location: admin.php");
            exit();
        } else {
            $error = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
        }
    } else {
        $error = "ইউজারনেমটি সঠিক নয়!";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Patient Care Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        :root {
            --navy: #0D1B3E; /* লোগোর গাঢ় নীল */
            --teal: #00A8B5; /* লোগোর টিল/সায়ান */
            --white: #ffffff;
        }

        body {
            background: linear-gradient(135deg, var(--navy) 0%, var(--teal) 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Hind Siliguri', sans-serif;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-box img {
            width: 100px;
            height: auto;
            margin-bottom: 15px;
        }

        .hospital-name {
            color: var(--navy);
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .login-title {
            color: var(--teal);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--teal);
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #eee;
            border-radius: 12px;
            outline: none;
            font-size: 16px;
            transition: 0.3s;
            box-sizing: border-box;
        }

        .input-group input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 10px rgba(0, 168, 181, 0.1);
        }

        .error-msg {
            color: #e84118;
            background: #fbc4b4;
            padding: 10px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: var(--navy);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(13, 27, 62, 0.3);
        }

        .login-btn:hover {
            background: var(--teal);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 168, 181, 0.3);
        }

        .footer-link {
            margin-top: 25px;
            display: block;
            text-decoration: none;
            color: #888;
            font-size: 13px;
        }

        .footer-link:hover { color: var(--navy); }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Hospital Logo -->
        <div class="logo-box">
            <img src="images/logo.png" alt="Hospital Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
        </div>

        <!-- Hospital Name -->
        <div class="hospital-name">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</div>
        <div class="login-title">অ্যাডমিন লগইন প্যানেল</div>

        <!-- Error Message -->
        <?php if($error != ""): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="ইউজারনেম লিখুন" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="পাসওয়ার্ড লিখুন" required>
            </div>

            <button type="submit" name="login" class="login-btn">
                প্রবেশ করুন <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>

        <a href="index.php" class="footer-link">← হোম পেজে ফিরে যান</a>
    </div>

</body>
</html>