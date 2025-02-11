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
    $id = $_POST['id'];
    $updatedCodeGoods = $_POST['updatedCodeGoods'];
    $updatedNameGoods = $_POST['updatedNameGoods'];

    // Tạo dữ liệu mới từ form
    $newData = [
        'codeGoods' => $updatedCodeGoods,
        'nameGoods' => $updatedNameGoods
    ];

    // Cập nhật dữ liệu mới vào file JSON
    updateJsonFileById('../' . GOODS_JSON_LINK, $id, $newData);

    echo json_encode(['success' => true, 'message' => 'Cập nhật hàng hóa thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
