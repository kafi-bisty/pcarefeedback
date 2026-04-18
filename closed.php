<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাময়িকভাবে বন্ধ - পেশেন্ট কেয়ার হাসপাতাল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        :root {
            --navy: #0D1B3E;
            --teal: #00A8B5;
            --gold: #ED8F03;
        }

        body { 
            background: linear-gradient(135deg, var(--navy) 0%, var(--teal) 100%); 
            font-family: 'Hind Siliguri', sans-serif; 
            margin: 0; padding: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }

        .closed-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 35px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
            max-width: 450px; width: 90%;
            text-align: center;
            border: 2px solid var(--gold);
        }

        .icon-box {
            font-size: 70px;
            color: var(--gold);
            margin-bottom: 20px;
        }

        h2 { color: var(--navy); margin-bottom: 10px; }
        p { color: #555; line-height: 1.6; font-size: 16px; }

        .notice-badge {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 12px;
            font-weight: bold;
            display: inline-block;
            margin: 20px 0;
            border: 1px solid #ffeeba;
        }

        .btn-home {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: var(--navy);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-home:hover { background: var(--teal); }
    </style>
</head>
<body>

    <div class="closed-card">
        <div class="icon-box">
            <i class="fas fa-tools"></i>
        </div>
        
        <img src="images/logo.png" style="max-width: 120px; margin-bottom: 15px;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
        
        <h2>সিস্টেম আপডেট চলছে</h2>
        
        <div class="notice-badge">
            <i class="fas fa-exclamation-triangle"></i> সাময়িকভাবে ফিডব্যাক গ্রহণ বন্ধ আছে
        </div>

        <p>
            সম্মানিত সেবাগ্রহীতা,<br>
            আমাদের অনলাইন ফিডব্যাক এবং পরিদর্শন মন্তব্য বই সিস্টেমটি বর্তমানে কিছু কারিগরি উন্নতির জন্য সাময়িকভাবে বন্ধ রাখা হয়েছে। 
        </p>
        
        <p style="font-weight: bold; color: var(--teal);">আমরা খুব শীঘ্রই আবার আপনাদের সেবায় ফিরে আসবো।</p>

        <a href="index.php" class="btn-home">হোম পেজে ফিরে যান</a>
        
        <div style="margin-top: 30px; font-size: 12px; color: #999;">
            © ২০২৪ পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার
        </div>
    </div>

</body>
</html>