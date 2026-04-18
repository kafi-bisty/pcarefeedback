<?php
$type = $_GET['type'] ?? 'Praise';
$title = ($type == 'Praise') ? "প্রশংসা করুন" : "অভিযোগ জানান";
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - পেশেন্ট কেয়ার হাসপাতাল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        body { 
            background: #f0f4f8; 
            font-family: 'Hind Siliguri', sans-serif; 
            margin: 0; 
            padding: 20px; 
        }

        .form-container { 
            max-width: 500px; 
            margin: 0 auto; 
            padding: 0; 
            border-radius: 20px; 
            background: #fff; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #00A8B5, #0056b3);
            padding: 25px;
            text-align: center;
            color: #fff;
        }
        .logo-img {
            width: 80px; height: 80px;
            background: #fff; border-radius: 50%;
            padding: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }
        .hospital-name { font-size: 18px; font-weight: 700; margin: 0; }

        .form-content { padding: 25px; }
        .Praise-text { color: #28a745; text-align: center; margin-top: 0; }
        .Complaint-text { color: #dc3545; text-align: center; margin-top: 0; }

        label { display: block; margin-top: 15px; font-size: 14px; color: #444; font-weight: 600; }

        input, select, textarea { 
            width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; 
            border-radius: 10px; box-sizing: border-box; font-size: 14px; outline: none; transition: 0.3s;
        }
        input:focus, select:focus, textarea:focus { border-color: #00A8B5; box-shadow: 0 0 8px rgba(0, 168, 181, 0.2); }

        /* Voice Button Styling */
        .message-label-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        .voice-btn {
            background: #f1f2f6;
            border: 1px solid #ddd;
            padding: 5px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            color: #0056b3;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .voice-btn:hover { background: #e0e0e0; }
        .voice-btn.recording {
            background: #ff4757;
            color: white;
            border-color: #ff4757;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .submit-btn { 
            width: 100%; padding: 15px; color: white; border: none; 
            border-radius: 12px; font-size: 17px; font-weight: bold; 
            cursor: pointer; margin-top: 25px; transition: 0.3s;
        }
        .Praise-bg { background: linear-gradient(45deg, #28a745, #5cd85d); }
        .Complaint-bg { background: linear-gradient(45deg, #dc3545, #ff6b6b); }

        .back-link { display: block; text-align: center; margin: 20px 0; text-decoration: none; color: #666; font-size: 14px; }
    </style>
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <img src="images/logo.png" alt="Logo" class="logo-img" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3063/3063176.png'">
            <div class="hospital-name">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</div>
            <div style="font-size: 11px; opacity: 0.8; margin-top: 5px;"><i class="fas fa-map-marker-alt"></i> কলেজ রোড, বরগুনা</div>
        </div>

        <div class="form-content">
            <h2 class="<?php echo $type; ?>-text"><i class="fas <?php echo ($type=='Praise')?'fa-smile':'fa-frown'; ?>"></i> <?php echo $title; ?></h2>
            
            <form action="submit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                
                <label>আপনার নাম:</label>
                <input type="text" name="patient_name" placeholder="পুরো নাম লিখুন" required>
                
                <label>মোবাইল নম্বর:</label>
                <input type="text" name="patient_phone" placeholder="০১৭XXXXXXXX" required>
                
                <label>কর্মচারীর নাম (ঐচ্ছিক):</label>
                <input type="text" name="employee_name" placeholder="কার বিরুদ্ধে অভিযোগ বা প্রশংসা?">
                
                <label>বিভাগ নির্বাচন করুন:</label>
                <select name="department" required>
                    <option value="">সিলেক্ট করুন</option>
                    <option value="Emergency">ইমার্জেন্সি</option>
                    <option value="Ward">ওয়ার্ড</option>
                    <option value="OPD">আউটডোর</option>
                    <option value="Billing">বিলিং ও কাউন্টার</option>
                </select>
                
                <!-- Voice Text Area Header -->
                <div class="message-label-wrapper">
                    <label style="margin-top:0;">বিস্তারিত লিখুন:</label>
                    <button type="button" class="voice-btn" id="voice-trigger" onclick="startDictation()">
                        <i class="fas fa-microphone"></i> <span id="voice-status">কথা বলে লিখুন</span>
                    </button>
                </div>
                <textarea name="message" id="message_box" rows="5" placeholder="এখানে আপনার কথাগুলো লিখুন..." required></textarea>

                <label>📂 প্রমাণ (ছবি বা ভিডিও - ঐচ্ছিক):</label>
                <input type="file" name="evidence" accept="image/*,video/*" class="file-input" style="background:#f9f9f9; border:2px dashed #ccc; padding:10px; cursor:pointer;">
                
                <button type="submit" class="submit-btn <?php echo $type; ?>-bg">
                    <i class="fas fa-paper-plane"></i> জমা দিন
                </button>
            </form>
            
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> পেছনে যান</a>
        </div>
    </div>

    <script>
        function startDictation() {
            if (window.hasOwnProperty('webkitSpeechRecognition')) {
                var recognition = new webkitSpeechRecognition();
                var btn = document.getElementById('voice-trigger');
                var statusTxt = document.getElementById('voice-status');

                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.lang = "bn-BD"; // বাংলা ভাষা

                recognition.onstart = function() {
                    btn.classList.add('recording');
                    statusTxt.innerText = "শুনছি... কথা বলুন";
                };

                recognition.onresult = function(e) {
                    var transcript = e.results[0][0].transcript;
                    document.getElementById('message_box').value += transcript + " ";
                    recognition.stop();
                };

                recognition.onerror = function() {
                    recognition.stop();
                    btn.classList.remove('recording');
                    statusTxt.innerText = "কথা বলে লিখুন";
                };

                recognition.onend = function() {
                    btn.classList.remove('recording');
                    statusTxt.innerText = "কথা বলে লিখুন";
                };

                recognition.start();
            } else {
                alert("দুঃখিত, আপনার ব্রাউজারে ভয়েস টাইপিং সাপোর্ট করে না। গুগল ক্রোম ব্যবহার করুন।");
            }
        }
    </script>
</body>
</html>