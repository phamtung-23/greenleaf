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
    $inventoryId = $_POST['id'];
    $filePath = '../'.IMPORT_EXPORT_INVENTORY_JSON_PATH;
    $dataList = readJsonFile($filePath);

    $inventoryData = calculateInventoryReportById($inventoryId, $dataList);

    echo json_encode(['success' => true, 'data' => $inventoryData]);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
