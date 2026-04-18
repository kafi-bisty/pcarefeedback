<?php
include 'db.php';

// লগইন ভেরিফিকেশন
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit(); 
}

// --- মালিকের জন্য সিকিউরিটি কোড (এটি পরিবর্তন করতে পারেন) ---
$owner_security_code = "SAKIB@45"; // উদাহরণস্বরূপ কোড:SAKIB@45

// ১. সিকিউরিটি কোড সহ চিরতরে ডিলিট করার লজিক
if (isset($_GET['delete_id']) && isset($_GET['code'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $provided_code = $_GET['code'];

    if ($provided_code === $owner_security_code) {
        // প্রথমে স্বাক্ষরের ফাইলের নাম খুঁজে বের করা (সার্ভার থেকে ডিলিট করার জন্য)
        $res = mysqli_query($conn, "SELECT visitor_signature FROM hospital_inspections WHERE id='$delete_id'");
        $row = mysqli_fetch_assoc($res);
        
        if($row && !empty($row['visitor_signature'])) {
            $file_path = "uploads/signatures/" . $row['visitor_signature'];
            if (file_exists($file_path)) {
                unlink($file_path); // ফাইল ডিলিট
            }
        }

        // ডাটাবেস থেকে ডিলিট করা
        mysqli_query($conn, "DELETE FROM hospital_inspections WHERE id='$delete_id'");
        echo "<script>alert('রেকর্ডটি সফলভাবে ডিলিট করা হয়েছে!'); window.location.href='inspection_archive.php';</script>";
    } else {
        echo "<script>alert('ভুল সিকিউরিটি কোড! ডিলিট করা সম্ভব হয়নি।'); window.location.href='inspection_archive.php';</script>";
    }
}

// ২. পুনরায় ড্যাশবোর্ডে পাঠানোর লজিক
if (isset($_GET['restore_id'])) {
    $restore_id = mysqli_real_escape_string($conn, $_GET['restore_id']);
    mysqli_query($conn, "UPDATE hospital_inspections SET is_archived=0 WHERE id='$restore_id'");
    echo "<script>alert('রেকর্ডটি পুনরায় একটিভ তালিকায় পাঠানো হয়েছে।'); window.location.href='inspection_archive.php';</script>";
}

// আর্কাইভ থেকে ডাটা নিয়ে আসা
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
        }

        body { 
            background: linear-gradient(135deg, #2c3e50, #000000); 
            font-family: 'Hind Siliguri', sans-serif; 
            margin: 0; padding: 20px; color: #333;
        }
        
        .book-container { max-width: 900px; margin: 0 auto; padding-bottom: 80px; }
        
        .register-page {
            background: var(--paper);
            padding: 50px 60px;
            margin-bottom: 40px;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid #dcdde1;
            position: relative;
            min-height: 900px;
            animation: fadeIn 0.8s ease-out;
        }

        .register-page::before {
            content: ""; position: absolute; left: 50px; top: 0; bottom: 0;
            width: 2px; background: rgba(255, 0, 0, 0.15);
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .book-header { text-align: center; border-bottom: 3px double var(--navy); padding-bottom: 15px; margin-bottom: 40px; }
        .book-header h1 { margin: 0; font-size: 24px; color: var(--navy); }
        
        .entry-content { font-size: 17px; line-height: 2; }
        .label { font-weight: bold; color: #000; min-width: 140px; display: inline-block; }

        .comment-text {
            background: rgba(0, 168, 181, 0.05);
            padding: 20px; border-radius: 10px; border-left: 5px solid var(--teal);
            font-style: italic; margin: 20px 0; font-size: 18px;
        }

        .sign-area { margin-top: 60px; float: right; width: 320px; text-align: left; padding: 15px; border: 1px solid #eee; border-radius: 10px; background: rgba(255,255,255,0.5); }
        .signature-img { max-width: 200px; max-height: 80px; display: block; margin-bottom: 5px; }
        .sign-line { border-top: 2px solid #000; margin-bottom: 8px; }

        .top-nav { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .btn-action { padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: bold; color: white; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; border: none; cursor: pointer; }
        
        .btn-back { background: #353b48; }
        .btn-restore { background: var(--teal); position: absolute; top: 20px; right: 110px; font-size: 12px; }
        .btn-delete { background: var(--danger); position: absolute; top: 20px; right: 20px; font-size: 12px; }
        .btn-print-fixed { position: fixed; bottom: 30px; right: 30px; background: var(--gold); z-index: 1000; }

        @media print {
            .btn-action, .btn-restore, .btn-delete, .btn-print-fixed { display: none !important; }
            .register-page { box-shadow: none; border: none; page-break-after: always; min-height: auto; }
        }
    </style>
</head>
<body>

    <div class="book-container">
        <div class="top-nav">
            <a href="view_inspections.php" class="btn-action btn-back"><i class="fas fa-arrow-left"></i> তালিকা ফিরে যান</a>
            <h2 style="color:white; margin:0;"><i class="fas fa-book"></i> ডিজিটাল পরিদর্শন বই</h2>
        </div>

        <button class="btn-action btn-print-fixed" onclick="window.print()"><i class="fas fa-print"></i> প্রিন্ট করুন</button>

        <?php if(mysqli_num_rows($archive) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($archive)): ?>
            <div class="register-page">
                <!-- Restore and Delete Buttons with Security -->
                <a href="inspection_archive.php?restore_id=<?php echo $row['id']; ?>" class="btn-action btn-restore"><i class="fas fa-undo"></i> Restore</a>
                <button onclick="secureDelete(<?php echo $row['id']; ?>)" class="btn-action btn-delete"><i class="fas fa-trash"></i> Delete</button>

                <div class="book-header">
                    <h1>পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
                    <p>কলেজ রোড,বরগুনা</p>
                    <div style="margin-top:10px; font-weight:bold; text-decoration: underline;">পরিদর্শন মন্তব্য প্রতিবেদন</div>
                </div>

                <div class="entry-content">
                    <p><span class="label"><i class="fas fa-calendar-alt"></i> তারিখ:</span> <?php echo date('d/m/Y', strtotime($row['submitted_at'])); ?></p>
                    <p><span class="label"><i class="fas fa-thumbtack"></i> উদ্দেশ্য:</span> <?php echo $row['purpose']; ?></p>
                    <div class="comment-text">"<?php echo nl2br($row['comments']); ?>"</div>
                    <?php if(!empty($row['suggestions'])): ?>
                        <p><strong>পরামর্শ:</strong> <?php echo nl2br($row['suggestions']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="sign-area">
                    <?php if(!empty($row['visitor_signature'])): ?>
                        <img src="uploads/signatures/<?php echo $row['visitor_signature']; ?>" class="signature-img">
                    <?php endif; ?>
                    <div class="sign-line"></div>
                    <p><strong>স্বাক্ষরকারী:</strong> <?php echo $row['visitor_name']; ?></p>
                    <p style="font-size:12px;">পদবী: <?php echo $row['designation']; ?><br>ঠিকানা: <?php echo $row['visitor_address']; ?></p>
                </div>
                <div style="clear:both;"></div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:white; text-align:center;">আর্কাইভ বুক খালি!</p>
        <?php endif; ?>
    </div>

    <script>
        function secureDelete(id) {
            let code = prompt("রেকর্ডটি ডিলিট করার জন্য মালিকের সিকিউরিটি কোড দিন:");
            if (code != null && code != "") {
                window.location.href = "inspection_archive.php?delete_id=" + id + "&code=" + code;
            }
        }
    </script>
</body>
</html>