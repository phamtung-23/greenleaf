<?php

// Định nghĩa đường dẫn file và thư mục
define('GOODS_JSON_PATH', '../database/import_goods.json');
define('IMAGE_DIR', '../database/image_import');
define('GOODS_CSV_PATH', '../database/database/goods.csv');
define('SUPPLIERS_CSV_PATH', '../database/database/suppliers.csv');
define('IMAGE_DIR_LINK', 'database/image_import');
define('SUPPLIERS_JSON_LINK', '../database/database/suppliers.json');
define('GOODS_JSON_LINK', '../database/database/goods.json');
define('CUSTOMER_JSON_LINK', '../database/database/customers.json');
define('EXPORT_GOODS_JSON_PATH', '../database/export_goods.json');
define('IMAGE_EXPORT_DIR', '../database/image_export');
define('INVENTORY_JSON_PATH', '../database/inventory.json');
define('IMPORT_EXPORT_INVENTORY_JSON_PATH', '../database/import_export_inventory.json');



// Hàm ghi log
function logEntry($message)
{
    $logFile = '../../logs/debug_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    // get full path
    $filePath = $_SERVER['PHP_SELF'];
    $logMessage = "[$timestamp] $filePath: $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

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

function getGoodsList($filePath = GOODS_CSV_PATH)
{
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
            $uniqueName = uniqid('image_') . '_' . $formatDate . '.' . $imageExt;
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

// cập nhật dữ liệu vào file json theo id
function updateJsonFileById($filePath, $id, $newData)
{
    $data = readJsonFile($filePath);

    foreach ($data as $key => $item) {
        if ($item['id'] === $id) {
            $data[$key] = array_merge($item, $newData);
            break;
        }
    }

    writeJsonFile($filePath, $data);
}

// Xóa dữ liệu trong file json theo id
function deleteJsonFileById($filePath, $id)
{
    $data = readJsonFile($filePath);

    foreach ($data as $key => $item) {
        if ($item['id'] === $id) {
            unset($data[$key]);
            break;
        }
    }

    writeJsonFile($filePath, $data);
}

// kiểm tra hàng hóa có tồn tại trong kho không
function checkGoodsExist($goods, $supplier, $price, $inventoryData)
{
    foreach ($inventoryData as $item) {
        if ($item['item'] === $goods && $item['supplier'] === $supplier && $item['unit_price'] === $price) {
            return true;
        }
    }
    return false;
}

// Thêm hàng hóa vào kho
function addInventory($goods, $supplier, $weight, $price, $inventoryData)
{
    $inventoryData[] = [
        'item' => $goods,
        'supplier' => $supplier,
        'weight' => $weight,
        'unit_price' => $price,
    ];
    return $inventoryData;
}

// cập nhật hàng hóa trong kho
function updateInventory($goods, $supplier, $weight, $price, $inventoryData, $type)
{
    foreach ($inventoryData as $key => $item) {
        if ($item['item'] === $goods && $item['supplier'] === $supplier && $item['unit_price'] === $price) {
            if ($type === 'import') {
                $inventoryData[$key]['weight'] += $weight;
            } else {
                $inventoryData[$key]['weight'] -= $weight;
            }
            break;
        }
    }
    return $inventoryData;
}

// lấy thông tin mặt hàng theo id
function getGoodsById($id, $filePath = '../' . GOODS_JSON_LINK)
{
    $data = readJsonFile($filePath);
    foreach ($data as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }
    return [];
}

// lấy thông tin số lượng hàng hóa trong kho, tiền tương ứng và thôn tin hàng hóa theo supplier id
function getGoodsBySupplier($supplierId, $filePath = GOODS_JSON_PATH)
{
    $data = readJsonFile($filePath);
    $result = [];
    foreach ($data as $item) {
        if ($item['supplier'] === $supplierId) {
            $goodsInfo = getGoodsById($item['item']);
            $item['goodsInfo'] = $goodsInfo;
            $result[] = $item;
        }
    }
    return $result;
}

// Lấy danh sách thông tin giá và số lượng hàng hóa theo item id và supplier id
function getWeightPriceByItem($itemId, $supplierId, $filePath = GOODS_JSON_PATH)
{
    $data = readJsonFile($filePath);
    $result = [];
    foreach ($data as $item) {
        if ($item['item'] === $itemId && $item['supplier'] === $supplierId) {
            $result[] = [
                'weight' => $item['weight'],
                'unit_price' => $item['unit_price'],
            ];
        }
    }
    return $result;
}

// Lấy số lượng hàng hóa theo item id và supplier id và giá
function getWeightByItemSupplierPrice($itemId, $supplierId, $price, $filePath)
{
    $data = readJsonFile($filePath);
    foreach ($data as $item) {
        if ($item['item'] === $itemId && $item['supplier'] === $supplierId && $item['unit_price'] === $price) {
            return $item['weight'];
        }
    }
    return 0;
}

// lưu thông tin nhập hàng vào file json theo item id và supplier id và giá và theo ngày
function saveImportExportGoodsInInventory($supplier, $item, $remaining_weight, $unit_price, $current_date, $importExportData, $filePath, $type)
{
    $inventoryData = readJsonFile($filePath);
    if (checkImportExportInventoryExist($item, $supplier, $unit_price, $current_date, $inventoryData)) {
        foreach ($inventoryData as $key => $data) {
            if ($data['item'] === $item && $data['supplier'] === $supplier && $data['unit_price'] === $unit_price && $data['createdAt'] === $current_date) {
                if ($type === 'import') {
                    // thêm thông tin import vào danh sách import_list
                    $data['import_list'][] = $importExportData;
                    $inventoryData[$key] = $data;
                    writeJsonFile($filePath, $inventoryData);
                    break;
                } else {
                    // thêm thông tin export vào danh sách export_list
                    $data['export_list'][] = $importExportData;
                    $inventoryData[$key] = $data;
                    writeJsonFile($filePath, $inventoryData);
                    break;
                }
            }
        }
    } else {
        if ($type === 'import') {
            $inventoryData[] = [
                'id' => uniqid('import_export_'),
                'item' => $item,
                'supplier' => $supplier,
                'remaining_weight' => $remaining_weight,
                'unit_price' => $unit_price,
                'createdAt' => $current_date,
                'import_list' => [$importExportData],
            ];
            writeJsonFile($filePath, $inventoryData);
        } else {
            $inventoryData[] = [
                'id' => uniqid('import_export_'),
                'item' => $item,
                'supplier' => $supplier,
                'remaining_weight' => $remaining_weight,
                'unit_price' => $unit_price,
                'createdAt' => $current_date,
                'export_list' => [$importExportData],
            ];
            writeJsonFile($filePath, $inventoryData);
        }
    }
}

function checkImportExportInventoryExist($goods, $supplier, $price, $current_date, $inventoryData)
{
    foreach ($inventoryData as $item) {
        if ($item['item'] === $goods && $item['supplier'] === $supplier && $item['unit_price'] === $price && $item['createdAt'] === $current_date) {
            return true;
        }
    }
    return false;
}

// tính toán thông tin cho report inventory
function calculateInventoryReport($inventoryData)
{

    // lấy thông tin NCC
    $supplierData = readJsonFile(SUPPLIERS_JSON_LINK);
    $nameNCC = '';
    $codeNCC = '';
    foreach ($supplierData as $supplier) {
        if ($supplier['id'] === $inventoryData['supplier']) {
            $nameNCC = $supplier['nameNCC'];
            $codeNCC = $supplier['codeNCC'];
            break;
        }
    }
    // lấy thông tin mặt hàng
    $goodsData = readJsonFile(GOODS_JSON_LINK);
    $nameGoods = '';
    $codeGoods = '';
    foreach ($goodsData as $goods) {
        if ($goods['id'] === $inventoryData['item']) {
            $nameGoods = $goods['nameGoods'];
            $codeGoods = $goods['codeGoods'];
            break;
        }
    }


    $SLN = 0;
    $xa = 0;
    $SLX = 0;
    $haoHut = 0;
    $customerData = [];
    if (isset($inventoryData['import_list'])) {
        foreach ($inventoryData['import_list'] as $import) {
            $SLN += $import['weight'];
            $xa += $import['disposed_weight'];
        }
    }
    if (isset($inventoryData['export_list'])) {
        foreach ($inventoryData['export_list'] as $export) {
            $SLX += $export['export_weight'];
            $haoHut += $export['lost_weight'];
            // kiểm tra xem khách hàng đã có trong danh sách chưa
            if (!in_array($export['customer'], $customerData)) {
                $customerData[] = [
                    'id' => $export['customer'],
                    'weight' => $export['export_weight'],
                ];
            } else {
                // câp nhật số lượng hàng hóa đã xuất ra của khách hàng
                foreach ($customerData as $key => $customer) {
                    if ($customer['id'] === $export['customer']) {
                        $customerData[$key]['weight'] += $export['export_weight'];
                        break;
                    }
                }
            }
        }
    }

    $thanhTien = ($SLN + $inventoryData['remaining_weight']) * $inventoryData['unit_price'];
    $CPHaoHut = $haoHut * $inventoryData['unit_price'];
    $conLai = $SLN + $inventoryData['remaining_weight'] - $xa;
    $tonCuoi = $conLai - $SLX - $haoHut;
    $CPTonCuoi = $tonCuoi * $inventoryData['unit_price'];

    return [
        'DVT' => 'kg',
        'tonDau' => $inventoryData['remaining_weight'],
        'SLN' => $SLN,
        'xa' => $xa,
        'conLai' => $conLai,
        'unit_price' => $inventoryData['unit_price'],
        'thanhTien' => $thanhTien,
        'SLX' => $SLX,
        'haoHut' => $haoHut,
        'CPHaoHut' => $CPHaoHut,
        'tonCuoi' => $tonCuoi,
        'CPTonCuoi' => $CPTonCuoi,
        'nameNCC' => $nameNCC,
        'codeNCC' => $codeNCC,
        'nameGoods' => $nameGoods,
        'codeGoods' => $codeGoods,
        'createdAt' => $inventoryData['createdAt'],
        'customerData' => $customerData,
    ];
}

function checkCustomerDataById($customerId, $customerData)
{
    foreach ($customerData as $customer) {
        if ($customer['id'] === $customerId) {
            return [true, $customer['weight']];
        }
    }
    return [false, 0];
}

function calculateInventoryReportById($inventoryId, $inventoryData)
{

    foreach ($inventoryData as $item) {
        if ($item['id'] === $inventoryId) {
            // lấy thông tin NCC
            $supplierData = readJsonFile(SUPPLIERS_JSON_LINK);
            $nameNCC = '';
            $codeNCC = '';
            foreach ($supplierData as $supplier) {
                if ($supplier['id'] === $item['supplier']) {
                    $nameNCC = $supplier['nameNCC'];
                    $codeNCC = $supplier['codeNCC'];
                    break;
                }
            }
            // lấy thông tin mặt hàng
            $goodsData = readJsonFile(GOODS_JSON_LINK);
            $nameGoods = '';
            $codeGoods = '';
            foreach ($goodsData as $goods) {
                if ($goods['id'] === $item['item']) {
                    $nameGoods = $goods['nameGoods'];
                    $codeGoods = $goods['codeGoods'];
                    break;
                }
            }


            $SLN = 0;
            $xa = 0;
            $SLX = 0;
            $haoHut = 0;
            $customerData = [];
            if (isset($item['import_list'])) {
                foreach ($item['import_list'] as $import) {
                    $SLN += $import['weight'];
                    $xa += $import['disposed_weight'];
                }
            }
            if (isset($item['export_list'])) {
                foreach ($item['export_list'] as $export) {
                    $SLX += $export['export_weight'];
                    $haoHut += $export['lost_weight'];
                    // kiểm tra xem khách hàng đã có trong danh sách chưa
                    if (!in_array($export['customer'], $customerData)) {
                        $customerFromDB = readJsonFile(CUSTOMER_JSON_LINK);
                        $customerName = '';
                        foreach ($customerFromDB as $customer) {
                            if ($customer['id'] === $export['customer']) {
                                $customerName = $customer['nameCustomer'];
                                break;
                            }
                        }
                        $customerData[] = [
                            'id' => $export['customer'],
                            'weight' => $export['export_weight'],
                            'name' => $customerName,
                        ];
                    } else {
                        // câp nhật số lượng hàng hóa đã xuất ra của khách hàng
                        foreach ($customerData as $key => $customer) {
                            if ($customer['id'] === $export['customer']) {
                                $customerData[$key]['weight'] += $export['export_weight'];
                                break;
                            }
                        }
                    }
                }
            }

            $thanhTien = ($SLN + $item['remaining_weight']) * $item['unit_price'];
            $CPHaoHut = $haoHut * $item['unit_price'];
            $conLai = $SLN + $item['remaining_weight'] - $xa;
            $tonCuoi = $conLai - $SLX - $haoHut;
            $CPTonCuoi = $tonCuoi * $item['unit_price'];

            return [
                'DVT' => 'kg',
                'tonDau' => $item['remaining_weight'],
                'SLN' => $SLN,
                'xa' => $xa,
                'conLai' => $conLai,
                'unit_price' => $item['unit_price'],
                'thanhTien' => $thanhTien,
                'SLX' => $SLX,
                'haoHut' => $haoHut,
                'CPHaoHut' => $CPHaoHut,
                'tonCuoi' => $tonCuoi,
                'CPTonCuoi' => $CPTonCuoi,
                'nameNCC' => $nameNCC,
                'codeNCC' => $codeNCC,
                'nameGoods' => $nameGoods,
                'codeGoods' => $codeGoods,
                'createdAt' => $item['createdAt'],
                'customerData' => $customerData,
                'import_list' => $item['import_list'] ?? [],
                'export_list' => $item['export_list'] ?? [],
            ];
        }
    }
}
