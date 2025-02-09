<?php

// Định nghĩa đường dẫn file và thư mục
define('GOODS_JSON_PATH', '../database/goods_inventory.json');
define('IMAGE_DIR', '../database/image_import');
define('GOODS_CSV_PATH', '../database/database/goods.csv');
define('SUPPLIERS_CSV_PATH', '../database/database/suppliers.csv');
define('IMAGE_DIR_LINK', 'database/image_import');


function getSuppliersList($filePath = SUPPLIERS_CSV_PATH)
{
  $suppliers = [];
  if (($handle = fopen($filePath, "r")) !== false) {
    $isFirstRow = true;
    while (($row = fgetcsv($handle)) !== false) {
      if ($isFirstRow) {
        $isFirstRow = false;
        continue;
      }
      $suppliers[] = $row[1]; // Cột thứ 2
    }
    fclose($handle);
  }
  return $suppliers;
}

function getGoodsList($filePath = GOODS_CSV_PATH) {
  $goods = [];
  if (($handle = fopen($filePath, "r")) !== false) {
      $isFirstRow = true;
      while (($row = fgetcsv($handle)) !== false) {
          if ($isFirstRow) {
              $isFirstRow = false;
              continue;
          }
          $goods[] = $row[1] . " - " . $row[2]; // Kết hợp cột thứ 2 và cột thứ 3
      }
      fclose($handle);
  }
  return $goods;
}

// Tạo thư mục nếu chưa tồn tại
function createDirectoryIfNotExists($dirPath)
{
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0755, true);
    }
}

// Xử lý upload hình ảnh
function uploadImages($files, $imageDir)
{
    createDirectoryIfNotExists($imageDir);
    $imageLinks = [];

    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $formatDate = $year . '_' . $month . '_' . $day;

    if (isset($files['images']) && !empty($files['images']['name'][0])) {
        foreach ($files['images']['name'] as $key => $imageName) {
            $tmpName = $files['images']['tmp_name'][$key];
            $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
            $uniqueName = uniqid('image_'). '_' . $formatDate . '.' . $imageExt;
            $targetPath = $imageDir . '/' . $uniqueName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $imageLinks[] = IMAGE_DIR_LINK . '/' . $uniqueName;
            }
        }
    }

    return $imageLinks;
}

// Đọc dữ liệu từ JSON file
function readJsonFile($filePath)
{
    if (!file_exists($filePath)) {
        return [];
    }

    $jsonContent = file_get_contents($filePath);
    $data = json_decode($jsonContent, true);

    return is_array($data) ? $data : [];
}

// Ghi dữ liệu vào JSON file
function writeJsonFile($filePath, $data)
{
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
