<?php
include 'db.php';

// ১. লগইন ভেরিফিকেশন
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// ২. এরর সমাধান: সবার আগে $current_url ডিফাইন করা
$current_url = 'index.php'; 
$qr_fetch = mysqli_query($conn, "SELECT redirect_url FROM qr_settings WHERE id=1");
if ($qr_fetch && mysqli_num_rows($qr_fetch) > 0) {
    $qr_row = mysqli_fetch_assoc($qr_fetch);
    $current_url = $qr_row['redirect_url'];
}

// ৩. আপডেট লজিক (QR & Status)
if (isset($_POST['update_qr'])) {
    $new_url = mysqli_real_escape_string($conn, $_POST['new_url']);
    mysqli_query($conn, "UPDATE qr_settings SET redirect_url='$new_url' WHERE id=1");
    echo "<script>alert('QR গন্তব্য পরিবর্তন হয়েছে!'); window.location.href='admin.php';</script>";
    exit();
}

if (isset($_POST['update_action'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']); 
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $owner_reply = mysqli_real_escape_string($conn, $_POST['owner_reply']);
    mysqli_query($conn, "UPDATE hospital_feedback SET status='$status', owner_reply='$owner_reply' WHERE id='$id'");
    echo "<script>alert('সফলভাবে আপডেট হয়েছে!'); window.location.href='admin.php';</script>";
    exit();
}

// ৪. আর্কাইভ লজিক
if (isset($_GET['archive_id'])) {
    $archive_id = mysqli_real_escape_string($conn, $_GET['archive_id']);
    mysqli_query($conn, "UPDATE hospital_feedback SET is_archived=1 WHERE id='$archive_id'");
    header("Location: admin.php");
    exit();
}

// ৫. পরিসংখ্যান (Active Data Only)
$total_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_feedback WHERE is_archived=0"))['total'];
$praise_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_feedback WHERE type='Praise' AND is_archived=0"))['total'];
$complaint_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_feedback WHERE type='Complaint' AND is_archived=0"))['total'];
$total_inspections = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_inspections WHERE is_archived=0"))['total'];

// ৬. ফিল্টার কুয়েরি (ফিডব্যাক টেবিলের জন্য)
$filter = $_GET['filter'] ?? 'all';
$condition = ($filter == 'all') ? "is_archived=0" : "type='$filter' AND is_archived=0";
$feedback_result = mysqli_query($conn, "SELECT * FROM hospital_feedback WHERE $condition ORDER BY id DESC");

// ৭. পরিদর্শন মন্তব্য বই এর ডাটা তুলে আনা (Active Only)
$inspections_result = mysqli_query($conn, "SELECT * FROM hospital_inspections WHERE is_archived=0 ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Patient Care Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Orbitron:wght@500;700&display=swap');
        
        :root {
            --primary: #00A8B5;
            --secondary: #0056b3;
            --gold: #ED8F03;
            --dark: #0D1B3E;
            --light-bg: #f4f7fa;
            --card-bg: #ffffff;
            --text-color: #2f3542;
            --neon-blue: #00f2fe;
        }

        body.dark-mode {
            --light-bg: #020617;
            --card-bg: #1e293b;
            --text-color: #f1f5f9;
        }

        body { background-color: var(--light-bg); color: var(--text-color); font-family: 'Hind Siliguri', sans-serif; margin: 0; transition: 0.3s; }

        .hospital-header { background: linear-gradient(135deg, #0D1B3E, var(--primary)); color: white; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .hospital-header img { width: 70px; margin-bottom: 10px; }

        .admin-container { width: 95%; max-width: 1400px; margin: 20px auto; }
        
        .nav-bar { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; background: var(--card-bg); padding: 15px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .theme-toggle { background: rgba(0,0,0,0.05); padding: 8px 15px; border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: bold; }
        
        .btn-link { padding: 10px 18px; border-radius: 10px; text-decoration: none; color: white; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 13px; }
        .btn-history { background: #2f3542; }
        .btn-inspection { background: var(--gold); }
        .btn-logout { background: #ff4757; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); padding: 20px; border-radius: 20px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-bottom: 5px solid var(--primary); }
        .stat-card p { font-size: 30px; font-weight: bold; margin: 10px 0 0; }

        .qr-card { background: var(--card-bg); padding: 30px; border-radius: 20px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center; justify-content: space-around; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        #digital-clock { font-family: 'Orbitron', sans-serif; font-size: 18px; font-weight: bold; }
        body.dark-mode #digital-clock { color: var(--neon-blue); text-shadow: 0 0 15px var(--neon-blue); }

        .table-wrapper { background: var(--card-bg); border-radius: 20px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        th { background: rgba(0,0,0,0.03); padding: 15px; color: #747d8c; text-align: left; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); vertical-align: top; font-size: 14px; }
        
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; display: inline-block; margin-bottom: 10px; }
        .Pending { background: #fff3cd; color: #856404; }
        .investigation { background: #d1ecf1; color: #0c5460; }
        .Resolved { background: #d4edda; color: #155724; }

        .q-btn { font-size: 10px; padding: 5px 8px; cursor: pointer; border-radius: 5px; border: 1px solid #ddd; background: var(--card-bg); color: var(--text-color); margin: 2px; display: inline-block; }
        textarea { width: 100%; border-radius: 10px; padding: 8px; background: var(--light-bg); color: var(--text-color); border: 1px solid #ddd; margin-top: 5px; }

        .section-header { display: flex; justify-content: space-between; align-items: center; margin: 30px 0 15px; border-left: 5px solid var(--gold); padding-left: 15px; }

        .owner-card { background: var(--card-bg); padding: 20px 40px; border-radius: 20px; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.1); cursor: pointer; margin: 40px auto; width: fit-content; border: 1px solid #eee; }
        .modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); }
        .modal-content { background: var(--card-bg); margin: 8% auto; border-radius: 25px; width: 90%; max-width: 400px; overflow: hidden; animation: zoomIn 0.3s; position: relative; }
        @keyframes zoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body>

    <header class="hospital-header">
        <img src="images/logo.png" alt="Logo">
        <h1>পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার - অ্যাডমিন</h1>
    </header>

    <div class="admin-container">
        
        <!-- Top Nav Bar -->
        <div class="nav-bar">
            <div id="digital-clock">00:00:00 AM</div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <div class="theme-toggle" onclick="toggleDarkMode()">
                    <i id="theme-icon" class="fas fa-moon"></i> <span id="theme-text">ডার্ক মোড</span>
                </div>
                <a href="history.php" class="btn-link btn-history"><i class="fas fa-history"></i> ইতিহাস</a>
                <a href="view_inspections.php" class="btn-link btn-inspection"><i class="fas fa-book"></i> পরিদর্শন তালিকা</a>
                <a href="logout.php" class="btn-link btn-logout"><i class="fas fa-sign-out-alt"></i> লগআউট</a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card"><h3>মোট ফিডব্যাক</h3><p><?php echo $total_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: #2ecc71;"><h3>প্রশংসা 😊</h3><p><?php echo $praise_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: #ff4757;"><h3>অভিযোগ 😟</h3><p><?php echo $complaint_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: var(--gold);"><h3>নতুন পরিদর্শন</h3><p><?php echo $total_inspections; ?></p></div>
        </div>

        <!-- Dynamic QR Box -->
        <div class="qr-card">
            <div style="flex:1; min-width: 300px;">
                <h3>Dynamic QR Settings</h3>
                <form method="POST">
                    <input type="text" name="new_url" value="<?php echo htmlspecialchars($current_url); ?>" required style="width: 80%; padding: 10px; border-radius: 10px; border: 1px solid #ddd; margin-bottom: 10px;">
                    <button type="submit" name="update_qr" class="btn-link btn-history" style="border:none; cursor:pointer;">Update Link</button>
                </form>
                <a href="qr_poster.php" target="_blank" style="display:inline-block; margin-top:10px; color: var(--gold); text-decoration:none; font-weight:bold;">
                    <i class="fas fa-print"></i> অফিসিয়াল পোস্টার প্রিন্ট করুন
                </a>
            </div>
            <div style="text-align: center;">
                <div id="qrcode"></div>
                <button onclick="downloadQR()" class="q-btn" style="margin-top:10px; background:var(--primary); color:white; border:none; padding:8px 15px; border-radius:8px;">📥 QR ডাউনলোড</button>
            </div>
        </div>

        <!-- SECTION: Patients Feedback -->
        <div class="section-header">
            <h2>রোগীদের ফিডব্যাক (Praise & Complaints)</h2>
            <div>
                <a href="admin.php?filter=all" class="q-btn <?php echo ($filter=='all')?'active-tab':''; ?>">সবগুলো</a>
                <a href="admin.php?filter=Praise" class="q-btn <?php echo ($filter=='Praise')?'active-tab':''; ?>">প্রশংসা</a>
                <a href="admin.php?filter=Complaint" class="q-btn <?php echo ($filter=='Complaint')?'active-tab':''; ?>">অভিযোগ</a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID & টাইপ</th>
                        <th>বিভাগ ও কর্মচারী</th>
                        <th>বার্তা & প্রমাণ</th>
                        <th>রোগী</th>
                        <th width="300">ব্যবস্থা নিন (Quick Update)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($feedback_result)): ?>
                    <tr class="<?php echo $row['type']; ?>">
                        <td align="center"><strong><?php echo $row['tracking_id']; ?></strong><br><?php echo ($row['type']=='Praise'?'😊':'😟'); ?></td>
                        <td><small><?php echo $row['department']; ?></small><br><strong><?php echo $row['employee_name']; ?></strong></td>
                        <td>
                            <?php echo nl2br($row['message']); ?>
                            <?php if (!empty($row['evidence_file'])): ?>
                                <br><a href="uploads/<?php echo $row['evidence_file']; ?>" target="_blank" style="color:var(--primary); font-weight:bold; font-size:12px;">📂 প্রমাণ দেখুন</a>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo $row['patient_name']; ?></strong><br><small><?php echo $row['patient_phone']; ?></small></td>
                        <td>
                            <form method="POST" action="admin.php">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <span class="status-badge <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                                <select name="status" id="status_<?php echo $row['id']; ?>" style="width:100%; padding:5px; border-radius:5px;">
                                    <option value="Pending" <?php echo ($row['status']=='Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="investigation" <?php echo ($row['status']=='investigation') ? 'selected' : ''; ?>>Investigation</option>
                                    <option value="Resolved" <?php echo ($row['status']=='Resolved') ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                                <div class="quick-replies">
                                    <?php if($row['type'] == 'Praise'): ?>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Resolved', 'আপনার মূল্যবান প্রশংসার জন্য ধন্যবাদ। এটি আমাদের কর্মচারীদের আরও উৎসাহিত করবে।')">প্রশংসা গ্রহণ</span>
                                    <?php else: ?>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Pending', 'আপনার অভিযোগটি গ্রহণ করা হয়েছে। আমরা এটি খতিয়ে দেখছি।')">পেন্ডিং</span>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'investigation', 'আপনার অভিযোগটি বর্তমানে তদন্তাধীন রয়েছে। অতি শীঘ্রই ব্যবস্থা নেওয়া হবে।')">তদন্ত</span>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Resolved', 'আপনার অভিযোগের সত্যতা পাওয়া গিয়েছে এবং অভিযুক্তের বিরুদ্ধে বিভাগীয় ব্যবস্থা নেওয়া হয়েছে। ধন্যবাদ।')">সমাধান</span>
                                    <?php endif; ?>
                                </div>
                                <textarea name="owner_reply" id="reply_<?php echo $row['id']; ?>" rows="2"><?php echo $row['owner_reply']; ?></textarea>
                                <button type="submit" name="update_action" class="btn-link btn-history" style="width:100%; border:none; cursor:pointer; font-size:12px; padding:8px; margin-top:5px; background: #2ecc71;">Update Status</button>
                            </form>
                            <a href="admin.php?archive_id=<?php echo $row['id']; ?>" style="display:block; text-align:center; font-size:11px; margin-top:10px; color:#888; text-decoration:none;">আর্কাইভে পাঠান</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- SECTION: Inspection Book Show -->
        <div class="section-header">
            <h2>সাম্প্রতিক পরিদর্শন মন্তব্য (Inspection Book)</h2>
            <a href="view_inspections.php" class="btn-link btn-inspection">সবগুলো দেখুন <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>তারিখ</th>
                        <th>পরিদর্শনকারী</th>
                        <th>পদবী ও প্রতিষ্ঠান</th>
                        <th>উদ্দেশ্য ও মন্তব্য</th>
                        <th>স্বাক্ষর</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($inspections_result) > 0): ?>
                        <?php while($ins = mysqli_fetch_assoc($inspections_result)): ?>
                        <tr>
                            <td><small><?php echo date('d M, Y', strtotime($ins['submitted_at'])); ?></small></td>
                            <td><strong><?php echo $ins['visitor_name']; ?></strong><br><small><?php echo $ins['phone']; ?></small></td>
                            <td><?php echo $ins['designation']; ?><br><small><?php echo $ins['organization']; ?></small></td>
                            <td>
                                <i>"<?php echo mb_strimwidth($ins['comments'], 0, 100, "..."); ?>"</i>
                            </td>
                            <td align="center">
                                <?php if(!empty($ins['visitor_signature'])): ?>
                                    <img src="uploads/signatures/<?php echo $ins['visitor_signature']; ?>" style="height:40px; background:#f9f9f9; padding:2px; border-radius:5px;">
                                <?php else: ?>
                                    <small style="color:#ccc;">সই নেই</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" align="center">কোনো পরিদর্শন মন্তব্য পাওয়া যায়নি।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Owner Profile Card -->
        <div class="owner-card" onclick="openOwnerModal()">
            <i class="fas fa-user-md" style="font-size:40px; color:var(--primary);"></i>
            <h4>ডাঃ মো: নাজমুস সাকিব</h4>
            <span style="color:var(--primary); font-size:12px; font-weight:bold;">HOSPITAL OWNER</span>
        </div>
    </div>

    <!-- Modal Owner -->
    <div id="ownerModal" class="modal">
        <div class="modal-content">
            <div style="background: linear-gradient(135deg, #0D1B3E, var(--primary)); padding: 40px; text-align: center; color: white;">
                <span style="position:absolute; right:20px; top:15px; cursor:pointer; font-size:24px;" onclick="closeOwnerModal()">&times;</span>
                <i class="fas fa-user-md" style="font-size: 50px;"></i>
                <h2>ডাঃ মো: নাজমুস সাকিব</h2>
                <p>স্বত্বাধিকারী ও চিকিৎসক</p>
            </div>
            <div style="padding: 20px; text-align: center;">
                <p><i class="fas fa-hospital"></i> পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</p>
                <p><i class="fas fa-map-marker-alt"></i> দীনু মঞ্জিল, কলেজ রোড, বরগুনা</p>
            </div>
        </div>
    </div>

    <script>
        // ডার্ক মোড লজিক
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
                updateThemeUI(true);
            }
        });

        function toggleDarkMode() {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeUI(isDark);
        }

        function updateThemeUI(isDark) {
            const icon = document.getElementById('theme-icon');
            const text = document.getElementById('theme-text');
            if (isDark) {
                if(icon) icon.classList.replace('fa-moon', 'fa-sun');
                if(text) text.innerText = "লাইট মোড";
                if(icon) icon.style.color = "#FFB75E";
            } else {
                if(icon) icon.classList.replace('fa-sun', 'fa-moon');
                if(text) text.innerText = "ডার্ক মোড";
                if(icon) icon.style.color = "#ED8F03";
            }
        }

        // ডিজিটাল ঘড়ি
        function updateClock() {
            const now = new Date();
            let h = now.getHours();
            let m = now.getMinutes();
            let s = now.getSeconds();
            let ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            document.getElementById('digital-clock').innerText = 
                `${h < 10 ? '0'+h : h}:${m < 10 ? '0'+m : m}:${s < 10 ? '0'+s : s} ${ampm}`;
        }
        setInterval(updateClock, 1000); updateClock();

        function setReply(id, status, message) { 
            document.getElementById('reply_' + id).value = message; 
            document.getElementById('status_' + id).value = status; 
        }

        function openOwnerModal() { document.getElementById("ownerModal").style.display = "block"; }
        function closeOwnerModal() { document.getElementById("ownerModal").style.display = "none"; }

        var siteUrl = window.location.origin + window.location.pathname.replace('admin.php', 'qr.php'); 
        var qrcode = new QRCode(document.getElementById("qrcode"), { text: siteUrl, width: 120, height: 120 });
        function downloadQR() { 
            var canvas = document.querySelector('#qrcode canvas'); 
            if(canvas) {
                var link = document.createElement('a'); 
                link.href = canvas.toDataURL("image/png"); 
                link.download = 'hospital_qr.png'; 
                link.click(); 
            }
        }
    </script>
</body>
</html>