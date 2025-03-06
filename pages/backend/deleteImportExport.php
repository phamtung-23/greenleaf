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

  $import_Export_id = isset($_POST['id']) ? $_POST['id'] : '';

  // // Đọc dữ liệu hiện có
  $dataImportExport = getDataById($import_Export_id, '../' . IMPORT_EXPORT_INVENTORY_JSON_PATH);
  // $dataImport = getDataById($import_id, '../' . GOODS_JSON_PATH);

  $item = $dataImportExport['item'];
  $supplier = $dataImportExport['supplier'];
  $unit_price = $dataImportExport['unit_price'];
  $import_list = $dataImportExport['import_list'] ?? [];
  $export_list = $dataImportExport['export_list'] ?? [];

  // ======= xóa thông tin nhập hàng theo id trong  import_goods.json
  if (count($import_list) > 0) {
    foreach ($import_list as $import_id) {
      // ======= Cập nhật số lượng hàng hóa trong kho
      $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
      if (checkGoodsExist($item, $supplier, (int)$unit_price, $inventoryData)) {
        // trừ đi số lượng hàng hóa đã nhập trước đó
        $inventoryData = updateInventory($item, $supplier, (float)$import_id['remaining_weight'], (int)$unit_price, $inventoryData, 'export');
      } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin hàng hóa trong kho!']);
        exit();
      }
      writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);
      deleteJsonFileById('../' . GOODS_JSON_PATH, $import_id['id']);
    }
  }
  // ======= xóa thông tin xuất hàng theo id trong  export_goods.json
  if (count($export_list) > 0) {
    foreach ($export_list as $export_id) {
      $inventoryData = readJsonFile('../' . INVENTORY_JSON_PATH);
      if (checkGoodsExist($item, $supplier, (int)$unit_price, $inventoryData)) {
        // trừ đi số lượng hàng hóa đã nhập trước đó
        $inventoryData = updateInventory($item, $supplier, (float)$export_id['export_weight'], (int)$unit_price, $inventoryData, 'import');
      } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin hàng hóa trong kho!']);
        exit();
      }
      writeJsonFile('../' . INVENTORY_JSON_PATH, $inventoryData);
      deleteJsonFileById('../' . EXPORT_GOODS_JSON_PATH, $export_id['id']);
    }
  }
  // ======= xóa thông tin nhập hàng vào import_export_inventory.json
  deleteJsonFileById('../' . IMPORT_EXPORT_INVENTORY_JSON_PATH, $import_Export_id);

  echo json_encode(['success' => true, 'message' => 'Xóa phiếu theo dõi thành công!']);
} else {
  echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
}
