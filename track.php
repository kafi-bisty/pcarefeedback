<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অবস্থা ট্র্যাকিং - পেশেন্ট কেয়ার হাসপাতাল</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        body { background-color: #f0f4f8; font-family: 'Hind Siliguri', sans-serif; margin: 0; padding: 20px; }
        .track-main-container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); overflow: hidden; }

        /* Header Style */
        .track-header { padding: 30px 20px; text-align: center; color: #fff; transition: 0.5s; }
        .bg-praise-header { background: linear-gradient(135deg, #28a745, #1e7e34); } /* প্রশংসার জন্য সবুজ */
        .bg-complaint-header { background: linear-gradient(135deg, #dc3545, #a71d2a); } /* অভিযোগের জন্য লাল */
        .bg-default-header { background: linear-gradient(135deg, #00A8B5, #0056b3); }

        .logo-img { width: 80px; height: 80px; background: #fff; border-radius: 50%; padding: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); margin-bottom: 15px; }
        .hospital-name { font-size: 20px; font-weight: 700; margin: 0; }

        .track-body { padding: 30px 20px; text-align: center; }
        .search-box { margin-bottom: 30px; display: flex; justify-content: center; gap: 10px; }
        .search-box input { width: 60%; padding: 14px; border: 2px solid #eee; border-radius: 12px; font-size: 16px; outline: none; text-transform: uppercase; }
        .search-box button { padding: 14px 25px; background: #00A8B5; color: #fff; border: none; border-radius: 12px; cursor: pointer; }

        /* Progress Tracker */
        .progress-tracker { display: flex; justify-content: space-between; margin: 40px 0; position: relative; padding: 0 10px; }
        .progress-tracker::before { content: ""; position: absolute; top: 18px; left: 10%; width: 80%; height: 4px; background: #eee; z-index: 1; }
        .step { position: relative; z-index: 2; background: #fff; width: 40px; height: 40px; border-radius: 50%; border: 4px solid #eee; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .step.completed { background: #00A8B5; border-color: #00A8B5; color: #fff; }
        .step-label { position: absolute; top: 45px; font-size: 12px; width: 100px; left: -30px; color: #666; font-weight: bold; }

        .status-card { text-align: left; padding: 25px; border-radius: 20px; background: #f9fbfd; border: 1px solid #eef2f7; animation: fadeInUp 0.5s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .reply-box { background: #e7f3ff; border-left: 6px solid #007bff; padding: 20px; border-radius: 12px; margin-top: 20px; }
        .badge { padding: 6px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; float: right; text-transform: uppercase; }
        .bg-pending { background: #ffc107; color: #000; }
        .bg-investigation { background: #d1ecf1; color: #0c5460; }
        .bg-resolved { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<?php
$row = null;
$typeClass = 'bg-default-header';
$headerTitle = "ফিডব্যাক ট্র্যাকিং";

if (isset($_GET['tid'])) {
    $tid = mysqli_real_escape_string($conn, $_GET['tid']);
    $query = "SELECT * FROM hospital_feedback WHERE tracking_id = '$tid'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        // ধরন অনুযায়ী টাইটেল ও কালার পরিবর্তন
        if ($row['type'] == 'Praise') {
            $typeClass = 'bg-praise-header';
            $headerTitle = "প্রশংসার অবস্থা দেখুন";
            $messageTitle = "আপনার প্রশংসা ছিল:";
        } else {
            $typeClass = 'bg-complaint-header';
            $headerTitle = "অভিযোগের অবস্থা দেখুন";
            $messageTitle = "আপনার অভিযোগ ছিল:";
        }
    }
}
?>

<div class="track-main-container">
    <!-- Header with Dynamic Color -->
    <div class="track-header <?php echo $typeClass; ?>">
        <img src="images/logo.png" alt="Logo" class="logo-img">
        <div class="hospital-name">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</div>
        <h2 style="margin-top:15px;"><?php echo $headerTitle; ?></h2>
    </div>

    <div class="track-body">
        <div class="search-box">
            <form method="GET" action="" style="width: 100%; display: flex; justify-content: center; gap: 10px;">
                <input type="text" name="tid" placeholder="PCARE1234" value="<?php echo $_GET['tid'] ?? ''; ?>" required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <?php if ($row): 
            $status = strtolower($row['status']);
        ?>
            <!-- Progress Tracker -->
            <div class="progress-tracker">
                <div class="step completed"><i class="fas fa-check"></i><div class="step-label">জমা হয়েছে</div></div>
                <div class="step <?php echo ($status=='investigation' || $status=='resolved') ? 'completed' : ''; ?>">
                    <i class="fas <?php echo ($status=='investigation' || $status=='resolved') ? 'fa-check' : 'fa-spinner fa-spin'; ?>"></i>
                    <div class="step-label">যাচাই চলছে</div>
                </div>
                <div class="step <?php echo ($status=='resolved') ? 'completed' : ''; ?>">
                    <i class="fas <?php echo ($status=='resolved') ? 'fa-check' : 'fa-flag'; ?>"></i>
                    <div class="step-label">সমাধান</div>
                </div>
            </div>

            <div class="status-card">
                <span class="badge bg-<?php echo $status; ?>"><?php echo $row['status']; ?></span>
                <p>ট্র্যাকিং আইডি: <strong><?php echo $row['tracking_id']; ?></strong></p>
                <p>বিভাগ: <strong><?php echo $row['department']; ?></strong></p>
                <hr>
                <p><strong><?php echo $messageTitle; ?></strong></p>
                <p style="color: #555;"><?php echo nl2br($row['message']); ?></p>
                
                <div class="reply-box">
                    <strong><i class="fas fa-user-shield"></i> কর্তৃপক্ষের জবাব:</strong><br>
                    <p style="margin-top: 8px; color: #333;">
                        <?php echo $row['owner_reply'] ? nl2br($row['owner_reply']) : "আপনার ফিডব্যাকটি নিয়ে আমরা কাজ করছি।"; ?>
                    </p>
                </div>
            </div>
        <?php elseif(isset($_GET['tid'])): ?>
            <div style="color:red; margin-top:20px;"><i class="fas fa-exclamation-triangle"></i> আইডিটি সঠিক নয়!</div>
        <?php endif; ?>

        <br><a href="index.php" style="text-decoration:none; color:#0056b3; font-weight:bold;"><i class="fas fa-arrow-left"></i> ল্যান্ডিং পেজে ফিরে যান</a>
    </div>
</div>

</body>
</html>