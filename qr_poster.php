<?php
include 'db.php';
// ডাটাবেস থেকে বর্তমান লিঙ্ক নেওয়া
$qr_fetch = mysqli_query($conn, "SELECT redirect_url FROM qr_settings WHERE id=1");
$qr_row = mysqli_fetch_assoc($qr_fetch);
$current_url = ($qr_row) ? $qr_row['redirect_url'] : 'index.php';
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official QR Poster - Patient Care</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        :root {
            --navy: #0D1B3E;
            --teal: #00A8B5;
            --gold: #ED8F03;
            --white: #ffffff;
            --light-teal: rgba(0, 168, 181, 0.05);
        }

        * { box-sizing: border-box; -webkit-print-color-adjust: exact; margin: 0; padding: 0; }

        body { 
            background: #f0f0f0; 
            font-family: 'Hind Siliguri', sans-serif; 
        }

        /* --- A4 Page Setup --- */
        .a4-page {
            width: 210mm;
            height: 297mm;
            padding: 8mm;
            margin: 10px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* লোগো কালার ফ্রেম */
        .outer-frame {
            border: 6mm solid var(--navy);
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 8mm;
            border-image: linear-gradient(to bottom, var(--navy), var(--teal)) 1;
            justify-content: space-between; /* এটি সব অংশকে সমানভাবে ছড়িয়ে দিবে */
        }

        /* Header */
        .header { text-align: center; border-bottom: 3px solid var(--teal); padding-bottom: 15px; }
        .logo-box { width: 90px; height: 90px; margin: 0 auto 10px; }
        .logo-box img { width: 100%; height: auto; }
        .h-name { font-size: 26pt; color: var(--navy); margin: 0; font-weight: 800; line-height: 1.1; }
        .h-addr { font-size: 14pt; color: var(--teal); margin: 5px 0; font-weight: 600; }

        /* Instruction Area */
        .instruction {
            font-size: 20pt;
            color: #222;
            font-weight: 800;
            text-align: center;
            margin-top: 15px;
        }
        .instruction span { color: var(--teal); border-bottom: 3px solid var(--teal); }

        /* QR Code Container */
        .qr-wrapper {
            margin: 10px auto;
            padding: 15px;
            background: white;
            border: 3px solid var(--navy);
            border-radius: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* ডিজিটাল সেবাসমূহ Section */
        .services-container {
            width: 100%;
            background: var(--light-teal);
            padding: 18px;
            border-radius: 20px;
            border: 2px solid var(--teal);
        }
        .services-container h3 { 
            color: var(--navy); 
            font-size: 18pt; 
            margin-bottom: 15px; 
            text-align: center;
            border-bottom: 2px solid var(--teal);
            padding-bottom: 5px;
        }
        .service-list { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
        }
        .item { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            font-size: 15pt; 
            font-weight: 700; 
            color: #333; 
        }
        .item i { color: var(--teal); font-size: 16pt; width: 25px; text-align: center; }

        /* Offline Contact Box */
        .contact-box {
            border: 2px dashed var(--navy);
            padding: 12px;
            border-radius: 20px;
            background: #fff;
            text-align: center;
        }
        .contact-box h3 { margin: 0; color: var(--navy); font-size: 16pt; font-weight: 800; }
        .contact-box .phone-number { 
            font-size: 26pt; 
            font-weight: 900; 
            color: var(--gold); 
            margin: 5px 0; 
            display: block;
        }
        .contact-box p { margin: 0; font-size: 12pt; color: #555; font-weight: 600; }

        /* Footer */
        .footer {
            background: var(--navy);
            color: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }
        .footer h4 { margin: 0; font-size: 16pt; }
        .footer p { margin: 2px 0 0; font-size: 11pt; opacity: 0.8; }

        .system-tag { margin-top: 5px; font-size: 8pt; color: #999; text-align: center; text-transform: uppercase; }

        /* Print Button - Browser Only */
        .btn-print {
            position: fixed; top: 20px; right: 20px;
            background: #28a745; color: white; border: none;
            padding: 12px 24px; border-radius: 50px; font-size: 16px;
            font-weight: bold; cursor: pointer; box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            z-index: 9999;
        }

        @media print {
            body { background: white; }
            .a4-page { margin: 0; border: none; box-shadow: none; width: 210mm; height: 297mm; }
            .btn-print { display: none; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> এখনই প্রিন্ট করুন</button>

    <div class="a4-page">
        <div class="outer-frame">
            
            <!-- Header -->
            <div class="header">
                <div class="logo-box">
                    <img src="images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
                </div>
                <h1 class="h-name">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
                <p class="h-addr"><i class="fas fa-map-marker-alt"></i> কলেজ রোড, বরগুনা</p>
            </div>

            <!-- Scan Instruction -->
            <div class="instruction">
                স্মার্টফোনের ক্যামেরা দিয়ে <br><span>কিউআর কোডটি স্ক্যান করুন</span>
            </div>

            <!-- QR Code Area -->
            <div class="qr-wrapper">
                <div id="qrcode"></div>
            </div>

            <!-- ডিজিটাল সেবাসমূহ Section -->
            <div class="services-container">
                <h3>ডিজিটাল সেবাসমূহ:</h3>
                <div class="service-list">
                    <div class="item"><i class="fas fa-heart"></i> ভালো কাজের প্রশংসা</div>
                    <div class="item"><i class="fas fa-edit"></i> পরিদর্শন মন্তব্য দান</div>
                    <div class="item"><i class="fas fa-exclamation-triangle"></i> যে কোনো অভিযোগ</div>
                    <div class="item"><i class="fas fa-search-location"></i> অভিযোগ ট্র্যাকিং</div>
                </div>
            </div>

            <!-- Offline Support Section -->
            <div class="contact-box">
                <h3>স্মার্টফোন নেই? সরাসরি যোগাযোগ করুন:</h3>
                <div class="phone-number"><i class="fas fa-phone-alt"></i> ০১৯১১১১৪৫৩৪</div>
                <p>(কল বা এসএমএস-এর মাধ্যমে আপনার মতামত জানান)</p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <h4>আপনার সুচিন্তিত মতামত আমাদের পথপ্রদর্শক</h4>
                <p>কর্তৃপক্ষ - পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</p>
            </div>

            <div class="system-tag">
                Digital Feedback Management System | PC-Hospital
            </div>

        </div>
    </div>

    <script>
        var siteUrl = window.location.origin + window.location.pathname.replace('qr_poster.php', 'qr.php'); 
        new QRCode(document.getElementById("qrcode"), {
            text: siteUrl,
            width: 200, // সাইজ কমানো হয়েছে যাতে পুরো পেজে ফিট হয়
            height: 200,
            colorDark : "#0D1B3E",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>