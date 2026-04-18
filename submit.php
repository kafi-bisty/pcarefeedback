<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ফর্ম থেকে ডাটা নেওয়া এবং সিকিউরিটি নিশ্চিত করা
    $type = $_POST['type'];
    $p_name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $p_phone = mysqli_real_escape_string($conn, $_POST['patient_phone']);
    $e_name = mysqli_real_escape_string($conn, $_POST['employee_name']);
    $dept = $_POST['department'];
    $msg = mysqli_real_escape_string($conn, $_POST['message']);

    // ১. ইউনিক ট্র্যাকিং আইডি জেনারেট করা (উদা: PCARE4821)
    $tracking_id = "PCARE" . rand(1000, 9999);

    // ২. ফাইল আপলোড প্রসেসিং (ছবি বা ভিডিও)
    $file_name = NULL; // ডিফল্টভাবে খালি থাকবে
    if (!empty($_FILES['evidence']['name'])) {
        $target_dir = "uploads/";
        
        // যদি ফোল্ডার না থাকে তবে তৈরি করে নেওয়া (নিরাপত্তার জন্য)
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES["evidence"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . rand(100, 999) . "." . $file_ext; // ইউনিক ফাইলের নাম
        $target_file = $target_dir . $file_name;

        // ফাইলটি uploads ফোল্ডারে পাঠানো
        move_uploaded_file($_FILES["evidence"]["tmp_name"], $target_file);
    }

    // ৩. ডাটাবেসে ইনসার্ট করা (tracking_id এবং evidence_file সহ)
    $sql = "INSERT INTO hospital_feedback (type, patient_name, patient_phone, employee_name, department, message, tracking_id, evidence_file) 
            VALUES ('$type', '$p_name', '$p_phone', '$e_name', '$dept', '$msg', '$tracking_id', '$file_name')";

    if (mysqli_query($conn, $sql)) {
        // ৪. রোগীকে অ্যালার্ট দিয়ে ট্র্যাকিং আইডি জানানো
        echo "<script>
                alert('ধন্যবাদ! আপনার ফিডব্যাক সফলভাবে জমা হয়েছে।\\n\\nআপনার ট্র্যাকিং আইডি: $tracking_id\\n\\nভবিষ্যতে আপনার অভিযোগের আপডেট দেখতে এই আইডিটি অবশ্যই সংরক্ষণ করুন।');
                window.location.href='index.php';
              </script>";
    } else {
        // ডাটাবেস এরর হলে জানানো
        echo "Error: " . mysqli_error($conn);
    }
}
?>