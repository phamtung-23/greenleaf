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
    $filePath = '../' . EXPORT_GOODS_JSON_PATH;
    // Đường dẫn thư mục lưu hình ảnh
    $imageDir = '../' . IMAGE_EXPORT_DIR;

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
    }

    $export_id = isset($_POST['export_id']) ? $_POST['export_id'] : '';
    $inventory_id = isset($_POST['inventory_id']) ? $_POST['inventory_id'] : '';
    // Lấy dữ liệu từ form
    $supplier = isset($_POST['supplier']) ? $_POST['supplier'] : '';
    $item = isset($_POST['item']) ? $_POST['item'] : '';
    $remaining_weight = isset($_POST['remaining_weight']) ? $_POST['remaining_weight'] : 0;
    $customer = isset($_POST['customer']) ? $_POST['customer'] : '';
    $export_weight = isset($_POST['export_weight']) ? $_POST['export_weight'] : 0;
    $lost_weight = isset($_POST['lost_weight']) ? $_POST['lost_weight'] : 0;
    $update_time = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại

    // Xử lý upload hình ảnh
    $imageLinks = uploadImages($_FILES, '../' . IMAGE_EXPORT_DIR);

    // Đọc dữ liệu hiện có
    $data = readJsonFile('../' . EXPORT_GOODS_JSON_PATH);

    $exportData = [];
    // Lấy data export by id
    foreach ($data as $key => $value) {
        if ($value['id'] == $export_id) {
            $exportData = $value;
            break;
        }
    }

    if (empty($exportData)) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin xuất hàng!']);
        exit();
    }

    $unit_price = $exportData['unit_price'];
    $export_weight_old = $exportData['export_weight'];
    // Tạo dữ liệu mới từ form
    $updateData = [
        'updatedAt' => $update_time,
        'customer' => $customer,
        'export_weight' => (float)$export_weight,
        'lost_weight' => (float)$lost_weight,
        'images' => !empty($imageLinks) ? $imageLinks : $exportData['images'], // Thêm đường dẫn hình ảnh
    ];

    // Ghi dữ liệu vào file JSON
    updateJsonFileById('../' . EXPORT_GOODS_JSON_PATH, $export_id, $updateData);

    // // // lấy số lượng hàng hóa hiện có
    // // $remaining_weight_in_inventory = getWeightByItemSupplierPrice($item, $supplier, (int)$unit_price, '../'.INVENTORY_JSON_PATH);

    // ======= Cập nhật số lượng hàng hóa trong kho
    $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
    if (checkGoodsExist($item, $supplier, (int)$unit_price, $inventoryData)) {
        // cộng thêm số lượng hàng hóa mới nhập
        $inventoryData = updateInventory($item, $supplier, (float)$export_weight_old, (int)$unit_price, $inventoryData, 'import');
        // trừ đi số lượng hàng hóa đã nhập trước đó
        $inventoryData = updateInventory($item, $supplier, (float)$export_weight, (int)$unit_price, $inventoryData, 'export');
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin hàng hóa trong kho!']);
        exit();
    }
    writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);

    $newUpdateData = getDataById($export_id, '../' . EXPORT_GOODS_JSON_PATH);

    // ======= cập nhật thông tin nhập hàng vào import_export_inventory.json
    $current_date = date('d-m-Y');
    updateImportExportGoodsInInventory($inventory_id, $export_id, $current_date, $newUpdateData, '../' . IMPORT_EXPORT_INVENTORY_JSON_PATH, 'export');


    echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin xuất hàng thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
