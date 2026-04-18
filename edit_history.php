<?php
include 'db.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM hospital_feedback WHERE id='$id'");
$data = mysqli_fetch_assoc($res);

if (isset($_POST['update_history'])) {
    $owner_reply = mysqli_real_escape_string($conn, $_POST['owner_reply']);
    $status = $_POST['status'];
    
    mysqli_query($conn, "UPDATE hospital_feedback SET owner_reply='$owner_reply', status='$status' WHERE id='$id'");
    echo "<script>alert('সংশোধন সফল হয়েছে!'); window.location.href='history.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Edit History Record</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; }
        .edit-box { background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        input, select, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 10px; }
        .save-btn { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="edit-box">
        <h2>রেকর্ড সংশোধন করুন</h2>
        <p><strong>Tracking ID:</strong> <?php echo $data['tracking_id']; ?></p>
        <form method="POST">
            <label>বর্তমান অবস্থা:</label>
            <select name="status">
                <option value="Pending" <?php if($data['status']=='Pending') echo 'selected'; ?>>Pending</option>
                <option value="Resolved" <?php if($data['status']=='Resolved') echo 'selected'; ?>>Resolved</option>
            </select>
            
            <label>মালিকের জবাব সংশোধন:</label>
            <textarea name="owner_reply" rows="4" required><?php echo $data['owner_reply']; ?></textarea>
            
            <button type="submit" name="update_history" class="save-btn">পরিবর্তন সেভ করুন</button>
        </form>
        <br>
        <center><a href="history.php" style="text-decoration:none; color:#666;">বাতিল করুন</a></center>
    </div>
</body>
</html>