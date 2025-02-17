<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_name("greenleaf");
session_start();

if (!isset($_SESSION['email'])) {
    echo "<script>alert('You are not authorized to view this page. Please log in.'); window.location.href = '../../index.php';</script>";
    exit();
}

include '../../helper/general.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from form
    $codeNCC = isset($_POST['codeNCC']) ? $_POST['codeNCC'] : '';
    $nameNCC = isset($_POST['nameNCC']) ? $_POST['nameNCC'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $bankNumber = isset($_POST['bankNumber']) ? $_POST['bankNumber'] : '';
    $bankName = isset($_POST['bankName']) ? $_POST['bankName'] : '';

    // Đọc dữ liệu hiện có
    $data = readJsonFile('../'.SUPPLIERS_JSON_LINK);

    // Tạo dữ liệu mới từ form
    $newData = [
        'id' => uniqid('ncc_'),
        'codeNCC' => $codeNCC,
        'nameNCC' => $nameNCC,
        'address' => $address,
        'phone' => $phone,
        'bankNumber' => $bankNumber,
        'bankName' => $bankName
    ];

    // Thêm dữ liệu mới vào danh sách
    $data[] = $newData;

    // Ghi dữ liệu vào file JSON
    writeJsonFile('../'.SUPPLIERS_JSON_LINK, $data);

    echo json_encode(['success' => true, 'message' => 'Thêm nhà cung cấp thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
