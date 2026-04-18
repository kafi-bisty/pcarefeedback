<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ফর্ম থেকে ডাটা নেওয়া এবং সুরক্ষিত করা
    $name = mysqli_real_escape_string($conn, $_POST['visitor_name']);
    $deg = mysqli_real_escape_string($conn, $_POST['designation']);
    $org = mysqli_real_escape_string($conn, $_POST['organization']);
    $addr = mysqli_real_escape_string($conn, $_POST['visitor_address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);
    $suggestions = mysqli_real_escape_string($conn, $_POST['suggestions']);
    $rating = isset($_POST['rating']) ? $_POST['rating'] : 5;

    // ১. ডিজিটাল স্বাক্ষর সেভ করার লজিক
    $sig_filename = "";
    if (!empty($_POST['signature_data'])) {
        $sig_data = $_POST['signature_data'];
        
        // Base64 ডাটা থেকে ইমেজ ডাটা আলাদা করা
        $sig_data = str_replace('data:image/png;base64,', '', $sig_data);
        $sig_data = str_replace(' ', '+', $sig_data);
        $decoded_data = base64_decode($sig_data);
        
        // ইউনিক ফাইলের নাম তৈরি (উদা: sig_1712345678.png)
        $sig_filename = "sig_" . time() . ".png";
        $target_dir = "uploads/signatures/";
        $file_path = $target_dir . $sig_filename;

        // যদি ফোল্ডার না থাকে তবে তৈরি করা
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // ছবি হিসেবে ফোল্ডারে সেভ করা
        file_put_contents($file_path, $decoded_data);
    }

    // ২. ডাটাবেসে ইনসার্ট করা
    $sql = "INSERT INTO hospital_inspections (
                visitor_name, 
                designation, 
                organization, 
                visitor_address, 
                phone, 
                purpose, 
                comments, 
                suggestions, 
                visitor_signature, 
                rating
            ) VALUES (
                '$name', 
                '$deg', 
                '$org', 
                '$addr', 
                '$phone', 
                '$purpose', 
                '$comments', 
                '$suggestions', 
                '$sig_filename', 
                '$rating'
            )";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('আপনার মূল্যবান মতামত ও স্বাক্ষর সফলভাবে সংরক্ষিত হয়েছে। ধন্যবাদ!'); 
                window.location.href='index.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>