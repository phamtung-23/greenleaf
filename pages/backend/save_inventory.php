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
    // Đường dẫn file JSON
    $filePath = '../' . GOODS_JSON_PATH;
    // Đường dẫn thư mục lưu hình ảnh
    $imageDir = '../' . IMAGE_DIR;

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
    }

    // Lấy dữ liệu từ form
    $supplier = isset($_POST['supplier']) ? $_POST['supplier'] : '';
    $item = isset($_POST['item']) ? $_POST['item'] : '';
    $weight = isset($_POST['weight']) ? $_POST['weight'] : 0;
    $disposed_weight = isset($_POST['disposed_weight']) ? $_POST['disposed_weight'] : 0;
    $remaining_weight = isset($_POST['remaining_weight']) ? $_POST['remaining_weight'] : 0;
    $unit_price = isset($_POST['unit_price']) ? str_replace('.', '', $_POST['unit_price']) : 0; // Bỏ dấu chấm
    $total_price = isset($_POST['total_price']) ? str_replace('.', '', $_POST['total_price']) : 0; // Bỏ dấu chấm
    $time = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại

    // Xử lý upload hình ảnh
    $imageLinks = [];
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        $imageLinks = uploadImages($_FILES, '../' . IMAGE_DIR);
    }

    // Đọc dữ liệu hiện có
    $data = readJsonFile('../' . GOODS_JSON_PATH);

    // Lấy ID cuối cùng và tăng thêm 1
    $lastId = end($data)['index'] ?? 0;
    // Tạo dữ liệu mới từ form
    $newData = [
        'id' => uniqid('import_'), // ID duy nhất
        'index' => $lastId + 1,
        'createdAt' => $time,
        'supplier' => $supplier,
        'item' => $item,
        'weight' => (float)$weight,
        'disposed_weight' => (float)$disposed_weight,
        'remaining_weight' => (float)$remaining_weight,
        'unit_price' => (int)$unit_price,
        'total_price' => (int)$total_price,
        'images' => $imageLinks, // Thêm đường dẫn hình ảnh
    ];

    // Thêm dữ liệu mới vào danh sách
    $data[] = $newData;

    // Ghi dữ liệu vào file JSON
    writeJsonFile('../' . GOODS_JSON_PATH, $data);

    // lấy số lượng hàng hóa hiện có
    $remaining_weight_in_inventory = getWeightByItemSupplierPrice($item, $supplier, (int)$unit_price, '../' . INVENTORY_JSON_PATH);

    // ======= Cập nhật số lượng hàng hóa trong kho
    $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
    if (checkGoodsExist($item, $supplier, (int)$unit_price, $inventoryData)) {
        $inventoryData = updateInventory($item, $supplier, (float)$remaining_weight, (int)$unit_price, $inventoryData, 'import');
    } else {
        $inventoryData = addInventory($item, $supplier, (float)$remaining_weight, (int)$unit_price, $inventoryData);
    }
    writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);

    // ======= Lưu thông tin nhập hàng vào import_export_inventory.json
    $current_date = date('d-m-Y');
    saveImportExportGoodsInInventory($supplier, $item, (float)$remaining_weight_in_inventory, (int)$unit_price, $current_date, $newData, '../' . IMPORT_EXPORT_INVENTORY_JSON_PATH, 'import');


    echo json_encode(['success' => true, 'message' => 'Đã nhập hàng thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
