<?php
include 'db.php';

// ১. লগইন ভেরিফিকেশন
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// ২. এরর সমাধান: $current_url ডিফাইন করা
$current_url = 'index.php'; 
$qr_fetch = mysqli_query($conn, "SELECT redirect_url FROM qr_settings WHERE id=1");
if ($qr_fetch && mysqli_num_rows($qr_fetch) > 0) {
    $qr_row = mysqli_fetch_assoc($qr_fetch);
    $current_url = $qr_row['redirect_url'];
}

// ৩. আপডেট লজিক
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

// ৫. পরিসংখ্যান
$total_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_feedback WHERE is_archived=0"))['total'];
$praise_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_feedback WHERE type='Praise' AND is_archived=0"))['total'];
$complaint_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_feedback WHERE type='Complaint' AND is_archived=0"))['total'];
$total_inspections = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hospital_inspections WHERE is_archived=0"))['total'];

// ৬. ফিল্টার কুয়েরি
$filter = $_GET['filter'] ?? 'all';
$condition = ($filter == 'all') ? "is_archived=0" : "type='$filter' AND is_archived=0";
$feedback_result = mysqli_query($conn, "SELECT * FROM hospital_feedback WHERE $condition ORDER BY id DESC");
$inspections_result = mysqli_query($conn, "SELECT * FROM hospital_inspections WHERE is_archived=0 ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Patient Care Hospital</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Poppins:wght@300;500;700&family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --primary: #00A8B5;
            --secondary: #0056b3;
            --gold: #ED8F03;
            --navy: #0D1B3E;
            --light-bg: #f4f7fa;
            --card-bg: #ffffff;
            --text-color: #2f3542;
            --border-color: #e2e8f0;
            --neon-blue: #00f2fe;
        }

        body.dark-mode {
            --light-bg: #0f172a;
            --card-bg: #1e293b;
            --text-color: #f1f5f9;
            --border-color: #334155;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: var(--light-bg); color: var(--text-color); font-family: 'Hind Siliguri', 'Poppins', sans-serif; transition: 0.3s; line-height: 1.6; }

        /* --- Header Adjusted for Logo --- */
        .hospital-header {
            background: linear-gradient(135deg, var(--navy), var(--primary));
            color: white; padding: 30px 15px; text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .logo-container {
            width: 85px; height: 85px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px; padding: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 3px solid rgba(255,255,255,0.3);
        }
        .logo-container img { max-width: 90%; height: auto; }
        .hospital-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }

        .admin-container { width: 95%; max-width: 1400px; margin: 0 auto; padding: 20px 0; }

        /* Navigation Bar */
        .nav-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 25px; background: var(--card-bg); padding: 15px 20px;
            border-radius: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            flex-wrap: wrap; gap: 15px;
        }

        #digital-clock { font-family: 'Orbitron', sans-serif; font-size: 1.1rem; color: var(--primary); font-weight: bold; }
        body.dark-mode #digital-clock { color: var(--neon-blue); text-shadow: 0 0 10px var(--neon-blue); }

        .nav-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .theme-toggle { 
            background: rgba(0,0,0,0.05); padding: 10px 15px; border-radius: 12px; 
            cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px;
        }
        body.dark-mode .theme-toggle { background: rgba(255,255,255,0.1); }

        .btn-link { 
            padding: 10px 16px; border-radius: 10px; text-decoration: none; 
            color: white; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; 
            font-size: 13px; transition: 0.3s;
        }
        .btn-history { background: #475569; }
        .btn-inspection { background: var(--gold); }
        .btn-logout { background: #ef4444; }
        .btn-link:hover { transform: translateY(-2px); filter: brightness(1.1); }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-card { 
            background: var(--card-bg); padding: 20px; border-radius: 20px; 
            text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.03); 
            border-bottom: 4px solid var(--primary);
        }
        .stat-card h3 { font-size: 14px; color: #94a3b8; margin-bottom: 10px; }
        .stat-card p { font-size: 28px; font-weight: 700; color: var(--text-color); }

        /* QR Section */
        .qr-card { 
            background: var(--card-bg); padding: 25px; border-radius: 20px; 
            display: flex; flex-wrap: wrap; gap: 20px; align-items: center; 
            justify-content: space-around; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        }
        .qr-info { flex: 1; min-width: 280px; }
        .qr-info input { 
            width: 100%; padding: 12px; border-radius: 12px; 
            border: 1px solid var(--border-color); background: var(--light-bg); 
            color: var(--text-color); margin-bottom: 10px;
        }

        /* Tables & Lists */
        .section-header { 
            display: flex; justify-content: space-between; align-items: center; 
            margin: 30px 0 15px; border-left: 5px solid var(--gold); padding-left: 15px; 
        }
        .table-wrapper { 
            background: var(--card-bg); border-radius: 20px; overflow-x: auto; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px;
        }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: rgba(0,0,0,0.02); padding: 18px 15px; color: #64748b; text-align: left; font-size: 13px; font-weight: 600; }
        td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: top; font-size: 14px; }
        
        /* Badges */
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; display: inline-block; margin-bottom: 8px; }
        .Pending { background: #fef3c7; color: #92400e; }
        .investigation { background: #e0f2fe; color: #075985; }
        .Resolved { background: #dcfce7; color: #166534; }

        /* Quick Replies */
        .quick-replies { margin: 10px 0; display: flex; flex-wrap: wrap; gap: 5px; }
        .q-btn { 
            font-size: 10px; padding: 6px 10px; cursor: pointer; border-radius: 8px; 
            border: 1px solid var(--border-color); background: var(--card-bg); 
            color: var(--text-color); font-weight: 600; transition: 0.2s;
        }
        .q-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }
        
        textarea { 
            width: 100%; border-radius: 12px; padding: 12px; 
            background: var(--light-bg); color: var(--text-color); 
            border: 1px solid var(--border-color); outline: none; font-size: 13px;
        }

        .update-btn-table { 
            width: 100%; padding: 10px; background: #10b981; color: white; 
            border: none; border-radius: 10px; cursor: pointer; font-weight: 600; margin-top: 8px;
        }

        /* Owner Card */
        .owner-card { 
            background: var(--card-bg); padding: 25px 40px; border-radius: 25px; 
            text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            cursor: pointer; margin: 50px auto; width: fit-content; 
            border: 1px solid var(--border-color); transition: 0.3s; 
        }
        .owner-card:hover { transform: translateY(-5px); border-color: var(--primary); }






        /* Modal */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(10px); }
        .modal-content { 
            background: var(--card-bg); margin: 8% auto; border-radius: 30px; 
            width: 90%; max-width: 400px; overflow: hidden; 
            animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); position: relative; 
        }
        @keyframes zoomIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
        .modal-header { background: linear-gradient(135deg, var(--navy), var(--primary)); padding: 40px 20px; text-align: center; color: white; }
        .close-btn { position: absolute; right: 20px; top: 15px; color: white; font-size: 24px; cursor: pointer; opacity: 0.7; }
        .modal-body { padding: 30px; text-align: center; }

        /* Responsive Mobile Tuning */
        @media (max-width: 768px) {
            .nav-bar { justify-content: center; text-align: center; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .qr-card { text-align: center; }
            .qr-info { min-width: 100%; }
            th, td { padding: 12px 10px; font-size: 12px; }
            .hospital-header h1 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

    <header class="hospital-header">
        <div class="logo-container">
            <img src="images/logo.png" alt="Hospital Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
        </div>
        <h1>পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
        <p style="font-size: 12px; margin-top: 5px; opacity: 0.8;">অ্যাডমিন কন্ট্রোল ড্যাশবোর্ড</p>
    </header>

    <div class="admin-container">
        
        <!-- Navigation -->
        <div class="nav-bar">
            <div id="digital-clock">00:00:00 AM</div>
            <div class="nav-actions">
                <div class="theme-toggle" onclick="toggleDarkMode()">
                    <i id="theme-icon" class="fas fa-moon"></i> <span id="theme-text">ডার্ক মোড</span>
                </div>
                <a href="history.php" class="btn-link btn-history"><i class="fas fa-history"></i> ইতিহাস</a>
                <a href="view_inspections.php" class="btn-link btn-inspection"><i class="fas fa-book"></i> পরিদর্শন</a>
                <a href="logout.php" class="btn-link btn-logout"><i class="fas fa-sign-out-alt"></i> লগআউট</a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card"><h3>একটিভ ফিডব্যাক</h3><p><?php echo $total_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: #10b981;"><h3>প্রশংসা 😊</h3><p><?php echo $praise_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: #ef4444;"><h3>অভিযোগ 😟</h3><p><?php echo $complaint_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: var(--gold);"><h3>পরিদর্শন</h3><p><?php echo $total_inspections; ?></p></div>
        </div>

        <!-- QR Control -->
        <div class="qr-card">
            <div class="qr-info">
                <h3 style="margin-bottom: 15px;"><i class="fas fa-qrcode"></i> Dynamic QR Settings</h3>
                <form method="POST">
                    <input type="text" name="new_url" value="<?php echo htmlspecialchars($current_url); ?>" required>
                    <button type="submit" name="update_qr" class="btn-link btn-history" style="border:none; cursor:pointer; width: 100%;">Update Link</button>
                </form>
                <a href="qr_poster.php" target="_blank" style="display:block; margin-top:15px; text-align:center; color: var(--gold); text-decoration:none; font-weight:bold; font-size: 14px;">
                    <i class="fas fa-print"></i> অফিসিয়াল পোস্টার প্রিন্ট করুন
                </a>
            </div>
            <div style="text-align: center;">
                <div id="qrcode"></div>
                <button onclick="downloadQR()" class="q-btn" style="margin-top:10px; background:var(--primary); color:white; border:none; padding:8px 15px; border-radius:8px;">📥 QR ডাউনলোড</button>
            </div>
        </div>

        <!-- Patients Feedback Section -->
        <div class="section-header">
            <h2>রোগীদের ফিডব্যাক (Active)</h2>
            <div style="display:flex; gap:5px;">
                <a href="admin.php?filter=all" class="q-btn <?php echo ($filter=='all')?'active-filter':''; ?>">সবগুলো</a>
                <a href="admin.php?filter=Praise" class="q-btn <?php echo ($filter=='Praise')?'active-filter':''; ?>">প্রশংসা</a>
                <a href="admin.php?filter=Complaint" class="q-btn <?php echo ($filter=='Complaint')?'active-filter':''; ?>">অভিযোগ</a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID & টাইপ</th>
                        <th>বিভাগ ও কর্মচারী</th>
                        <th>বার্তা ও প্রমাণ</th>
                        <th>রোগী</th>
                        <th width="280">ব্যবস্থা নিন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($feedback_result)): ?>
                    <tr class="<?php echo $row['type']; ?>">
                        <td align="center"><strong><?php echo $row['tracking_id']; ?></strong><br><?php echo ($row['type']=='Praise'?'😊':'😟'); ?></td>
                        <td><small><?php echo $row['department']; ?></small><br><strong><?php echo $row['employee_name']; ?></strong></td>
                        <td>
                            <div style="max-height: 80px; overflow-y: auto;"><?php echo nl2br($row['message']); ?></div>
                            <?php if (!empty($row['evidence_file'])): ?>
                                <a href="uploads/<?php echo $row['evidence_file']; ?>" target="_blank" style="color:var(--primary); font-weight:bold; font-size:11px; text-decoration:none; display:inline-block; margin-top:8px;"><i class="fas fa-paperclip"></i> প্রমাণ দেখুন</a>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo $row['patient_name']; ?></strong><br><small><?php echo $row['patient_phone']; ?></small></td>
                        <td>
                            <form method="POST" action="admin.php">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <span class="status-badge <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                                <select name="status" id="status_<?php echo $row['id']; ?>">
                                    <option value="Pending" <?php echo ($row['status']=='Pending'?'selected':''); ?>>Pending</option>
                                    <option value="investigation" <?php echo ($row['status']=='investigation'?'selected':''); ?>>Investigation</option>
                                    <option value="Resolved" <?php echo ($row['status']=='Resolved'?'selected':''); ?>>Resolved</option>
                                </select>
                                <div class="quick-replies">
                                    <?php if($row['type'] == 'Praise'): ?>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Resolved', 'আপনার মূল্যবান প্রশংসার জন্য ধন্যবাদ। এটি আমাদের কর্মচারীদের আরও উৎসাহিত করবে।')">প্রশংসা গ্রহণ</span>
                                    <?php else: ?>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Pending', 'আপনার অভিযোগটি গ্রহণ করা হয়েছে। আমরা এটি খতিয়ে দেখছি।')">পেন্ডিং</span>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'investigation', 'আপনার অভিযোগটি বর্তমানে তদন্তাধীন রয়েছে। অতি শীঘ্রই ব্যবস্থা নেওয়া হবে।')">তদন্ত</span>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Resolved', 'আপনার অভিযোগের সত্যতা পাওয়া গিয়েছে এবং যথাযথ ব্যবস্থা নেওয়া হয়েছে। ধন্যবাদ।')">সমাধান</span>
                                    <?php endif; ?>
                                </div>
                                <textarea name="owner_reply" id="reply_<?php echo $row['id']; ?>" placeholder="জবাব..."><?php echo $row['owner_reply']; ?></textarea>
                                <button type="submit" name="update_action" class="update-btn-table">Update Status</button>
                            </form>
                            <a href="admin.php?archive_id=<?php echo $row['id']; ?>" class="archive-btn" onclick="return confirm('তালিকা থেকে সরিয়ে দিতে চান?')">আর্কাইভে পাঠান</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Inspections -->
        <div class="section-header">
            <h2>সাম্প্রতিক পরিদর্শন (Inspection)</h2>
            <a href="view_inspections.php" class="q-btn">সব দেখুন <i class="fas fa-arrow-right"></i></a>
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
                            <td><strong><?php echo $ins['visitor_name']; ?></strong></td>
                            <td><small><?php echo $ins['designation']; ?><br><?php echo $ins['organization']; ?></small></td>
                            <td><i style="font-size:12px;">"<?php echo mb_strimwidth($ins['comments'], 0, 80, "..."); ?>"</i></td>
                            <td align="center">
                                <?php if(!empty($ins['visitor_signature'])): ?>
                                    <img src="uploads/signatures/<?php echo $ins['visitor_signature']; ?>" style="height:35px; background:white; padding:2px; border:1px solid #eee; border-radius:5px;">
                                <?php else: ?>
                                    <small style="color:#ccc;">সই নেই</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" align="center">কোনো ডাটা নেই।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Owner Card -->
        <div class="owner-card" onclick="openOwnerModal()">
            <i class="fas fa-user-md"></i>
            <span style="display:block; color:var(--primary); font-weight:bold; font-size:12px; margin-top:5px;">HOSPITAL OWNER</span>
            <h4>ডাঃ নাজমুস সাকিব</h4>
        </div>
    </div>

    <!-- Owner Modal Popup -->
    <div id="ownerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn" onclick="closeOwnerModal()">&times;</span>
                <i class="fas fa-user-md" style="font-size: 50px; color:white;"></i>
                <h2 style="margin:10px 0 0;">ডাঃ নাজমুস সাকিব</h2>
                <p style="margin:0; opacity:0.8;">স্বত্বাধিকারী ও চিকিৎসক</p>
            </div>
            <div class="modal-body">
                <p><i class="fas fa-hospital" style="color:var(--primary);"></i> পেশেন্ট কেয়ার হাসপাতাল</p>
                <p><i class="fas fa-map-marker-alt" style="color:var(--primary); margin-top:10px;"></i> কলেজ রোড, বরগুনা</p>
                <p style="margin-top:20px; font-weight:bold; color:#10b981;"><i class="fas fa-heart"></i> সর্বদা আপনাদের সেবায় নিয়োজিত</p>
            </div>
        </div>
    </div>

    <script>
        // Theme Logic
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

        // Clock
        function updateClock() {
            const now = new Date();
            let h = now.getHours(); let m = now.getMinutes(); let s = now.getSeconds();
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
        window.onclick = function(e) { if (e.target == document.getElementById("ownerModal")) closeOwnerModal(); }

        // QR Code
        var siteUrl = window.location.origin + window.location.pathname.replace('admin.php', 'qr.php'); 
        var qrcode = new QRCode(document.getElementById("qrcode"), { text: siteUrl, width: 120, height: 120 });
        function downloadQR() { 
            var canvas = document.querySelector('#qrcode canvas'); 
            if(canvas) {
                var link = document.createElement('a'); 
                link.href = canvas.toDataURL("image/png"); 
                link.download = 'pcare_qr.png'; 
                link.click(); 
            }
        }
    </script>
</body>
</html>