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

    // Lấy dữ liệu từ form
    $supplier = isset($_POST['supplier']) ? $_POST['supplier'] : '';
    $item = isset($_POST['item']) ? $_POST['item'] : '';
    $unit_price = isset($_POST['unit_price']) ? $_POST['unit_price'] : 0;
    $customer = isset($_POST['customer']) ? $_POST['customer'] : '';
    $export_weight = isset($_POST['export_weight']) ? $_POST['export_weight'] : 0;
    $lost_weight = isset($_POST['lost_weight']) ? $_POST['lost_weight'] : 0;
    $time = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại

    // Xử lý upload hình ảnh
    $imageLinks = [];
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && $_FILES['images']['name'][0] !== '') {
        $imageLinks = uploadImages($_FILES, '../' . IMAGE_EXPORT_DIR);
    }

    // Đọc dữ liệu hiện có
    $data = readJsonFile('../' . EXPORT_GOODS_JSON_PATH);

    // Lấy ID cuối cùng và tăng thêm 1
    $lastId = end($data)['index'] ?? 0;

    // Tạo dữ liệu mới từ form
    $newData = [
        'id' => uniqid('export_'), // ID duy nhất
        'index' => $lastId + 1,
        'createdAt' => $time,
        'supplier' => $supplier,
        'unit_price' => (float)$unit_price,
        'item' => $item,
        'customer' => $customer,
        'export_weight' => (float)$export_weight,
        'lost_weight' => (float)$lost_weight,
        'images' => $imageLinks
    ];

    $total_weight = (float)$export_weight + (float)$lost_weight;

    // lấy số lượng hàng hóa hiện có
    $remaining_weight_in_inventory = getWeightByItemSupplierPrice($item, $supplier, (int)$unit_price, '../' . INVENTORY_JSON_PATH);

    // kiểm tra số lượng xuất ra có vuột qua số lượng còn trong kho không
    if ($total_weight > $remaining_weight_in_inventory) {
        echo json_encode(['success' => false, 'message' => 'Số lượng hàng hóa xuất ra lớn hơn số lượng còn trong kho!']);
        exit();
    }

    // Thêm dữ liệu mới vào danh sách
    $data[] = $newData;

    // Ghi dữ liệu vào file JSON
    writeJsonFile('../' . EXPORT_GOODS_JSON_PATH, $data);

    // ======= Cập nhật số lượng hàng hóa trong kho
    $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
    $inventoryData = updateInventory($item, $supplier, $total_weight, (int)$unit_price, $inventoryData, 'export');
    writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);

    // ======= Lưu thông tin xuất hàng vào import_export_inventory.json
    $current_date = date('d-m-Y');
    saveImportExportGoodsInInventory($supplier, $item, (float)$remaining_weight_in_inventory, (int)$unit_price, $current_date, $newData, '../' . IMPORT_EXPORT_INVENTORY_JSON_PATH, 'export');

    echo json_encode(['success' => true, 'message' => 'Đã xuất hàng thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}

// 1. Cập nhật số lượng hàng hóa trong kho
// 2. luu tru thong tin hang hoa ban đầu, nhập và xuất trong ngày
