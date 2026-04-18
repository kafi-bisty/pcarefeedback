<?php
include 'db.php';

// লগইন ভেরিফিকেশন
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit(); 
}

// ১. ডিলিট করার লজিক (Permanent Delete)
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM hospital_feedback WHERE id='$id'");
    echo "<script>alert('রেকর্ডটি চিরতরে মুছে ফেলা হয়েছে!'); window.location.href='history.php';</script>";
}

// ২. পুনরায় ড্যাশবোর্ডে পাঠানোর লজিক (Restore)
if (isset($_GET['restore_id'])) {
    $restore_id = mysqli_real_escape_string($conn, $_GET['restore_id']);
    mysqli_query($conn, "UPDATE hospital_feedback SET is_archived=0 WHERE id='$restore_id'");
    echo "<script>alert('এটি পুনরায় মেইন ড্যাশবোর্ডে পাঠানো হয়েছে।'); window.location.href='history.php';</script>";
}

// আর্কাইভ করা ডাটা তুলে আনা
$result = mysqli_query($conn, "SELECT * FROM hospital_feedback WHERE is_archived=1 ORDER BY id DESC");
$total_archived = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>আরকাইভ ইতিহাস - পেশেন্ট কেয়ার হাসপাতাল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        :root {
            --primary: #00A8B5;
            --secondary: #0056b3;
            --success: #2ecc71;
            --danger: #e74c3c;
            --dark: #2f3542;
            --light: #f4f7fa;
        }

        body { background-color: var(--light); font-family: 'Hind Siliguri', sans-serif; margin: 0; padding-bottom: 50px; }
        
        /* Header Style */
        .hospital-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; padding: 25px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .hospital-header img { width: 70px; height: 70px; background: white; border-radius: 50%; padding: 5px; margin-bottom: 10px; }
        .hospital-header h1 { margin: 0; font-size: 20px; }

        .container { width: 95%; max-width: 1300px; margin: 20px auto; }

        /* Navigation */
        .nav-bar { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .btn-back { background: var(--dark); color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-back:hover { background: #000; transform: translateX(-5px); }

        /* Summary Tag */
        .summary-tag { background: var(--primary); color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; }

        /* Modern Table UI */
        .table-wrapper { background: white; border-radius: 20px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #f8fafc; padding: 18px; color: #64748b; text-align: left; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
        td { padding: 18px; border-bottom: 1px solid #f1f5f9; vertical-align: top; font-size: 14px; }
        tr:hover { background: #f9fbfd; }

        /* Action Buttons */
        .action-btns { display: flex; gap: 8px; }
        .action-btn { padding: 8px 12px; border-radius: 6px; color: white; text-decoration: none; font-size: 12px; font-weight: bold; transition: 0.3s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-edit { background: var(--secondary); }
        .btn-restore { background: var(--success); }
        .btn-delete { background: var(--danger); }
        .action-btn:hover { opacity: 0.8; transform: translateY(-2px); }

        /* Typography */
        .tracking-id { font-weight: bold; color: var(--dark); background: #eee; padding: 2px 8px; border-radius: 4px; }
        .msg-text { color: #2f3542; line-height: 1.5; margin-bottom: 10px; }
        .reply-text { color: var(--success); font-weight: 600; font-size: 13px; border-top: 1px dashed #ddd; padding-top: 5px; }

        @media (max-width: 768px) {
            .nav-bar { flex-direction: column; gap: 10px; text-align: center; }
            th, td { padding: 12px; font-size: 12px; }
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header class="hospital-header">
        <img src="images/logo.png" alt="Hospital Logo">
        <h1>পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h1>
        <p style="margin: 5px 0 0; font-size: 13px; opacity: 0.9;"><i class="fas fa-map-marker-alt"></i> দীনু মঞ্জিল, কলেজ রোড, বরগুনা</p>
    </header>

    <div class="container">
        
        <!-- Navigation Navbar -->
        <div class="nav-bar">
            <a href="admin.php" class="btn-back"><i class="fas fa-arrow-left"></i> ড্যাশবোর্ডে ফিরে যান</a>
            <div style="text-align: right;">
                <span class="summary-tag">মোট আর্কাইভ রেকর্ড: <?php echo $total_archived; ?></span>
            </div>
        </div>

        <h2 style="color: var(--dark); margin-bottom: 20px;"><i class="fas fa-history"></i> আরকাইভকৃত অভিযোগ ও প্রশংসার ইতিহাস</h2>

        <!-- Data Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID & ধরন</th>
                        <th>বিভাগ ও কর্মচারী</th>
                        <th>বার্তা ও কর্তৃপক্ষের জবাব</th>
                        <th>রোগীর তথ্য</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td align="center">
                                <span class="tracking-id"><?php echo $row['tracking_id']; ?></span><br>
                                <div style="font-size: 20px; margin-top: 8px;"><?php echo ($row['type']=='Praise'?'😊':'😟'); ?></div>
                            </td>
                            <td>
                                <div style="color: #747d8c; font-size: 12px;"><?php echo $row['department']; ?></div>
                                <div style="font-weight: bold; margin-top: 4px;"><?php echo $row['employee_name']; ?></div>
                            </td>
                            <td>
                                <div class="msg-text"><?php echo nl2br($row['message']); ?></div>
                                <div class="reply-text">
                                    <i class="fas fa-reply-all"></i> জবাব: 
                                    <?php echo $row['owner_reply'] ? $row['owner_reply'] : "<span style='color:#999; font-weight:normal;'>কোনো জবাব দেওয়া হয়নি</span>"; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: bold;"><?php echo $row['patient_name']; ?></div>
                                <div style="color: #747d8c; font-size: 12px;"><?php echo $row['patient_phone']; ?></div>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <!-- Edit Link -->
                                    <a href="edit_history.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit" title="এডিট">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Restore Link -->
                                    <a href="history.php?restore_id=<?php echo $row['id']; ?>" class="action-btn btn-restore" title="রিস্টোর" onclick="return confirm('এটি কি পুনরায় ড্যাশবোর্ডে পাঠাতে চান?')">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    <!-- Delete Link -->
                                    <a href="history.php?delete_id=<?php echo $row['id']; ?>" class="action-btn btn-delete" title="ডিলিট" onclick="return confirm('আপনি কি নিশ্চিত যে এটি চিরতরে মুছে ফেলবেন?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" align="center" style="padding: 50px; color: #999; font-size: 18px;">আর্কাইভে কোনো রেকর্ড পাওয়া যায়নি।</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>