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
    $codeGoods = isset($_POST['codeGoods']) ? $_POST['codeGoods'] : '';
    $nameGoods = isset($_POST['nameGoods']) ? $_POST['nameGoods'] : '';

    // Đọc dữ liệu hiện có
    $data = readJsonFile('../'.GOODS_JSON_LINK);

    // Tạo dữ liệu mới từ form
    $newData = [
        'id' => uniqid('goods_'),
        'codeGoods' => $codeGoods,
        'nameGoods' => $nameGoods
    ];

    // Thêm dữ liệu mới vào danh sách
    $data[] = $newData;

    // Ghi dữ liệu vào file JSON
    writeJsonFile('../'.GOODS_JSON_LINK, $data);

    echo json_encode(['success' => true, 'message' => 'Thêm hàng hóa thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
