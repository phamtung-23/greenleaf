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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Đường dẫn file JSON
    $filePath = '../' . GOODS_JSON_PATH;
    // Đường dẫn thư mục lưu hình ảnh
    $imageDir = '../' . IMAGE_DIR;

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
    }

    $import_id = isset($_GET['import_id']) ? $_GET['import_id'] : '';
    $inventory_id = isset($_GET['inventory_id']) ? $_GET['inventory_id'] : '';
   

    // Đọc dữ liệu hiện có
    $dataImport = getDataById($import_id, '../' . GOODS_JSON_PATH);

    $item = $dataImport['item'];
    $supplier = $dataImport['supplier'];
    $unit_price = $dataImport['unit_price'];
    $remaining_weight = $dataImport['remaining_weight'];

    // ======= Cập nhật số lượng hàng hóa trong kho
    $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
    if (checkGoodsExist($item, $supplier, (int)$unit_price, $inventoryData)) {
        // trừ đi số lượng hàng hóa đã nhập trước đó
        $inventoryData = updateInventory($item, $supplier, (float)$remaining_weight, (int)$unit_price, $inventoryData, 'export');
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin hàng hóa trong kho!']);
        exit();
    }
    writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);

    // ======= Xóa thông tin nhâp hàng theo id trong  import_goods.json
    deleteJsonFileById('../' . GOODS_JSON_PATH, $import_id);

    // ======= Xóa thông tin nhập hàng vào import_export_inventory.json
    deleteImportExportGoodsInInventory($inventory_id, $import_id, '../' . IMPORT_EXPORT_INVENTORY_JSON_PATH, 'import');


    echo json_encode(['success' => true, 'message' => 'Xóa thông tin nhập hàng thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
