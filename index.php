<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Care Hospital - Feedback System</title>
    <!-- Font Awesome & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Orbitron:wght@500;700;900&display=swap');

        :root {
            --logo-navy: #0D1B3E;      
            --logo-teal: #00A8B5;      
            --bg-gradient: linear-gradient(135deg, #0D1B3E 0%, #00A8B5 100%);
            --white: #ffffff;
            --gold: #ED8F03;
            --gold-grad: linear-gradient(135deg, #FFB75E 0%, #ED8F03 100%);
            --neon-blue: #00f2fe;
            --text-main: #1e293b;
            --card-bg: #ffffff;
        }

        /* ডার্ক মোড ভ্যারিয়েবল ওভাররাইড */
        body.dark-mode {
            --bg-gradient: linear-gradient(135deg, #020617 0%, #0f172a 100%);
            --white: #1e293b;
            --text-main: #f1f5f9;
            --card-bg: #0f172a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Hind Siliguri', sans-serif; transition: background 0.4s, color 0.4s; }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            color: var(--text-main);
        }

        /* --- Top Bar: Clock, Theme & Language --- */
        .top-bar {
            width: 100%;
            max-width: 500px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 15px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        #digital-clock {
            font-family: 'Orbitron', sans-serif;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
        }

        /* ডার্ক মোড অন হলে ঘড়ির নিওন গ্লো */
        body.dark-mode #digital-clock {
            color: var(--neon-blue) !important;
            text-shadow: 0 0 10px var(--neon-blue), 0 0 20px var(--neon-blue);
        }

        .top-actions { display: flex; align-items: center; gap: 15px; }

        .theme-toggle {
            cursor: pointer;
            font-size: 18px;
            color: var(--gold);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .lang-container {
            display: flex;
            align-items: center;
            gap: 5px;
            color: white;
        }
        .lang-container select {
            background: transparent;
            color: white;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 3px 8px;
            border-radius: 10px;
            cursor: pointer;
            outline: none;
            font-size: 13px;
        }
        .lang-container select option { color: #333; }

        /* --- Main Container --- */
        .landing-container {
            background: var(--card-bg);
            padding: 40px 25px;
            border-radius: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            max-width: 420px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .logo-img { max-width: 180px; height: auto; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto; }

        h1 { font-size: 22px; color: var(--logo-teal); margin-bottom: 5px; font-weight: 800; line-height: 1.3; }
        body.dark-mode h1 { color: var(--neon-blue); }
        
        .address { font-size: 14px; color: #64748b; margin-bottom: 30px; font-weight: 600; }

        /* Choice Buttons */
        .choice-box { display: flex; flex-direction: column; gap: 12px; }
        .btn {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 22px; border-radius: 20px;
            text-decoration: none; font-size: 17px; font-weight: 700; color: white;
            transition: 0.3s;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

        .btn-praise { background: linear-gradient(135deg, #28a745 0%, var(--logo-teal) 100%); }
        .btn-complaint { background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%); }
        .btn-track { background: linear-gradient(135deg, var(--logo-navy) 0%, var(--logo-teal) 100%); }
        .btn-inspection { background: var(--gold-grad); border: 1px solid #d48600; }

        /* Owner Card */
        .owner-trigger {
            margin-top: 35px;
            background: rgba(148, 163, 184, 0.1);
            padding: 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            border: 1px solid #eee;
            transition: 0.3s;
        }
        body.dark-mode .owner-trigger { border-color: #334155; }
        .owner-thumb { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--logo-teal); }
        .owner-info-text b { color: var(--text-main); font-size: 15px; display: block; }
        .owner-info-text span { color: var(--logo-teal); font-size: 12px; font-weight: bold; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); }
        .modal-content {
            background: var(--card-bg); margin: 10vh auto; border-radius: 40px; width: 90%; max-width: 400px;
            overflow: hidden; position: relative; animation: slideIn 0.4s;
        }
        @keyframes slideIn { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { background: var(--logo-navy); padding: 50px 20px; text-align: center; color: white; position: relative; }
        .modal-close { position: absolute; right: 20px; top: 15px; color: white; font-size: 24px; cursor: pointer; }
        
        .owner-img-container { width: 150px; height: 150px; border-radius: 50%; border: 6px solid var(--card-bg); margin: 0 auto; margin-top: -75px; position: relative; z-index: 10; overflow: hidden; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .owner-img-full { width: 100%; height: 100%; object-fit: cover; object-position: top; }

        .modal-body { padding: 20px 25px 30px; text-align: center; color: var(--text-main); }
        .detail-row { display: flex; align-items: center; gap: 12px; background: rgba(148, 163, 184, 0.1); padding: 12px; border-radius: 15px; margin-bottom: 10px; text-align: left; font-size: 14px; font-weight: 600; }
        .detail-row i { color: var(--logo-teal); width: 20px; text-align: center; }

        @media (max-width: 480px) { h1 { font-size: 19px; } }
    </style>
</head>
<body>

    <!-- Top Info Bar -->
    <div class="top-bar">
        <div id="digital-clock">00:00:00 AM</div>
        
        <div class="top-actions">
            <!-- ডার্ক মোড বাটন -->
            <div class="theme-toggle" onclick="toggleDarkMode()">
                <i id="theme-icon" class="fas fa-moon"></i> 
                <span id="theme-text" style="font-size: 12px; font-weight: bold;">ডার্ক মোড</span>
            </div>

            <!-- ল্যাঙ্গুয়েজ সিলেক্টর -->
            <div class="lang-container">
                <i class="fas fa-globe"></i>
                <select id="langSelector" onchange="changeLanguage()">
                    <option value="bn">বাংলা</option>
                    <option value="en">English</option>
                </select>
            </div>
        </div>
    </div>

    <div class="landing-container">
        <!-- Hospital Logo -->
        <img src="images/logo.png" alt="Logo" class="logo-img">

        <h1 id="hospital-name">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
        <p class="address" id="hospital-addr">কলেজ রোড, বরগুনা</p>
        
        <div class="choice-box">
            <a href="form.php?type=Praise" class="btn btn-praise">
                <span><i class="fas fa-smile"></i> &nbsp; <span id="txt-praise">ভালো কাজের প্রশংসা</span></span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="form.php?type=Complaint" class="btn btn-complaint">
                <span><i class="fas fa-frown"></i> &nbsp; <span id="txt-complaint">খারাপ কাজের অভিযোগ</span></span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="track.php" class="btn btn-track">
                <span><i class="fas fa-search"></i> &nbsp; <span id="txt-track">অভিযোগের/প্রশংসার আপডেট দেখুন</span></span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="inspection_form.php" class="btn btn-inspection">
                <span><i class="fas fa-clipboard-check"></i> &nbsp; <span id="txt-inspection">পরিদর্শন মন্তব্য বই</span></span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="owner-trigger" onclick="toggleModal(true)">
            <img src="images/owner.jpg" alt="Dr. Nazmus Sakib" class="owner-thumb" onerror="this.src='https://cdn-icons-png.flaticon.com/512/387/387561.png'">
            <div class="owner-info-text">
                <b id="txt-owner-name">ডাঃ মো: নাজমুস সাকিব</b>
                <span id="txt-owner-title">হাসপাতাল মালিক (বিস্তারিত)</span>
            </div>
            <i class="fas fa-info-circle" style="margin-left: auto; color: #ccc;"></i>
        </div>
    </div>

    <!-- Owner Modal -->
    <div id="ownerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-close" onclick="toggleModal(false)">&times;</span>
            </div>
            <div class="owner-img-container">
                <img src="images/owner.jpg" alt="Dr. Nazmus Sakib" class="owner-img-full" onerror="this.src='https://cdn-icons-png.flaticon.com/512/387/387561.png'">
            </div>
            <div class="modal-body">
                <h2 id="modal-name">ডাঃ মো: নাজমুস সাকিব</h2>
                <p style="color: var(--logo-teal); font-weight: bold; margin-bottom: 20px;" id="modal-title">স্বত্বাধিকারী ও চিকিৎসক</p>
                <div class="detail-row"><i class="fas fa-hospital"></i> <span id="modal-hosp">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</span></div>
                <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span id="modal-loc">দীনু মঞ্জিল, কলেজ রোড, বরগুনা</span></div>
                <div class="detail-row"><i class="fas fa-heart"></i> <span id="modal-msg">রোগী সেবায় আমরা সর্বদা প্রতিশ্রুতিবদ্ধ</span></div>
            </div>
        </div>
    </div>

    <script>
        // Digital Clock
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            const lang = document.getElementById('langSelector').value;
            if(lang === 'bn') {
                const bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
                const toBn = (n) => n.toString().padStart(2, '0').split('').map(d => isNaN(d) ? d : bnDigits[d]).join('');
                document.getElementById('digital-clock').innerText = `${toBn(hours)}:${toBn(minutes)}:${toBn(seconds)} ${ampm === 'AM' ? 'পূর্বাহ্ণ' : 'অপরাহ্ণ'}`;
            } else {
                document.getElementById('digital-clock').innerText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')} ${ampm}`;
            }
        }
        setInterval(updateClock, 1000); updateClock();

        // Dark Mode Logic
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
                icon.classList.replace('fa-moon', 'fa-sun');
                text.innerText = "লাইট মোড";
                icon.style.color = "#FFB75E";
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                text.innerText = "ডার্ক মোড";
                icon.style.color = "#ED8F03";
            }
        }

        // Language Logic
        function toggleModal(show) { document.getElementById("ownerModal").style.display = show ? "block" : "none"; }

        const translations = {
            en: { hName: "Patient Care Hospital & Diagnostic Centre", hAddr: "College Road, Barguna", praise: "Praise for Good Work", complaint: "Complaint for Bad Behavior", track: "Track Status", inspection: "Inspection Book", oName: "Dr. Nazmus Sakib", oTitle: "Hospital Owner (Details)", mName: "Dr. Nazmus Sakib", mTitle: "Proprietor & Physician", mLoc: "College Road, Barguna", mMsg: "Committed to patient care" },
            bn: { hName: "পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার", hAddr: "কলেজ রোড, বরগুনা", praise: "ভালো কাজের প্রশংসা", complaint: "খারাপ কাজের অভিযোগ", track: "অভিযোগের/প্রশংসার আপডেট দেখুন", inspection: "পরিদর্শন মন্তব্য বই", oName: "ডাঃ নাজমুস সাকিব", oTitle: "হাসপাতাল মালিক (বিস্তারিত)", mName: "ডাঃ নাজমুস সাকিব", mTitle: "স্বত্বাধিকারী ও চিকিৎসক", mLoc: "দীনু মঞ্জিল, কলেজ রোড, বরগুনা", mMsg: "রোগী সেবায় আমরা সর্বদা প্রতিশ্রুতিবদ্ধ" }
        };

        function changeLanguage() {
            const lang = document.getElementById('langSelector').value;
            const t = translations[lang];
            document.getElementById('hospital-name').innerText = t.hName;
            document.getElementById('hospital-addr').innerText = t.hAddr;
            document.getElementById('txt-praise').innerText = t.praise;
            document.getElementById('txt-complaint').innerText = t.complaint;
            document.getElementById('txt-track').innerText = t.track;
            document.getElementById('txt-inspection').innerText = t.inspection;
            document.getElementById('txt-owner-name').innerText = t.oName;
            document.getElementById('txt-owner-title').innerText = t.oTitle;
            document.getElementById('modal-name').innerText = t.mName;
            document.getElementById('modal-title').innerText = t.mTitle;
            document.getElementById('modal-loc').innerText = t.mLoc;
            document.getElementById('modal-msg').innerText = t.mMsg;
            updateClock();
        }

        window.onclick = function(e) { if (e.target == document.getElementById("ownerModal")) toggleModal(false); }
    </script>
</body>
</html>