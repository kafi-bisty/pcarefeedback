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
            --neon-blue: #00f2fe;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --logo-circle-border: rgba(0, 168, 181, 0.2);
        }

        /* ডার্ক মোড ভ্যারিয়েবল */
        body.dark-mode {
            --bg-gradient: linear-gradient(135deg, #020617 0%, #0f172a 100%);
            --white: #1e293b;
            --text-main: #f1f5f9;
            --card-bg: #0f172a;
            --logo-circle-border: rgba(0, 242, 254, 0.4);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Hind Siliguri', sans-serif; transition: all 0.3s ease; }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            color: var(--text-main);
        }

        /* --- Top Bar --- */
        .top-bar {
            width: 100%;
            max-width: 450px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 15px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        #digital-clock {
            font-family: 'Orbitron', sans-serif;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }

        body.dark-mode #digital-clock {
            color: var(--neon-blue);
            text-shadow: 0 0 10px var(--neon-blue);
        }

        .top-actions { display: flex; align-items: center; gap: 10px; }

        .theme-toggle, .lang-container {
            cursor: pointer;
            background: rgba(255,255,255,0.1);
            padding: 5px 12px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .lang-container select {
            background: transparent;
            color: white;
            border: none;
            outline: none;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        .lang-container select option { color: #333; }

        /* --- Main Card --- */
        .landing-container {
            background: var(--card-bg);
            padding: 30px 20px;
            border-radius: 35px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            max-width: 400px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .logo-wrapper {
            width: 130px;
            height: 130px;
            margin: 0 auto 20px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 5px solid var(--logo-circle-border);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .logo-img { width: 100%; height: 100%; object-fit: cover; }

        h1 { font-size: 18px; color: var(--logo-teal); margin-bottom: 5px; font-weight: 800; }
        body.dark-mode h1 { color: var(--neon-blue); }
        .address { font-size: 12px; color: #64748b; margin-bottom: 25px; font-weight: 600; }

        /* Buttons */
        .choice-box { display: flex; flex-direction: column; gap: 10px; }
        .btn {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; border-radius: 18px;
            text-decoration: none; font-size: 15px; font-weight: 700; color: white;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .btn:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .btn-praise { background: linear-gradient(135deg, #28a745 0%, #00A8B5 100%); }
        .btn-complaint { background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%); }
        .btn-track { background: linear-gradient(135deg, #0D1B3E 0%, #0056b3 100%); }
        .btn-inspection { background: linear-gradient(135deg, #FFB75E 0%, #ED8F03 100%); }
        .btn-call { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }

        /* Owner Card */
        .owner-trigger {
            margin-top: 25px;
            background: rgba(148, 163, 184, 0.1);
            padding: 10px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .owner-thumb { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--logo-teal); }
        .owner-info-text b { color: var(--text-main); font-size: 14px; display: block; text-align: left; }
        .owner-info-text span { color: var(--logo-teal); font-size: 11px; font-weight: bold; display: block; text-align: left; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); }
        .modal-content {
            background: var(--card-bg); margin: 8vh auto; border-radius: 35px; width: 90%; max-width: 380px;
            overflow: hidden; position: relative; animation: slideIn 0.4s ease-out;
        }
        @keyframes slideIn { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-close { position: absolute; right: 15px; top: 15px; color: white; font-size: 22px; cursor: pointer; background: rgba(0,0,0,0.3); width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 20; }
        .owner-img-container { width: 130px; height: 130px; border-radius: 50%; border: 5px solid var(--card-bg); margin: 0 auto; margin-top: -65px; position: relative; z-index: 10; overflow: hidden; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .owner-img-full { width: 100%; height: 100%; object-fit: cover; object-position: top; }
        .modal-body { padding: 15px 20px 25px; text-align: center; color: var(--text-main); }
        .detail-row { display: flex; align-items: center; gap: 10px; background: rgba(148, 163, 184, 0.1); padding: 10px; border-radius: 12px; margin-bottom: 8px; text-align: left; font-size: 13px; font-weight: 600; }
        .detail-row i { color: var(--logo-teal); width: 18px; text-align: center; }

        @media (max-width: 480px) { h1 { font-size: 16px; } .btn { font-size: 14px; } }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div id="digital-clock">00:00:00 AM</div>
        <div class="top-actions">
            <div class="theme-toggle" onclick="toggleDarkMode()">
                <i id="theme-icon" class="fas fa-moon" style="color: var(--gold);"></i> 
            </div>
            <div class="lang-container">
                <i class="fas fa-globe" style="color: white; font-size: 14px;"></i>
                <select id="langSelector" onchange="changeLanguage()">
                    <option value="bn">বাংলা</option>
                    <option value="en">EN</option>
                </select>
            </div>
        </div>
    </div>

    <div class="landing-container">
        <div class="logo-wrapper">
            <img src="images/logo.png" alt="Logo" class="logo-img">
        </div>

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
                <span><i class="fas fa-search"></i> &nbsp; <span id="txt-track">আপডেট দেখুন</span></span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="inspection_form.php" class="btn btn-inspection">
                <span><i class="fas fa-clipboard-check"></i> &nbsp; <span id="txt-inspection">পরিদর্শন মন্তব্য বই</span></span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <!-- সরাসরি কল করার বাটন -->
            <a href="tel:01911114534" class="btn btn-call">
                <span><i class="fas fa-phone-volume"></i> &nbsp; <span id="txt-call">সরাসরি কল করুন</span></span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="owner-trigger" onclick="toggleModal(true)">
            <img src="images/owner.jpg" alt="Dr. Nazmus Sakib" class="owner-thumb" onerror="this.src='https://cdn-icons-png.flaticon.com/512/387/387561.png'">
            <div class="owner-info-text">
                <b id="txt-owner-name"> মো: নাজমুস সাকিব</b>
                <span id="txt-owner-title">চেয়ারম্যান (বিস্তারিত)</span>
            </div>
            <i class="fas fa-info-circle" style="margin-left: auto; color: var(--logo-teal);"></i>
        </div>
    </div>

    <!-- Owner Modal -->
    <div id="ownerModal" class="modal">
        <div class="modal-content">
            <div style="background: var(--logo-navy); padding: 40px 20px; text-align: center;">
                <span class="modal-close" onclick="toggleModal(false)">&times;</span>
            </div>
            <div class="owner-img-container">
                <img src="images/owner.jpg" alt="Dr. Nazmus Sakib" class="owner-img-full" onerror="this.src='https://cdn-icons-png.flaticon.com/512/387/387561.png'">
            </div>
            <div class="modal-body">
                <h2 id="modal-name"> মো:নাজমুস সাকিব</h2>
                <h3 style="color: var(--logo-teal); margin-bottom: 20px;" id="modal-title">চেয়ারম্যান</h3>
                <p style="color: var(--logo-teal); font-weight: bold; margin-bottom: 15px; font-size: 13px;" id="modal-title"></p>
                <div class="detail-row"><i class="fas fa-hospital"></i> <span id="modal-hosp" style="font-size: 11px;">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</span></div>
                <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span id="modal-loc"> কলেজ রোড, বরগুনা</span></div>
                <!-- মোবাইল নাম্বার সেকশন -->
                <div class="detail-row">
                    <i class="fas fa-phone-alt"></i>
                    <span id="modal-phone"><strong>সরাসরি কথা বলুন:</strong>01911114534</span>
                </div>
                <div class="detail-row"><i class="fas fa-heart"></i> <span id="modal-msg">রোগী সেবায় আমরা সর্বদা প্রতিশ্রুতিবদ্ধ</span></div>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            const lang = document.getElementById('langSelector').value;
            if(lang === 'bn') {
                const bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
                const toBn = (n) => n.toString().padStart(2, '0').split('').map(d => isNaN(d) ? d : bnDigits[d]).join('');
                document.getElementById('digital-clock').innerText = `${toBn(hours)}:${toBn(minutes)} ${ampm === 'AM' ? 'পূর্বাহ্ণ' : 'অপরাহ্ণ'}`;
            } else {
                document.getElementById('digital-clock').innerText = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')} ${ampm}`;
            }
        }
        setInterval(updateClock, 1000); updateClock();

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
            if (isDark) { icon.classList.replace('fa-moon', 'fa-sun'); icon.style.color = "#FFB75E"; }
            else { icon.classList.replace('fa-sun', 'fa-moon'); icon.style.color = "#ED8F03"; }
        }

        function toggleModal(show) { document.getElementById("ownerModal").style.display = show ? "block" : "none"; }

        const translations = {
            en: { hName: "Patient Care Hospital & Diagnostic Centre", hAddr: "College Road, Barguna", praise: "Praise Service", complaint: "Complaint", track: "Track Status", inspection: "Inspection Book", call: "Call Directly", oName: "Dr. Nazmus Sakib", oTitle: "Proprietor", modalPhone: "Direct Call: 01911114534", mLoc: "College Road, Barguna", mMsg: "Committed to excellence" },
            bn: { hName: "পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার", hAddr: "কলেজ রোড, বরগুনা", praise: "ভালো কাজের প্রশংসা", complaint: "খারাপ কাজের অভিযোগ", track: "আপডেট দেখুন", inspection: "পরিদর্শন মন্তব্য বই", call: "সরাসরি কল করুন", oName: "ডাঃ মো: নাজমুস সাকিব", oTitle: "চেয়ারম্যান", modalPhone: "সরাসরি কথা বলুন: ০১৯১১১১৪৫৩৪", mLoc: " কলেজ রোড, বরগুনা", mMsg: "রোগী সেবায় আমরা সর্বদা প্রতিশ্রুতিবদ্ধ" }
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
            document.getElementById('txt-call').innerText = t.call;
            document.getElementById('txt-owner-name').innerText = t.oName;
            document.getElementById('modal-name').innerText = t.oName;
            document.getElementById('modal-title').innerText = t.oTitle;
            document.getElementById('modal-phone').innerHTML = `<strong>${t.modalPhone}</strong>`;
            document.getElementById('modal-loc').innerText = t.mLoc;
            document.getElementById('modal-msg').innerText = t.mMsg;
            updateClock();
        }

        window.onclick = function(e) { if (e.target == document.getElementById("ownerModal")) toggleModal(false); }
    </script>
</body>
</html>