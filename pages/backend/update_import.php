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

    $import_id = isset($_POST['import_id']) ? $_POST['import_id'] : '';
    $inventory_id = isset($_POST['inventory_id']) ? $_POST['inventory_id'] : '';
    // Lấy dữ liệu từ form
    $supplier = isset($_POST['supplier']) ? $_POST['supplier'] : '';
    $item = isset($_POST['item']) ? $_POST['item'] : '';
    $weight = isset($_POST['weight']) ? $_POST['weight'] : 0;
    $disposed_weight = isset($_POST['disposed_weight']) ? $_POST['disposed_weight'] : 0;
    $remaining_weight = isset($_POST['remaining_weight']) ? $_POST['remaining_weight'] : 0;
    $unit_price = isset($_POST['unit_price']) ? str_replace('.', '', $_POST['unit_price']) : 0; // Bỏ dấu chấm
    $total_price = isset($_POST['total_price']) ? str_replace('.', '', $_POST['total_price']) : 0; // Bỏ dấu chấm
    $update_time = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại

    // Xử lý upload hình ảnh
    $imageLinks = uploadImages($_FILES, '../' . IMAGE_DIR);

    // Đọc dữ liệu hiện có
    $data = readJsonFile('../' . GOODS_JSON_PATH);

    $importData = [];
    // Lấy data import by id
    foreach ($data as $key => $value) {
        if ($value['id'] == $import_id) {
            $importData = $value;
            break;
        }
    }

    if (empty($importData)) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin nhập hàng!']);
        exit();
    }
    // Tạo dữ liệu mới từ form
    $updateData = [
        'updatedAt' => $update_time,
        'weight' => (float)$weight,
        'disposed_weight' => (float)$disposed_weight,
        'remaining_weight' => (float)$remaining_weight,
        'total_price' => (int)$total_price,
        'images' => !empty($imageLinks) ? $imageLinks : $importData['images'], // Thêm đường dẫn hình ảnh
    ];

    // Ghi dữ liệu vào file JSON
    updateJsonFileById('../' . GOODS_JSON_PATH, $import_id, $updateData);

    // // lấy số lượng hàng hóa hiện có
    // $remaining_weight_in_inventory = getWeightByItemSupplierPrice($item, $supplier, (int)$unit_price, '../'.INVENTORY_JSON_PATH);

    // ======= Cập nhật số lượng hàng hóa trong kho
    $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
    if (checkGoodsExist($item, $supplier, (int)$unit_price, $inventoryData)) {
        // trừ đi số lượng hàng hóa đã nhập trước đó
        $inventoryData = updateInventory($item, $supplier, (float)$importData['remaining_weight'], (int)$unit_price, $inventoryData, 'export');
        // cộng thêm số lượng hàng hóa mới nhập
        $inventoryData = updateInventory($item, $supplier, (float)$remaining_weight, (int)$unit_price, $inventoryData, 'import');
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin hàng hóa trong kho!']);
        exit();
    }
    writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);

    $newUpdateData = getDataById($import_id, '../' . GOODS_JSON_PATH);

    // ======= cập nhật thông tin nhập hàng vào import_export_inventory.json
    $current_date = date('d-m-Y');
    updateImportExportGoodsInInventory($inventory_id, $import_id, $current_date, $newUpdateData, '../' . IMPORT_EXPORT_INVENTORY_JSON_PATH, 'import');


    echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin nhập hàng thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
