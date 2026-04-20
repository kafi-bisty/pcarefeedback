<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ডিজিটাল পরিদর্শন বই - পেশেন্ট কেয়ার হাসপাতাল</title>
    <!-- Font Awesome & Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        :root {
            --navy: #0D1B3E;
            --teal: #00A8B5;
            --gold: #ED8F03;
            --white: #ffffff;
        }

        body { 
            background: linear-gradient(135deg, var(--navy) 0%, var(--teal) 100%); 
            font-family: 'Hind Siliguri', sans-serif; 
            margin: 0; padding: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }

        #welcome-gate {
            background: white;
            padding: 40px;
            border-radius: 40px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            max-width: 450px; width: 90%;
            display: block;
            z-index: 100;
            border: 3px solid var(--gold);
        }

        #motivation-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: var(--navy);
            z-index: 1000;
            color: white;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }

        #main-form-card {
            display: none;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 35px;
            width: 95%; max-width: 550px;
            padding: 30px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.3);
            max-height: 90vh;
            overflow-y: auto;
        }

        .input-group { margin-bottom: 18px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 700; color: var(--navy); }
        input, textarea, select { width: 100%; padding: 14px; border: 2px solid #edf2f7; border-radius: 15px; outline: none; font-size: 15px; background: #f8fafc; box-sizing: border-box; }
        
        /* Voice Button Styling */
        .voice-btn {
            background: #f1f2f6;
            border: 1px solid #ddd;
            padding: 5px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            color: var(--teal);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.3s;
        }
        .voice-btn.active { background: #ff4757; color: white; border-color: #ff4757; }

        .btn { padding: 15px; border-radius: 18px; border: none; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px; width: 100%; }
        .btn-gold { background: linear-gradient(45deg, #FFB75E, #ED8F03); color: #fff; }
        .btn-teal { background: var(--teal); color: #fff; margin-top: 10px; }
        
        #signature-pad { width: 100%; height: 180px; border: 2px dashed var(--teal); border-radius: 20px; background: #fff; cursor: crosshair; }
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin: 15px 0; font-size: 30px; }
        .star-rating label { color: #ddd; cursor: pointer; }
        .star-rating input:checked ~ label { color: var(--gold); }
        .star-rating input { display: none; }
        
        .progress-container { display: flex; justify-content: space-between; margin-bottom: 20px; position: relative; }
        .progress-line { position: absolute; top: 50%; left: 0; width: 100%; height: 3px; background: #eee; transform: translateY(-50%); z-index: 1; }
        .progress-fill { position: absolute; top: 50%; left: 0; width: 0%; height: 3px; background: var(--gold); transform: translateY(-50%); z-index: 2; }
        .step-circle { width: 30px; height: 30px; background: #fff; border: 2px solid #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 3; font-weight: bold; font-size: 12px; }
        .step-circle.active { border-color: var(--gold); background: var(--gold); color: #fff; }
        .step-content { display: none; }
        .step-content.active { display: block; }
    </style>
</head>
<body>

    <!-- ১. Welcome Gate -->
    <div id="welcome-gate" class="animate__animated animate__zoomIn">
        <i class="fas fa-hospital-user" style="font-size: 50px; color: var(--gold); margin-bottom: 20px;"></i>
        <h2 style="margin: 0;">সম্মানিত অতিথি,</h2>
        <p style="color: #666; margin-bottom: 25px;">পরিদর্শন বইতে প্রবেশের জন্য আপনার পরিচয় দিন।</p>
        
        <div class="input-group">
            <input type="text" id="gate-name" placeholder="আপনার নাম লিখুন" required>
        </div>
        <div class="input-group">
            <select id="gate-role" required>
                <option value="">আপনার পরিচয় নির্বাচন করুন</option>
                <option value="প্রশাসনিক">প্রশাসনিক/সরকারি কর্মকর্তা</option>
                <option value="সিভিল সার্জন">সিভিল সার্জন / সরকারি কর্মকর্তা</option>
                <option value="বিশিষ্ট ব্যক্তি">বিশিষ্ট ব্যক্তি / সমাজসেবক</option>
                <option value="অভিভাবক">রোগীর অভিভাবক / আত্মীয়</option>
                <option value="অন্যান্য">অন্যান্য</option>
            </select>
        </div>
        <button class="btn btn-gold" onclick="startMotivation()">ভিতরে প্রবেশ করুন &nbsp; <i class="fas fa-arrow-right"></i></button>
    </div>

    <!-- ২. Motivation Overlay -->
    <div id="motivation-overlay">
        <i class="fas fa-award animate__animated animate__heartBeat animate__infinite" style="font-size: 80px; color: var(--gold);"></i>
        <div id="motivation-content" style="font-size: 24px; font-weight: bold; margin-top: 20px; line-height: 1.5;"></div>
    </div>

    <!-- ৩. মূল পরিদর্শন ফর্ম কার্ড -->
    <div class="main-card" id="main-form-card">
        <center>
            <img src="images/logo.png" style="max-width: 100px;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
            <h3 id="personalized-title" style="color: var(--navy); margin-top: 10px;">পরিদর্শন মন্তব্য বই</h3>
        </center>

        <div class="progress-container">
            <div class="progress-line"></div>
            <div class="progress-fill" id="progress-fill"></div>
            <div class="step-circle active" id="circle-1">১</div>
            <div class="step-circle" id="circle-2">২</div>
            <div class="step-circle" id="circle-3">৩</div>
        </div>

        <form action="submit_inspection.php" method="POST" id="inspectionForm">
            <input type="hidden" name="visitor_name" id="final-name">
            <input type="hidden" name="designation" id="final-deg">

            <!-- Step 1 -->
            <div class="step-content active" id="step-1">
                <div class="input-group">
                    <label>মোবাইল নম্বর</label>
                    <input type="text" name="phone" placeholder="০১৭XXXXXXXX" required>
                </div>
                <div class="input-group">
                    <label>প্রতিষ্ঠানের নাম</label>
                    <input type="text" name="organization" placeholder="অফিস বা প্রতিষ্ঠানের নাম (যদি থাকে)">
                </div>
                <div class="input-group">
                    <label>ঠিকানা</label>
                    <input type="text" name="visitor_address" placeholder="গ্রাম/শহর, জেলা" required>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-content" id="step-2">
                <div class="input-group">
                    <label>পরিদর্শনের উদ্দেশ্য</label>
                    <input type="text" name="purpose" placeholder="উদা: নিয়মিত পরিদর্শন" required>
                </div>
                <div class="input-group">
                    <label>আপনার রেটিং দিন</label>
                    <div class="star-rating">
                        <input type="radio" name="rating" id="st5" value="5" checked><label for="st5" class="fas fa-star"></label>
                        <input type="radio" name="rating" id="st4" value="4"><label for="st4" class="fas fa-star"></label>
                        <input type="radio" name="rating" id="st3" value="3"><label for="st3" class="fas fa-star"></label>
                        <input type="radio" name="rating" id="st2" value="2"><label for="st2" class="fas fa-star"></label>
                        <input type="radio" name="rating" id="st1" value="1"><label for="st1" class="fas fa-star"></label>
                    </div>
                </div>
                <div class="input-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <label style="margin-bottom: 0;">আপনার মন্তব্য</label>
                        <!-- Voice Trigger Button -->
                        <button type="button" class="voice-btn" id="voice-trigger" onclick="startDictation()">
                            <i class="fas fa-microphone"></i> <span id="voice-status">কথা বলে লিখুন</span>
                        </button>
                    </div>
                    <textarea name="comments" id="message_box" rows="4" placeholder="আপনার অভিজ্ঞতা লিখুন..." required></textarea>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-content" id="step-3">
                <div class="input-group">
                    <label>পরামর্শ (যদি থাকে)</label>
                    <textarea name="suggestions" rows="2" placeholder="উন্নয়নের জন্য পরামর্শ..."></textarea>
                </div>
                <div class="input-group">
                    <label>ডিজিটাল স্বাক্ষর (সই করুন)</label>
                    <div style="position:relative;">
                        <canvas id="signature-pad"></canvas>
                        <button type="button" onclick="clearSig()" style="position:absolute; top:5px; right:5px; padding:5px; background:red; color:white; border:none; border-radius:5px; font-size:10px;">Clear</button>
                    </div>
                </div>
                <input type="hidden" name="signature_data" id="signature_data">
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-prev" id="prevBtn" onclick="changeStep(-1)" style="display:none; background:#eee; color:#333;">পেছনে</button>
                <button type="button" class="btn btn-teal" id="nextBtn" onclick="changeStep(1)">পরবর্তী</button>
                <button type="submit" class="btn btn-gold" id="submitBtn" style="display:none;">জমা দিন</button>
            </div>
        </form>
    </div>

    <script>
        function startMotivation() {
            const name = document.getElementById('gate-name').value;
            const role = document.getElementById('gate-role').value;

            if(!name || !role) { alert("দয়া করে নাম এবং পরিচয় প্রদান করুন।"); return; }

            document.getElementById('final-name').value = name;
            document.getElementById('final-deg').value = role;
            document.getElementById('personalized-title').innerText = "স্বাগতম: " + name;

            let msg = `সম্মানিত ${name},<br>আপনার উপস্থিতি আমাদের অনুপ্রাণিত করে।`;
            if(role === "সিভিল সার্জন") msg = `শ্রদ্ধেয় সিভিল সার্জন ${name} মহোদয়,<br>আপনার সুচিন্তিত মতামত আমাদের সেবার মান বাড়াতে সাহায্য করবে।`;

            document.getElementById('welcome-gate').style.display = 'none';
            const overlay = document.getElementById('motivation-overlay');
            overlay.style.display = 'flex';
            document.getElementById('motivation-content').innerHTML = msg;

            setTimeout(() => {
                overlay.style.display = 'none';
                document.getElementById('main-form-card').style.display = 'block';
                document.getElementById('main-form-card').classList.add('animate__animated', 'animate__fadeIn');
            }, 3000);
        }

        // Voice to Text Function
        function startDictation() {
            if (window.hasOwnProperty('webkitSpeechRecognition')) {
                var recognition = new webkitSpeechRecognition();
                var btn = document.getElementById('voice-trigger');
                var status = document.getElementById('voice-status');

                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.lang = "bn-BD"; // বাংলা ভাষা

                recognition.onstart = function() {
                    btn.classList.add('active');
                    status.innerText = "শুনছি...";
                };

                recognition.onresult = function(e) {
                    document.getElementById('message_box').value += e.results[0][0].transcript + " ";
                    recognition.stop();
                };

                recognition.onend = function() {
                    btn.classList.remove('active');
                    status.innerText = "কথা বলে লিখুন";
                };

                recognition.onerror = function() {
                    recognition.stop();
                };

                recognition.start();
            } else {
                alert("দুঃখিত, আপনার ব্রাউজারে ভয়েস টাইপিং সাপোর্ট করে না। গুগল ক্রোম ব্যবহার করুন।");
            }
        }

        let currentStep = 1;
        function changeStep(n) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`circle-${currentStep}`).classList.remove('active');
            currentStep += n;
            document.getElementById(`step-${currentStep}`).classList.add('active');
            document.getElementById(`circle-${currentStep}`).classList.add('active');
            document.getElementById('progress-fill').style.width = ((currentStep - 1) / 2) * 100 + "%";
            
            document.getElementById('prevBtn').style.display = currentStep === 1 ? "none" : "block";
            document.getElementById('nextBtn').style.display = currentStep === 3 ? "none" : "block";
            document.getElementById('submitBtn').style.display = currentStep === 3 ? "block" : "none";
            if(currentStep === 3) resizeCanvas();
        }

        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        function resizeCanvas() { canvas.width = canvas.offsetWidth; canvas.height = 180; }
        canvas.addEventListener('mousedown', () => drawing = true);
        canvas.addEventListener('touchstart', (e) => { drawing = true; e.preventDefault(); });
        window.addEventListener('mouseup', () => drawing = false);
        window.addEventListener('touchend', () => drawing = false);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('touchmove', (e) => { draw(e); e.preventDefault(); });
        function draw(e) {
            if (!drawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#000';
            ctx.lineTo(x, y); ctx.stroke(); ctx.beginPath(); ctx.moveTo(x, y);
        }
        function clearSig() { ctx.clearRect(0, 0, canvas.width, canvas.height); ctx.beginPath(); }
        document.getElementById('inspectionForm').onsubmit = function() {
            document.getElementById('signature_data').value = canvas.toDataURL();
        };
    </script>
</body>
</html>