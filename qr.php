<?php
// qr.php
include 'db.php';

// ডাটাবেস থেকে বর্তমানে সেট করা লিঙ্কটি নিয়ে আসা
$result = mysqli_query($conn, "SELECT redirect_url FROM qr_settings WHERE id=1");
$row = mysqli_fetch_assoc($result);

// যদি ডাটাবেসে লিঙ্ক থাকে তবে সেখানে পাঠাবে, না থাকলে index.php তে পাঠাবে
$target = ($row && !empty($row['redirect_url'])) ? $row['redirect_url'] : 'index.php';

header("Location: " . $target);
exit();
?>