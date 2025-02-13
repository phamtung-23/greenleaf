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
    $supplierId = $_POST['supplierId'];
    
    $filePath = '../' . INVENTORY_JSON_PATH;
    $data = getGoodsBySupplier($supplierId, $filePath);

    echo json_encode(['success' => true, 'message' => 'Lấy dữ liệu thành công!', 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
