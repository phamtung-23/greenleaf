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
    $filePath = '../' . EXPORT_GOODS_JSON_PATH;
    // Đường dẫn thư mục lưu hình ảnh
    $imageDir = '../' . IMAGE_EXPORT_DIR;

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
    }

    $export_id = isset($_GET['export_id']) ? $_GET['export_id'] : '';
    $inventory_id = isset($_GET['inventory_id']) ? $_GET['inventory_id'] : '';
   

    // Đọc dữ liệu hiện có
    $dataExport = getDataById($export_id, '../' . EXPORT_GOODS_JSON_PATH);

    $item = $dataExport['item'];
    $supplier = $dataExport['supplier'];
    $unit_price = $dataExport['unit_price'];
    $export_weight = $dataExport['export_weight'];

    // ======= Cập nhật số lượng hàng hóa trong kho
    $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
    if (checkGoodsExist($item, $supplier, (int)$unit_price, $inventoryData)) {
        // trừ đi số lượng hàng hóa đã nhập trước đó
        $inventoryData = updateInventory($item, $supplier, (float)$export_weight, (int)$unit_price, $inventoryData, 'import');
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin hàng hóa trong kho!']);
        exit();
    }
    writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);

    // ======= Xóa thông tin xuất hàng theo id trong  export_goods.json
    deleteJsonFileById('../' . EXPORT_GOODS_JSON_PATH, $export_id);

    // ======= Xóa thông tin xuất hàng vào import_export_inventory.json
    deleteImportExportGoodsInInventory($inventory_id, $export_id, '../' . IMPORT_EXPORT_INVENTORY_JSON_PATH, 'export');


    echo json_encode(['success' => true, 'message' => 'Xóa thông tin xuất hàng thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
