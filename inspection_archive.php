<?php
include 'db.php';

// ১. লগইন ভেরিফিকেশন
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit(); 
}

// --- মালিকের জন্য সিকিউরিটি কোড ---
$owner_security_code = "SAKIB@45";

// ২. সিকিউরিটি কোড সহ চিরতরে ডিলিট করার লজিক
if (isset($_GET['delete_id']) && isset($_GET['code'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $provided_code = $_GET['code'];

    if ($provided_code === $owner_security_code) {
        $res = mysqli_query($conn, "SELECT visitor_signature FROM hospital_inspections WHERE id='$delete_id'");
        $row = mysqli_fetch_assoc($res);
        
        if($row && !empty($row['visitor_signature'])) {
            $file_path = "uploads/signatures/" . $row['visitor_signature'];
            if (file_exists($file_path)) { unlink($file_path); }
        }

        mysqli_query($conn, "DELETE FROM hospital_inspections WHERE id='$delete_id'");
        echo "<script>alert('রেকর্ডটি সফলভাবে ডিলিট করা হয়েছে!'); window.location.href='inspection_archive.php';</script>";
    } else {
        echo "<script>alert('ভুল সিকিউরিটি কোড!'); window.location.href='inspection_archive.php';</script>";
    }
}

// ৩. পুনরায় ড্যাশবোর্ডে পাঠানোর লজিক (Restore)
if (isset($_GET['restore_id'])) {
    $restore_id = mysqli_real_escape_string($conn, $_GET['restore_id']);
    mysqli_query($conn, "UPDATE hospital_inspections SET is_archived=0 WHERE id='$restore_id'");
    echo "<script>alert('রেকর্ডটি পুনরায় একটিভ তালিকায় পাঠানো হয়েছে।'); window.location.href='inspection_archive.php';</script>";
}

// ৪. আর্কাইভ থেকে ডাটা নিয়ে আসা
$archive = mysqli_query($conn, "SELECT * FROM hospital_inspections WHERE is_archived=1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ডিজিটাল পরিদর্শন রেজিস্টার - পেশেন্ট কেয়ার হাসপাতাল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        :root {
            --navy: #0D1B3E;
            --teal: #00A8B5;
            --gold: #ED8F03;
            --paper: #fffef0;
            --danger: #e84118;
            --success: #10b981;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            background: linear-gradient(135deg, #2c3e50, #000000); 
            font-family: 'Hind Siliguri', sans-serif; 
            padding: 20px; color: #333;
            min-height: 100vh;
        }
        
        .book-container { width: 100%; max-width: 900px; margin: 0 auto; padding-bottom: 80px; }
        
        /* --- Top Header and Nav --- */
        .page-header {
            background: linear-gradient(135deg, var(--navy), var(--teal));
            color: white; padding: 30px 15px; text-align: center;
            border-radius: 20px 20px 0 0; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border-bottom: 5px solid var(--gold);
        }
        .page-header img { width: 70px; height: 70px; background: white; border-radius: 50%; padding: 5px; margin-bottom: 10px; }

        .top-nav { 
            display: flex; justify-content: space-between; align-items: center; 
            background: white; padding: 15px; border-radius: 0 0 20px 20px; margin-bottom: 30px;
        }

        /* --- Register Page Design --- */
        .register-page {
            background: var(--paper);
            padding: 50px 60px;
            margin-bottom: 40px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            border: 1px solid #dcdde1;
            position: relative;
            min-height: 900px;
            animation: slideUp 0.8s ease-out;
        }

        /* Ledger margin line */
        .register-page::before {
            content: ""; position: absolute; left: 50px; top: 0; bottom: 0;
            width: 2px; background: rgba(255, 0, 0, 0.15);
        }

        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .book-inner-header { text-align: center; border-bottom: 2px solid var(--navy); padding-bottom: 10px; margin-bottom: 30px; }
        .book-inner-header h1 { font-size: 22px; color: var(--navy); text-transform: uppercase; }
        
        .entry-row { margin-bottom: 15px; font-size: 17px; line-height: 1.6; display: flex; flex-wrap: wrap; }
        .label { font-weight: bold; color: #000; min-width: 140px; display: inline-block; }

        .comment-text {
            background: rgba(0, 168, 181, 0.05);
            padding: 20px; border-radius: 10px; border-left: 5px solid var(--teal);
            font-style: italic; margin: 20px 0; font-size: 18px; line-height: 1.8;
        }

        /* Signature area */
        .sign-area { 
            margin-top: 50px; float: right; width: 300px; text-align: left; 
            padding: 15px; border: 1px solid #eee; border-radius: 10px; background: rgba(255,255,255,0.5); 
        }
        .signature-img { max-width: 100%; height: 70px; display: block; margin-bottom: 5px; filter: contrast(1.2); }
        .sign-line { border-top: 2px solid #000; margin-bottom: 8px; width: 100%; }

        /* Buttons */
        .btn { padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: bold; color: white; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-back { background: var(--navy); }
        .btn-restore { background: var(--success); font-size: 12px; }
        .btn-delete { background: var(--danger); font-size: 12px; }
        .btn-print-fixed { position: fixed; bottom: 30px; right: 30px; background: var(--gold); z-index: 1000; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .action-btns { position: absolute; top: 15px; right: 15px; display: flex; gap: 10px; }

        /* --- Mobile Responsive --- */
        @media (max-width: 768px) {
            body { padding: 10px; }
            .register-page { padding: 30px 15px 30px 45px; min-height: auto; }
            .register-page::before { left: 35px; }
            .sign-area { float: none; width: 100%; margin-top: 30px; }
            .book-inner-header h1 { font-size: 18px; }
            .action-btns { position: relative; top: 0; right: 0; margin-bottom: 20px; justify-content: flex-end; }
            .top-nav { flex-direction: column; gap: 10px; }
        }

        /* --- Print Mode Settings --- */
        @media print {
            body { background: white !important; padding: 0; }
            .btn, .top-nav, .action-btns { display: none !important; }
            .page-header { background: white !important; color: black !important; border-bottom: 2px solid black; box-shadow: none; border-radius: 0; }
            .page-header img { border: 1px solid black; }
            .register-page { box-shadow: none !important; border: 1px solid #eee !important; page-break-after: always; margin: 0; padding: 40px; min-height: 100vh; }
            .book-container { max-width: 100%; margin: 0; }
            .register-page::before { background: rgba(255, 0, 0, 0.3); }
        }
    </style>
</head>
<body>

    <div class="book-container">
        <!-- Full Page Header for A4 -->
        <header class="page-header">
            <img src="images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
            <h1>পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
            <p><i class="fas fa-map-marker-alt"></i> কলেজ রোড, বরগুনা</p>
        </header>

        <div class="top-nav">
            <a href="view_inspections.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> তালিকায় ফিরুন</a>
            <h2 style="font-size: 1.1rem; color: var(--navy);"><i class="fas fa-book"></i> ডিজিটাল পরিদর্শন বই আরকাইভ</h2>
        </div>

        <?php if(mysqli_num_rows($archive) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($archive)): ?>
            <div class="register-page">
                
                <!-- Action Buttons Inside Page -->
                <div class="action-btns">
                    <a href="inspection_archive.php?restore_id=<?php echo $row['id']; ?>" class="btn btn-restore"><i class="fas fa-undo"></i> Restore</a>
                    <button onclick="secureDelete(<?php echo $row['id']; ?>)" class="btn btn-delete"><i class="fas fa-trash"></i> Delete</button>
                </div>

                <div class="book-inner-header">
                    <h1>পরিদর্শন মন্তব্য প্রতিবেদন</h1>
                    <p style="font-size: 12px; color: #777;">অফিসিয়াল ডিজিটাল রেকর্ড</p>
                </div>

                <div class="entry-row">
                    <span class="label"><i class="fas fa-calendar-alt"></i> তারিখ:</span>
                    <span><?php echo date('d F, Y | h:i A', strtotime($row['submitted_at'])); ?></span>
                </div>

                <div class="entry-row">
                    <span class="label"><i class="fas fa-thumbtack"></i> উদ্দেশ্য:</span>
                    <span><?php echo htmlspecialchars($row['purpose']); ?></span>
                </div>

                <div class="comment-text">
                    "<?php echo nl2br(htmlspecialchars($row['comments'])); ?>"
                </div>

                <?php if(!empty($row['suggestions'])): ?>
                    <div style="margin-top: 20px;">
                        <p><strong><i class="fas fa-lightbulb" style="color: var(--gold);"></i> বিশেষ পরামর্শ ও দিকনির্দেশনা:</strong></p>
                        <p style="margin-top: 5px; color: #555; padding-left: 10px;"><?php echo nl2br(htmlspecialchars($row['suggestions'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="sign-area">
                    <div style="height: 80px; display: flex; align-items: center; justify-content: center;">
                        <?php if(!empty($row['visitor_signature'])): ?>
                            <img src="uploads/signatures/<?php echo $row['visitor_signature']; ?>" class="signature-img" alt="Signature">
                        <?php else: ?>
                            <span style="color:#ccc; font-size:11px;">(স্বাক্ষর নেই)</span>
                        <?php endif; ?>
                    </div>
                    <div class="sign-line"></div>
                    <p><strong>স্বাক্ষরকারী:</strong> <?php echo htmlspecialchars($row['visitor_name']); ?></p>
                    <p style="font-size:12px; color:#666;">পদবী: <?php echo htmlspecialchars($row['designation']); ?></p>
                    <p style="font-size:11px; color:#888;">ঠিকানা: <?php echo htmlspecialchars($row['visitor_address']); ?></p>
                </div>
                
                <div style="clear:both;"></div>
                
                <!-- Page Footer Info -->
                <div style="position:absolute; bottom:30px; left:60px; color:#aaa; font-size:11px;">
                    রেকর্ড আইডি: #PC-<?php echo $row['id']; ?> | পেশেন্ট কেয়ার হাসপাতাল
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding: 100px; background: white; border-radius: 20px;">
                <i class="fas fa-folder-open" style="font-size: 50px; color: #eee;"></i>
                <p style="margin-top: 15px; color: #999;">আর্কাইভ বর্তমানে খালি আছে।</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Floating Print Button -->
    <button class="btn btn-print-fixed" onclick="window.print()"><i class="fas fa-print"></i> প্রিন্ট / PDF</button>

    <script>
        function secureDelete(id) {
            let code = prompt("রেকর্ডটি চিরতরে ডিলিট করতে সিকিউরিটি কোড দিন:");
            if (code != null && code != "") {
                window.location.href = "inspection_archive.php?delete_id=" + id + "&code=" + encodeURIComponent(code);
            }
        }
    </script>
</body>
</html>