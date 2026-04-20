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
    <title>Chairman Dashboard - Patient Care Hospital</title>
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --primary: #00A8B5;
            --navy: #0D1B3E;
            --gold: #ED8F03;
            --light-bg: #f4f7fa;
            --card-bg: #ffffff;
            --text-color: #010204;
            --neon-blue: #00f2fe;
        }

        body.dark-mode {
            --light-bg: #020617;
            --card-bg: #1e293b;
            --text-color: #f1f5f9;
        }

        body { background-color: var(--light-bg); color: var(--text-color); font-family: 'Hind Siliguri', sans-serif; margin: 0; transition: 0.3s; }

        .hospital-header { background: linear-gradient(135deg, var(--navy), var(--primary)); color: white; padding: 25px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .hospital-header img { width: 70px; margin-bottom: 10px; border-radius: 50%; background: #fff; padding: 5px; }

        .admin-container { width: 95%; max-width: 1400px; margin: 20px auto; }
        
        .nav-bar { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; background: var(--card-bg); padding: 15px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); flex-wrap: wrap; gap: 15px; }
        
        #digital-clock { font-family: 'Orbitron', sans-serif; font-size: 18px; font-weight: bold; color: var(--primary); }
        body.dark-mode #digital-clock { color: var(--neon-blue); text-shadow: 0 0 10px var(--neon-blue); }

        .nav-right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .theme-toggle, .lang-box { background: rgba(0,0,0,0.05); padding: 8px 15px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: bold; font-size: 14px; border: 1px solid #ddd; }
        body.dark-mode .theme-toggle, body.dark-mode .lang-box { background: rgba(255,255,255,0.1); border-color: var(--neon-blue); }

        .btn-link { padding: 10px 18px; border-radius: 10px; text-decoration: none; color: white; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 13px; }
        .btn-history { background: #2f3542; }
        .btn-inspection { background: var(--gold); }
        .btn-logout { background: #ff4757; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); padding: 20px; border-radius: 20px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-bottom: 5px solid var(--primary); }
        .stat-card p { font-size: 30px; font-weight: bold; margin: 10px 0 0; }

        .qr-card { background: var(--card-bg); padding: 30px; border-radius: 25px; display: flex; flex-wrap: wrap; gap: 30px; align-items: center; justify-content: space-around; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .qr-info { flex: 1; min-width: 300px; }
        .qr-info input { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.1); background: var(--light-bg); color: var(--text-color); margin-bottom: 10px; outline: none; }

        .table-wrapper { background: var(--card-bg); border-radius: 20px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        th { background: rgba(0,0,0,0.03); padding: 15px; color: #747d8c; text-align: left; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); vertical-align: top; }
        
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; display: inline-block; margin-bottom: 10px; }
        .Pending { background: #fff3cd; color: #856404; }
        .investigation { background: #d1ecf1; color: #0c5460; }
        .Resolved { background: #d4edda; color: #155724; }

        .q-btn { font-size: 10px; padding: 5px 8px; cursor: pointer; border-radius: 5px; border: 1px solid #ddd; background: var(--card-bg); margin: 2px; display: inline-block; color: inherit; }
        textarea { width: 100%; border-radius: 10px; padding: 8px; background: var(--light-bg); color: var(--text-color); border: 1px solid #ddd; margin-top: 5px; }

        .chairman-section { margin-top: 50px; display: flex; justify-content: center; padding: 30px 0; border-top: 2px solid #e0e0e0; }
        .chairman-card { background: var(--card-bg); padding: 25px 40px; border-radius: 30px; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.1); cursor: pointer; transition: 0.3s; border: 1px solid #eee; width: 100%; max-width: 400px; }
        .chairman-card:hover { transform: translateY(-10px); border-color: var(--primary); }
        .chairman-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); margin-bottom: 15px; }

        .modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); }
        .modal-content { background: var(--card-bg); margin: 5% auto; border-radius: 30px; width: 90%; max-width: 420px; overflow: hidden; animation: zoomIn 0.3s; position: relative; }
        .modal-header { background: linear-gradient(135deg, var(--navy), var(--primary)); padding: 40px 20px; text-align: center; color: white; }
        @keyframes zoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr 1fr; } .nav-bar { flex-direction: column; text-align: center; } .qr-card { text-align: center; } }
    </style>
</head>
<body>

    <header class="hospital-header">
        <img src="images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
        <h1 id="txt-h-title">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
        <p id="txt-h-sub">চেয়ারম্যান অ্যাডমিন কন্ট্রোল ড্যাশবোর্ড</p>
    </header>

    <div class="admin-container">
        
        <div class="nav-bar">
            <div id="digital-clock">00:00:00 AM</div>

            <div class="nav-right">
                <div class="lang-box">
                    <i class="fas fa-globe"></i>
                    <select id="langSelector" onchange="changeAdminLang()" style="background:transparent; border:none; outline:none; font-weight:bold; cursor:pointer;">
                        <option value="bn">বাংলা</option>
                        <option value="en">English</option>
                    </select>
                </div>
                <div class="theme-toggle" onclick="toggleDarkMode()">
                    <i id="theme-icon" class="fas fa-moon"></i> <span id="theme-text">ডার্ক মোড</span>
                </div>
                <a href="history.php" class="btn-link btn-history"><i class="fas fa-history"></i> <span id="txt-nav-history">ইতিহাস</span></a>
                <a href="view_inspections.php" class="btn-link btn-inspection"><i class="fas fa-book"></i> <span id="txt-nav-inspect">পরিদর্শন</span></a>
                <a href="logout.php" class="btn-link btn-logout"><i class="fas fa-power-off"></i></a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card"><h3 id="txt-stat-active">একটিভ ফিডব্যাক</h3><p><?php echo $total_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: #2ecc71;"><h3 id="txt-stat-praise">প্রশংসা 😊</h3><p><?php echo $praise_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: #ff4757;"><h3 id="txt-stat-complaint">অভিযোগ 😟</h3><p><?php echo $complaint_active; ?></p></div>
            <div class="stat-card" style="border-bottom-color: var(--gold);"><h3 id="txt-stat-visit">পরিদর্শন</h3><p><?php echo $total_inspections; ?></p></div>
        </div>

        <!-- QR Section with Poster Button -->
        <div class="qr-card">
            <div class="qr-info">
                <h3 style="margin-bottom: 15px; color: var(--navy); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-qrcode"></i> <span id="txt-qr-title">Dynamic QR Settings</span>
                </h3>
                <form method="POST" action="admin.php">
                    <input type="text" name="new_url" value="<?php echo htmlspecialchars($current_url); ?>" required>
                    <button type="submit" name="update_qr" class="btn-link btn-history" style="border:none; cursor:pointer; width: 100%; justify-content: center;">Update Link</button>
                </form>
                
                <!-- অফিসিয়াল পোস্টার প্রিন্ট বাটন -->
                <a href="qr_poster.php" target="_blank" style="display:flex; align-items:center; justify-content:center; gap:10px; margin-top:15px; padding:15px; background:linear-gradient(45deg, var(--gold), #ff9f43); color:white; text-decoration:none; border-radius:15px; font-weight:800; box-shadow: 0 5px 15px rgba(237, 143, 3, 0.3); transition: 0.3s;">
                    <i class="fas fa-print" style="font-size: 18px;"></i> <span id="txt-qr-print">অফিসিয়াল পোস্টার প্রিন্ট করুন</span>
                </a>
            </div>
            <div style="text-align: center; background: rgba(0,0,0,0.02); padding: 20px; border-radius: 20px;">
                <div id="qrcode"></div>
                <button onclick="downloadQR()" class="q-btn" style="margin-top:15px; background:var(--primary); color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:bold; width: 100%;">📥 কিউআর ডাউনলোড</button>
            </div>
        </div>

        <!-- Table -->
        <h2 id="txt-sec-feedback" style="border-left:6px solid var(--primary); padding-left:15px; margin-bottom:15px;">রোগীদের ফিডব্যাক তালিকা</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID & ধরন</th>
                        <th>বিভাগ ও কর্মচারী</th>
                        <th>বার্তা & প্রমাণ</th>
                        <th>রোগী</th>
                        <th width="300">ব্যবস্থা নিন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($feedback_result)): ?>
                    <tr class="<?php echo $row['type']; ?>">
                        <td align="center"><strong><?php echo $row['tracking_id']; ?></strong><br><span><?php echo ($row['type']=='Praise'?'😊':'😟'); ?></span></td>
                        <td><small><?php echo $row['department']; ?></small><br><strong><?php echo $row['employee_name']; ?></strong></td>
                        <td>
                            <?php echo nl2br($row['message']); ?>
                            <?php if (!empty($row['evidence_file'])): ?>
                                <br><a href="uploads/<?php echo $row['evidence_file']; ?>" target="_blank" style="color:var(--primary); font-weight:bold; font-size:11px; text-decoration:none;"><i class="fas fa-paperclip"></i> View Proof</a>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo $row['patient_name']; ?></strong><br><small><?php echo $row['patient_phone']; ?></small></td>
                        <td>
                            <form method="POST" action="admin.php">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <span class="status-badge <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                                <select name="status">
                                    <option value="Pending" <?php echo ($row['status']=='Pending'?'selected':''); ?>>Pending</option>
                                    <option value="investigation" <?php echo ($row['status']=='investigation'?'selected':''); ?>>Investigation</option>
                                    <option value="Resolved" <?php echo ($row['status']=='Resolved'?'selected':''); ?>>Resolved</option>
                                </select>
                                <div class="quick-replies">
                                    <?php if($row['type'] == 'Praise'): ?>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Resolved', 'আপনার মূল্যবান প্রশংসার জন্য ধন্যবাদ। এটি আমাদের কর্মচারীদের আরও উৎসাহিত করবে।')">ধন্যবাদ</span>
                                    <?php else: ?>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'investigation', 'আপনার অভিযোগটি বর্তমানে তদন্তাধীন রয়েছে। অতি শীঘ্রই ব্যবস্থা নেওয়া হবে।')">তদন্ত</span>
                                        <span class="q-btn" onclick="setReply(<?php echo $row['id']; ?>, 'Resolved', 'আপনার অভিযোগের সত্যতা পাওয়া গিয়েছে এবং অভিযুক্তের বিরুদ্ধে যথাযথ ব্যবস্থা নেওয়া হয়েছে। ধন্যবাদ।')">সমাধান</span>
                                    <?php endif; ?>
                                </div>
                                <textarea name="owner_reply" id="reply_<?php echo $row['id']; ?>" rows="2"><?php echo $row['owner_reply']; ?></textarea>
                                <button type="submit" name="update_action" style="width:100%; padding:10px; background:#2ecc71; color:white; border:none; border-radius:10px; font-weight:bold; cursor:pointer; margin-top:5px;">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Chairman Card -->
        <div class="chairman-section">
            <div class="chairman-card" onclick="openOwnerModal()">
                <img src="images/owner.jpg" alt="Chairman" class="chairman-img" onerror="this.src='https://cdn-icons-png.flaticon.com/512/387/387561.png'">
                <span id="txt-owner-tag" style="display:block; color:var(--primary); font-weight:bold; font-size:12px; text-transform:uppercase;">Chairman</span>
                <h4 id="txt-owner-name">ডাঃ মো: নাজমুস সাকিব</h4>
                <p id="txt-owner-hosp" style="font-size: 11px; color: #94a3b8;">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</p>
            </div>
        </div>
    </div>

    <!-- Modal Owner -->
    <div id="ownerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn" onclick="closeOwnerModal()" style="position:absolute; right:20px; top:15px; cursor:pointer; font-size:24px; color:white;">&times;</span>
                <img src="images/owner.jpg" alt="Chairman" style="width:100px; height:100px; border-radius:50%; border:3px solid white; object-fit:cover; margin-bottom:10px;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/387/387561.png'">
                <h2 id="mdl-name">ডাঃ মো: নাজমুস সাকিব</h2>
                <p id="mdl-title" style="opacity:0.9; font-weight:bold;">চেয়ারম্যান ও চিকিৎসক</p>
            </div>
            <div class="modal-body">
                <div style="text-align:left; background:rgba(0, 168, 181, 0.05); padding:20px; border-radius:20px; font-size:14px;">
                    <p style="margin-bottom:10px;"><i class="fas fa-hospital" style="color:var(--primary);"></i> <span id="mdl-hosp">পেশেন্ট কেয়ার হাসপাতাল</span></p>
                    <p style="margin:10px 0;"><i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> <span id="mdl-addr">দীনু মঞ্জিল, কলেজ রোড, বরগুনা</span></p>
                    <p><i class="fas fa-phone-alt" style="color:var(--primary);"></i> ০১৯১১১১৪৫৩৪</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Language Logic
        const translations = {
            bn: {
                hTitle: "পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার", hSub: "চেয়ারম্যান অ্যাডমিন কন্ট্রোল ড্যাশবোর্ড",
                navHistory: "ইতিহাস", navInspect: "পরিদর্শন", themeDark: "ডার্ক মোড", themeLight: "লাইট মোড",
                statActive: "একটিভ ফিডব্যাক", statPraise: "প্রশংসা 😊", statComplaint: "অভিযোগ 😟", statVisit: "পরিদর্শন",
                qrTitle: "Dynamic QR Settings", qrPrint: "অফিসিয়াল পোস্টার প্রিন্ট করুন",
                secFeedback: "রোগীদের ফিডব্যাক তালিকা", ownerTag: "CHAIRMAN", ownerName: "ডাঃ মো: নাজমুস সাকিব",
                mdlName: "ডাঃ মো: নাজমুস সাকিব", mdlTitle: "চেয়ারম্যান ও চিকিৎসক", mdlHosp: "পেশেন্ট কেয়ার হাসপাতাল", mdlAddr: "দীনু মঞ্জিল, কলেজ রোড, বরগুনা"
            },
            en: {
                hTitle: "Patient Care Hospital & Diagnostic Centre", hSub: "Chairman Admin Dashboard",
                navHistory: "History", navInspect: "Inspection", themeDark: "Dark Mode", themeLight: "Light Mode",
                statActive: "Active Feedbacks", statPraise: "Total Praise 😊", statComplaint: "Complaints 😟", statVisit: "Inspections",
                qrTitle: "Dynamic QR Settings", qrPrint: "Print Official Poster",
                secFeedback: "Patient Feedback List", ownerTag: "CHAIRMAN", ownerName: "Dr. Md. Nazmus Sakib",
                mdlName: "Dr. Md. Nazmus Sakib", mdlTitle: "Chairman & Physician", mdlHosp: "Patient Care Hospital", mdlAddr: "Dinu Manzil, College Road, Barguna"
            }
        };

        function changeAdminLang() {
            const lang = document.getElementById('langSelector').value;
            const t = translations[lang];
            document.getElementById('txt-h-title').innerText = t.hTitle;
            document.getElementById('txt-h-sub').innerText = t.hSub;
            document.getElementById('txt-nav-history').innerText = t.navHistory;
            document.getElementById('txt-nav-inspect').innerText = t.navInspect;
            document.getElementById('theme-text').innerText = (document.body.classList.contains('dark-mode')) ? t.themeLight : t.themeDark;
            document.getElementById('txt-stat-active').innerText = t.statActive;
            document.getElementById('txt-stat-praise').innerText = t.statPraise;
            document.getElementById('txt-stat-complaint').innerText = t.statComplaint;
            document.getElementById('txt-stat-visit').innerText = t.statVisit;
            document.getElementById('txt-qr-title').innerText = t.qrTitle;
            document.getElementById('txt-qr-print').innerText = t.qrPrint;
            document.getElementById('txt-sec-feedback').innerText = t.secFeedback;
            document.getElementById('txt-owner-tag').innerText = t.ownerTag;
            document.getElementById('txt-owner-name').innerText = t.ownerName;
            document.getElementById('mdl-name').innerText = t.mdlName;
            document.getElementById('mdl-title').innerText = t.mdlTitle;
            document.getElementById('mdl-hosp').innerText = t.mdlHosp;
            document.getElementById('mdl-addr').innerText = t.mdlAddr;
        }

        // Dark Mode Logic
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-mode'); updateThemeUI(true); }
        });
        function toggleDarkMode() {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeUI(isDark);
        }
        function updateThemeUI(isDark) {
            const icon = document.getElementById('theme-icon');
            icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
            const lang = document.getElementById('langSelector').value;
            document.getElementById('theme-text').innerText = isDark ? translations[lang].themeLight : translations[lang].themeDark;
        }

        // Clock
        function updateClock() {
            const now = new Date();
            let h = now.getHours(); let m = now.getMinutes(); let s = now.getSeconds();
            let ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
            document.getElementById('digital-clock').innerText = `${h < 10 ? '0'+h : h}:${m < 10 ? '0'+m : m}:${s < 10 ? '0'+s : s} ${ampm}`;
        }
        setInterval(updateClock, 1000); updateClock();

        function setReply(id, status, message) { 
            document.getElementById('reply_' + id).value = message; 
            document.getElementById('reply_' + id).closest('form').querySelector('select').value = status;
        }

        function openOwnerModal() { document.getElementById("ownerModal").style.display = "block"; }
        function closeOwnerModal() { document.getElementById("ownerModal").style.display = "none"; }
        window.onclick = (e) => { if (e.target == document.getElementById("ownerModal")) closeOwnerModal(); }

        var siteUrl = window.location.origin + window.location.pathname.replace('admin.php', 'qr.php'); 
        var qrcode = new QRCode(document.getElementById("qrcode"), { text: siteUrl, width: 140, height: 140 });

        function downloadQR() { 
            var canvas = document.querySelector('#qrcode canvas'); 
            if(canvas) {
                var link = document.createElement('a'); 
                link.href = canvas.toDataURL("image/png"); 
                link.download = 'pcare_qr.png'; link.click(); 
            }
        }
    </script>
</body>
</html>