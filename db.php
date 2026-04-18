<?php
// ১. আপনার বর্তমান হোস্ট চেক করা
$host_name = $_SERVER['HTTP_HOST'];

if ($host_name == 'localhost') {
    // --- লোকাল সার্ভার সেটিংস (XAMPP) ---
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "hospital_db"; // আপনার লোকাল ডাটাবেস নাম
} else {
    // --- অনলাইন সার্ভার সেটিংস (InfinityFree) ---
    // এই তথ্যগুলো আপনার InfinityFree কন্ট্রোল প্যানেল (MySQL Databases) থেকে পাবেন
    $host = "sql112.infinityfree.com";      // আপনার অনলাইন MySQL Hostname
    $user = "if0_41693529";                // আপনার অনলাইন MySQL Username
    $pass = "ph2c26eeeU6zj2D";        // আপনার অনলাইন MySQL Password
    $dbname = "if0_41693529_hospital_bd";  // আপনার অনলাইন Database Name
}

// ২. ডাটাবেস কানেকশন তৈরি
$conn = mysqli_connect($host, $user, $pass, $dbname);

// ৩. কানেকশন চেক করা
if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

// ৪. সেশন শুরু করা (সব পেজের জন্য একবার)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ইউনিকোড সাপোর্ট (বাংলা লেখা যাতে ঠিকমতো আসে)
mysqli_set_charset($conn, "utf8mb4");
?>