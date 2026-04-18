<?php
include 'db.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit(); }

// আর্কাইভ করার লজিক
if (isset($_GET['archive_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['archive_id']);
    mysqli_query($conn, "UPDATE hospital_inspections SET is_archived=1 WHERE id='$id'");
    echo "<script>alert('মন্তব্যটি ডিজিটাল বুক আর্কাইভে সফলভাবে জমা করা হয়েছে।'); window.location.href='view_inspections.php';</script>";
}

// শুধুমাত্র যেগুলা আর্কাইভ করা হয়নি সেগুলো দেখাবে
$inspections = mysqli_query($conn, "SELECT * FROM hospital_inspections WHERE is_archived=0 ORDER BY id DESC");
$count = mysqli_num_rows($inspections);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন পরিদর্শন মন্তব্য - পেশেন্ট কেয়ার হাসপাতাল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        :root {
            --navy: #0D1B3E;
            --teal: #00A8B5;
            --gold: #ED8F03;
            --light: #f8fafc;
        }

        body { 
            background: #f0f2f5; 
            font-family: 'Hind Siliguri', sans-serif; 
            margin: 0; 
            padding-bottom: 50px;
        }

        /* --- Header Section Adjusted with Logo --- */
        .hospital-header {
            background: linear-gradient(135deg, var(--navy), var(--teal));
            color: white; 
            padding: 40px 20px; 
            text-align: center; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
            position: relative;
        }

        /* লোগো কন্টেইনার অ্যাডজাস্টমেন্ট */
        .logo-container {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
            border: 4px solid rgba(255, 255, 255, 0.3);
            padding: 5px;
            transition: 0.3s;
        }
        
        .logo-container:hover {
            transform: scale(1.05);
        }

        .logo-container img {
            max-width: 85%;
            height: auto;
            object-fit: contain;
        }

        .hospital-header h1 { 
            margin: 0; 
            font-size: 24px; 
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hospital-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        /* --- UI Elements --- */
        .container { width: 95%; max-width: 1000px; margin: 25px auto; }

        .top-nav { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px; 
            gap: 15px;
        }

        .btn-main {
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-dash { background: var(--navy); }
        .btn-archive { background: var(--gold); }
        .btn-main:hover { transform: translateY(-3px); opacity: 0.95; box-shadow: 0 6px 15px rgba(0,0,0,0.2); }

        .section-title { 
            color: var(--navy); 
            border-left: 6px solid var(--gold); 
            padding-left: 15px; 
            margin-bottom: 25px;
            font-weight: 700;
        }

        /* Card Design */
        .inspection-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #eef2f7;
            transition: 0.3s;
        }
        .inspection-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        
        .visitor-info { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .avatar { width: 55px; height: 55px; background: #f0f4f8; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--teal); font-size: 24px; border: 1px solid #ddd; }
        
        .visitor-details h3 { margin: 0; color: var(--navy); font-size: 19px; }
        .visitor-details span { font-size: 13px; color: var(--teal); font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }

        .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; background: #f8fafc; padding: 18px; border-radius: 15px; margin-bottom: 20px; font-size: 14px; border: 1px solid #edf2f7; }
        .meta-item i { color: var(--teal); width: 22px; }

        .comment-box { line-height: 1.7; color: #34495e; font-size: 15px; margin-bottom: 20px; padding: 15px; background: #fffdf0; border-radius: 12px; border-left: 4px solid #ED8F03; }

        .action-area { text-align: right; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .btn-log { background: #2f3542; color: white; padding: 12px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: bold; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-log:hover { background: var(--navy); }

        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 25px; color: #94a3b8; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

        @media (max-width: 600px) {
            .inspection-card { padding: 15px; }
            .btn-main { width: 100%; justify-content: center; }
            .top-nav { flex-direction: column; }
        }
    </style>
</head>
<body>

    <header class="hospital-header">
        <!-- Logo Container for adjustment -->
        <div class="logo-container">
            <img src="images/logo.png" alt="Hospital Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
        </div>
        <h1>পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
        <p><i class="fas fa-user-shield"></i> অ্যাডমিন কন্ট্রোল প্যানেল - পরিদর্শন রিপোর্ট</p>
    </header>

    <div class="container">
        
        <div class="top-nav">
            <a href="admin.php" class="btn-main btn-dash"><i class="fas fa-arrow-left"></i> ড্যাশবোর্ড</a>
            <a href="inspection_archive.php" class="btn-main btn-archive"><i class="fas fa-book"></i> ডিজিটাল পরিদর্শন বুক (আর্কাইভ)</a>
        </div>

        <h2 class="section-title">নতুন পরিদর্শন মন্তব্যসমূহ (<?php echo $count; ?>)</h2>

        <?php if($count > 0): ?>
            <?php while($row = mysqli_fetch_assoc($inspections)): ?>
            <div class="inspection-card">
                <div class="visitor-info">
                    <div class="avatar"><i class="fas fa-user-tie"></i></div>
                    <div class="visitor-details">
                        <h3><?php echo $row['visitor_name']; ?></h3>
                        <span><?php echo $row['designation']; ?></span>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-item"><i class="fas fa-building"></i> <strong>প্রতিষ্ঠান:</strong> <?php echo $row['organization']; ?></div>
                    <div class="meta-item"><i class="fas fa-phone"></i> <strong>মোবাইল:</strong> <?php echo $row['phone']; ?></div>
                    <div class="meta-item"><i class="fas fa-map-marker-alt"></i> <strong>ঠিকানা:</strong> <?php echo $row['visitor_address']; ?></div>
                    <div class="meta-item"><i class="fas fa-calendar-check"></i> <strong>তারিখ:</strong> <?php echo date('d M, Y', strtotime($row['submitted_at'])); ?></div>
                </div>

                <div class="comment-box">
                    <strong>পরিদর্শনের উদ্দেশ্য:</strong> <?php echo $row['purpose']; ?><br>
                    <hr style="border: 0; border-top: 1px solid rgba(0,0,0,0.05); margin: 10px 0;">
                    <strong>মূল মন্তব্য:</strong> <?php echo nl2br($row['comments']); ?>
                    <?php if(!empty($row['suggestions'])): ?>
                        <br><br><strong style="color:var(--gold);">পরামর্শ:</strong> <?php echo nl2br($row['suggestions']); ?>
                    <?php endif; ?>
                </div>

                <div class="action-area">
                    <a href="view_inspections.php?archive_id=<?php echo $row['id']; ?>" class="btn-log" onclick="return confirm('আপনি কি এই মন্তব্যটি পরিদর্শন বইয়ে লিপিবদ্ধ করতে চান?')">
                        <i class="fas fa-save"></i> বইয়ে লিপিবদ্ধ করুন (Archive)
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open" style="font-size: 60px; margin-bottom: 20px; color: #cbd5e0;"></i>
                <h3>বর্তমানে কোনো নতুন মন্তব্য নেই।</h3>
                <p>সব মন্তব্য অলরেডি আর্কাইভে জমা করা হয়েছে।</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>