<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_name("greenleaf");
session_start();

if (!isset($_SESSION['email'])) {
    echo "<script>alert('You are not authorized to view this page. Please log in.'); window.location.href = '../index.php';</script>";
    exit();
}

// Lấy email và tên đầy đủ từ session
$email = $_SESSION['email'];
$fullname = $_SESSION['full_name'];

include '../helper/general.php';

// Đường dẫn file JSON
$filePath = IMPORT_EXPORT_INVENTORY_JSON_PATH;
$dataList = readJsonFile($filePath);

$customerData = readJsonFile(CUSTOMER_JSON_LINK);

// Đọc danh sách nhà cung cấp từ suppliers.csv (bỏ dòng đầu tiên)
$suppliers = readJsonFile(SUPPLIERS_JSON_LINK);

// Đọc danh sách mặt hàng từ goods.csv (bỏ dòng đầu tiên)
$goods = readJsonFile(GOODS_JSON_LINK);

$customerList = readJsonFile(CUSTOMER_JSON_LINK);

// get id from url
$inventoryId = $_GET['id'];
$inventoryData = calculateInventoryReportById($inventoryId, $dataList);

echo "<script>
    const inventoryData = " . json_encode($inventoryData) . ";
</script>";

?>

<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <title>Green Leaf</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <main>
        <nav class="navbar navbar-expand-md bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <h2><img width="100" src="../images/new_logo.png" alt="Company Logo"></h2>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarScroll">
                    <ul class="navbar-nav me-auto my-2 my-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="import.php">Nhập hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="export.php">Xuất hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="management_ncc.php">Nhà cung cấp</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="management_goods.php">Hàng hóa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="management_customer.php">Khách hàng</a>
                        </li>
                    </ul>
                    <form action="../logout.php" class="d-flex" role="search">
                        <button class="btn btn-outline-danger" type="submit">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </nav>
        <div class="container pt-3">
            <h3>Thông tin chi tiết</h3>
            <p>Welcome, <?php echo $fullname; ?>!</p>
        </div>
        <div class="container" style="margin-bottom: 80px !important;">
            <div class="row">
                <div class="col-md-6 mt-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            Thông tin chung
                        </div>
                        <div class="card-body">
                            <p class="card-text">Ngày: <span class="badge rounded-pill text-bg-success"><?php echo $inventoryData['createdAt']; ?></span></p>
                            <p class="card-text">Mã mặt hàng: <span class="fw-bold"><?php echo $inventoryData['codeGoods']; ?></span></p>
                            <p class="card-text">Tên mặt hàng: <span class="fw-bold"><?php echo $inventoryData['nameGoods']; ?></span></p>
                            <p class="card-text">Mã NCC: <span class="fw-bold"><?php echo $inventoryData['codeNCC']; ?></span></p>
                            <p class="card-text">Tên NCC: <span class="fw-bold"><?php echo $inventoryData['nameNCC']; ?></span></p>
                            <p class="card-text">Đơn vị tính: <span class="fw-bold"><?php echo $inventoryData['DVT']; ?></span></p>
                            <p class="card-text">Tồn đầu: <span class="fw-bold"><?php echo $inventoryData['tonDau']; ?></span></p>
                            <p class="card-text">Số lượng nhập: <span class="fw-bold"><?php echo $inventoryData['SLN']; ?></span></p>
                            <p class="card-text">Xả: <span class="fw-bold"><?php echo $inventoryData['xa']; ?></span></p>
                            <p class="card-text">Còn lại: <span class="fw-bold"><?php echo $inventoryData['conLai']; ?></span></p>
                            <p class="card-text">Đơn giá: <span class="fw-bold"><?php echo number_format($inventoryData['unit_price'], 0, ',', '.'); ?> VND</span></p>
                            <p class="card-text">Thành tiền: <span class="fw-bold"><?php echo number_format($inventoryData['thanhTien'], 0, ',', '.'); ?> VND</span></p>
                            <p class="card-text">Số lượng xuất: <span class="fw-bold"><?php echo $inventoryData['SLX']; ?></span></p>
                            <p class="card-text">Hao hụt: <span class="fw-bold"><?php echo $inventoryData['haoHut']; ?></span></p>
                            <p class="card-text">Chi phí hao hụt: <span class="fw-bold"><?php echo number_format($inventoryData['CPHaoHut'], 0, ',', '.'); ?> VND</span></p>
                            <p class="card-text">Tồn cuối: <span class="fw-bold"><?php echo $inventoryData['tonCuoi']; ?></span></p>
                            <p class="card-text">Chi phí tồn: <span class="fw-bold"><?php echo number_format($inventoryData['CPTonCuoi'], 0, ',', '.'); ?> VND</span></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            Thông tin khách hàng
                        </div>
                        <div class="card-body">
                            <?php
                            if (empty($inventoryData['customerData'])) {
                                echo "<p class='card-text'>Không có dữ liệu.</p>";
                            } else {
                                foreach ($inventoryData['customerData'] as $customerItem) {
                                    echo "<div class='border border-1 p-2 mb-1 rounded'>";
                                    echo "<p class='card-text'>Tên khách hàng: <span class='fw-bold'>" . $customerItem['name'] . "</span></p>";
                                    echo "<p class='card-text'>Số lượng xuất: <span class='fw-bold'>" . $customerItem['weight'] . "</span></p>";
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            Danh sách phiếu nhập hàng
                        </div>
                        <div class="card-body">
                            <?php
                            if (empty($inventoryData['import_list'])) {
                                echo "<p class='card-text'>Không có dữ liệu.</p>";
                            } else {
                                foreach ($inventoryData['import_list'] as $importItem) {
                                    echo "<div class='border border-1 p-2 mb-1 rounded'>";
                                    echo "<p class='card-text'>Thời gian: <span class='badge rounded-pill text-bg-success'>" . $importItem['createdAt'] . "</span></p>";
                                    echo "<p class='card-text'>Số lượng nhập: <span class='fw-bold'>" . $importItem['weight'] . "</span></p>";
                                    echo "<p class='card-text'>Số lượng xả: <span class='fw-bold'>" . $importItem['disposed_weight'] . "</span></p>";
                                    echo "<div class='w-100 text-center'>
                                    <button class='btn bg-primary-subtle' style='min-width: 100px;'
                                    data-bs-id='{$importItem['id']}'  
                                    data-bs-toggle='modal' data-bs-target='#updateImportModal'>sửa</button>
                                    <button class='btn btn-danger' style='min-width: 100px;' data-bs-toggle='modal' data-bs-target='#deleteModal' data-bs-id='{$importItem['id']}'>xóa</button>
                                    </div>";
                                    // echo "<p class='card-text'>Tổng tiền: <span class='fw-bold'>" . number_format($importItem['total_price'], 0, ',', '.') . " VND</span></p>";
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            Danh sách phiếu xuất hàng
                        </div>
                        <div class="card-body">
                            <?php
                            if (empty($inventoryData['export_list'])) {
                                echo "<p class='card-text'>Không có dữ liệu.</p>";
                            } else {
                                foreach ($inventoryData['export_list'] as $exportItem) {
                                    echo "<div class='border border-1 p-2 mb-1 rounded'>";
                                    echo "<p class='card-text'>Thời gian: <span class='badge rounded-pill text-bg-warning'>" . $exportItem['createdAt'] . "</span></p>";
                                    echo "<p class='card-text'>Số lượng xuất: <span class='fw-bold'>" . $exportItem['export_weight'] . "</span></p>";
                                    echo "<p class='card-text'>Số lượng hao hụt: <span class='fw-bold'>" . $exportItem['lost_weight'] . "</span></p>";
                                    echo "<div class='w-100 text-center'>
                                    <button class='btn bg-primary-subtle' style='min-width: 100px;'
                                    data-bs-id='{$exportItem['id']}'  
                                    data-bs-toggle='modal' data-bs-target='#updateExportModal'>sửa</button>
                                    <button class='btn btn-danger' style='min-width: 100px;' data-bs-toggle='modal' data-bs-target='#deleteExportModal' data-bs-id='{$exportItem['id']}'>xóa</button>
                                    </div>";
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit import -->
        <div class="modal fade" id="updateImportModal" tabindex="-1" aria-labelledby="updateImportModalLabel" aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="updateImportModalLabel">Chỉnh sửa thông tin nhập hàng</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="row g-3 needs-validation" novalidate>
                            <input type="hidden" id="import_id">
                            <div class="col-md-6">
                                <label for="supplier" class="form-label">Nhà cung cấp:</label>
                                <select class="form-select" id="supplier" name="supplier" required disabled>
                                    <option selected disabled value="">Chọn nhà cung cấp</option>
                                    <?php
                                    foreach ($suppliers as $supplier) {
                                        echo "<option value='{$supplier['id']}'>{$supplier['nameNCC']} ({$supplier['codeNCC']})</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    Vui lòng chọn nhà cung cấp.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="item" class="form-label">Mặt hàng:</label>
                                <select class="form-select" id="item" name="item" required disabled>
                                    <option selected disabled value="">Chọn mặt hàng</option>
                                    <?php
                                    foreach ($goods as $good) {
                                        echo "<option value='{$good['id']}'>{$good['codeGoods']} - {$good['nameGoods']}</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    Vui lòng chọn mặt hàng.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="weight" class="form-label">Khối lượng (kg):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">kg</span>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.01" placeholder="Nhập khối lượng" aria-describedby="inputGroupPrepend" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập khối lượng.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="disposed_weight" class="form-label">Xả (kg):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">kg</span>
                                    <input type="number" class="form-control" id="disposed_weight" name="disposed_weight" step="0.01" placeholder="Nhập khối lượng đã xả" aria-describedby="inputGroupPrepend" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập khối lượng xả.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="remaining_weight" class="form-label">Khối lượng còn lại (kg):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">kg</span>
                                    <input type="number" class="form-control" id="remaining_weight" name="remaining_weight" step="0.01" placeholder="Khối lượng còn lại" aria-describedby="inputGroupPrepend" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập khối lượng còn lại.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="unit_price" class="form-label">Đơn giá (VNĐ):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">VNĐ</span>
                                    <input type="text" class="form-control" id="unit_price" name="unit_price" placeholder="Nhập đơn giá" aria-describedby="inputGroupPrepend" required disabled>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập đơn giá.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="total_price" class="form-label">Thành tiền (VNĐ):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">VNĐ</span>
                                    <input type="text" class="form-control" id="total_price" name="total_price" placeholder="Thành tiền" aria-describedby="inputGroupPrepend" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập thành tiền.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="images" class="form-label">Upload hình ảnh:</label>
                                <input class="form-control" type="file" id="images" name="images[]" multiple accept="image/*" required>
                                <div class="invalid-feedback">
                                    Vui lòng chọn hình ảnh.
                                </div>
                            </div>
                            <!-- display image -->
                            <div class="col-md-12">
                                <div class="row" id="image-preview-import">
                                    <div class="col-md-4">
                                        <img src="" alt="" class="img-thumbnail" style="width: 100px;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" id="btn-update-import">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Export -->
        <div class="modal fade" id="updateExportModal" tabindex="-1" aria-labelledby="updateExportModalLabel" aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="updateExportModalLabel">Chỉnh sửa thông tin xuất hàng</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formSubmit" class="row g-3 needs-validation" novalidate>
                            <!-- <div class="w-100 rounded border p-2 text-bg-secondary" role="alert">
                            Vui lòng nhập đúng nhà cung cấp và mặt hàng để xem thông tin trong kho!
                        </div> -->
                            <input type="hidden" id="export_id">
                            <div class="col-md-6">
                                <label for="remaining_weight_inventory" class="form-label">Khối lượng trong kho (kg):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">kg</span>
                                    <input type="number" class="form-control" id="remaining_weight_inventory" name="remaining_weight_inventory" step="0.01" placeholder="Khối lượng trong kho" aria-describedby="inputGroupPrepend" required disabled>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập khối lượng trong kho.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="customer" class="form-label">Khách hàng:</label>
                                <select class="form-select" id="customer" name="customer" required>
                                    <option selected disabled value="">Chọn khách hàng</option>
                                    <?php
                                    foreach ($customerList as $customer) {
                                        echo "<option value='{$customer['id']}'>{$customer['nameCustomer']}</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    Vui lòng chọn khách hàng.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="export_weight" class="form-label">khối lượng xuất (kg):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">kg</span>
                                    <input type="number" class="form-control" id="export_weight" name="export_weight" step="0.01" placeholder="Nhập khối lượng xuất" aria-describedby="inputGroupPrepend" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập khối lượng xuất.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="lost_weight" class="form-label">Hao hụt (kg):</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="inputGroupPrepend">kg</span>
                                    <input type="number" class="form-control" id="lost_weight" name="lost_weight" step="0.01" placeholder="Nhập khối lượng hao hụt" aria-describedby="inputGroupPrepend" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập khối lượng hao hụt.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="images" class="form-label">Upload hình ảnh:</label>
                                <input class="form-control" type="file" id="images" name="images[]" multiple accept="image/*" required>
                                <div class="invalid-feedback">
                                    Vui lòng chọn hình ảnh.
                                </div>
                            </div>
                            <!-- display image -->
                            <div class="col-md-12">
                                <div class="row" id="image-preview-export">
                                    <div class="col-md-4">
                                        <img src="" alt="" class="img-thumbnail" style="width: 100px;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" id="btn-update-export">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Delete import -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h1 class="modal-title fs-5" id="deleteModalLabel">Xóa thông tin</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa thông tin nhập hàng?</p>
                        <input type="hidden" id="deleteId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Delete import -->
        <div class="modal fade" id="deleteExportModal" tabindex="-1" aria-labelledby="deleteExportModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h1 class="modal-title fs-5" id="deleteExportModalLabel">Xóa thông tin</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa thông tin xuất hàng?</p>
                        <input type="hidden" id="deleteExportId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteExport">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer id="sticky-footer" class="flex-shrink-0 py-2 bg-dark text-white-50">
        <div class="container text-center">
            <small>© 2025 Phần mềm phát triển bởi PTTung 0359663439</small>
        </div>
    </footer>
    <script>

        // =================== IMPORT ==================
        // Open Edit Modal with existing import data
        const exampleModal = document.getElementById('updateImportModal')
        if (exampleModal) {
            exampleModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget
                // Extract info from data-bs-* attributes
                const id = button.getAttribute('data-bs-id')
                // If necessary, you could initiate an Ajax request here
                // and then do the updating in a callback.
                const importData = inventoryData.import_list.find(item => item.id == id)
                console.log(importData)

                // Update the modal's content.
                const importIdInput = document.getElementById('import_id')
                const supplierSelect = document.getElementById('supplier')
                const itemSelect = document.getElementById('item')
                const weightInput = document.getElementById('weight')
                const disposedWeightInput = document.getElementById('disposed_weight')
                const remainingWeightInput = document.getElementById('remaining_weight')
                const unitPriceInput = document.getElementById('unit_price')
                const totalPriceInput = document.getElementById('total_price')

                // Fill data to form
                importIdInput.value = id
                supplierSelect.value = importData.supplier
                itemSelect.value = importData.item
                weightInput.value = importData.weight
                disposedWeightInput.value = importData.disposed_weight
                remainingWeightInput.value = importData.remaining_weight
                unitPriceInput.value = formatCurrency(importData.unit_price)
                totalPriceInput.value = formatCurrency(importData.total_price)

                // Display image
                const imagePreview = document.getElementById('image-preview-import')
                imagePreview.innerHTML = ''
                importData.images.forEach(image => {
                    const imageElement = document.createElement('div')
                    imageElement.classList.add('col-md-4')
                    imageElement.innerHTML = `<img src="../${image}" alt="" class="img-thumbnail" style="width: 100%;">`
                    imagePreview.appendChild(imageElement)
                })
            })
        }

        // Open Delete Modal with existing import data
        const deleteModal = document.getElementById('deleteModal')
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget
                // Extract info from data-bs-* attributes
                const id = button.getAttribute('data-bs-id')

                // Update the modal's content.
                const deleteId = deleteModal.querySelector('#deleteId')
                deleteId.value = id
            })
        }

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
        // Hàm định dạng số tiền
        function formatCurrency(value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Hàm tính toán "Khối lượng còn lại" và "Thành tiền"
        function calculateValues() {
            const weightInput = document.getElementById('weight');
            const disposedWeightInput = document.getElementById('disposed_weight');
            const remainingWeightInput = document.getElementById('remaining_weight');
            const unitPriceInput = document.getElementById('unit_price');
            const totalPriceInput = document.getElementById('total_price');

            // Giá trị khối lượng và xả
            const weight = parseFloat(weightInput.value) || 0;
            const disposedWeight = parseFloat(disposedWeightInput.value) || 0;
            const remainingWeight = weight - disposedWeight;

            // Hiển thị khối lượng còn lại
            remainingWeightInput.value = remainingWeight >= 0 ? remainingWeight.toFixed(2) : 0;

            // Giá trị đơn giá và thành tiền
            const unitPrice = parseFloat(unitPriceInput.value.replace(/\./g, '')) || 0; // Bỏ dấu chấm trước khi parse
            const totalPrice = remainingWeight * unitPrice;

            // Hiển thị đơn giá và thành tiền đã format
            unitPriceInput.value = formatCurrency(unitPrice);
            totalPriceInput.value = formatCurrency(totalPrice.toFixed(0));
        }

        // Lắng nghe sự kiện nhập giá trị
        document.addEventListener('DOMContentLoaded', () => {
            const weightInput = document.getElementById('weight');
            const disposedWeightInput = document.getElementById('disposed_weight');
            const unitPriceInput = document.getElementById('unit_price');

            if (weightInput && disposedWeightInput && unitPriceInput) {
                weightInput.addEventListener('input', calculateValues);
                disposedWeightInput.addEventListener('input', calculateValues);
                unitPriceInput.addEventListener('input', calculateValues);
            }
        });

        // hanhdle update import
        $('#btn-update-import').on('click', async function() {
            const idInput = document.getElementById('import_id');
            const weightInput = document.getElementById('weight');
            const disposedWeightInput = document.getElementById('disposed_weight');
            const remainingWeightInput = document.getElementById('remaining_weight');
            const unitPriceInput = document.getElementById('unit_price');
            const totalPriceInput = document.getElementById('total_price');
            const imagesInput = document.getElementById('images');


            const id = idInput.value;
            const weight = weightInput.value;
            const disposedWeight = disposedWeightInput.value;
            const remainingWeight = remainingWeightInput.value;
            const unitPrice = unitPriceInput.value;
            const totalPrice = totalPriceInput.value;
            const images = imagesInput.files;

            const importData = inventoryData.import_list.find(item => item.id == id);


            // Validate form
            if (!weight || !disposedWeight || !remainingWeight || !unitPrice || !totalPrice) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Vui lòng nhập đầy đủ thông tin.',
                });
                return;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('supplier', importData.supplier);
            formData.append('item', importData.item);
            formData.append('import_id', importData.id);
            formData.append('inventory_id', inventoryData.id);
            formData.append('weight', weight);
            formData.append('disposed_weight', disposedWeight);
            formData.append('remaining_weight', remainingWeight);
            formData.append('unit_price', unitPrice.replace(/\./g, '')); // Bỏ dấu chấm trước khi gửi
            formData.append('total_price', totalPrice.replace(/\./g, '')); // Bỏ dấu chấm trước khi gửi
            if (images.length === 0) {
                formData.append('images[]', importData.images);
            } else {
                for (let i = 0; i < images.length; i++) {
                    formData.append('images[]', images[i]);
                }
            }


            // Call API to update import
            const response = await fetch(`backend/update_import.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: data.message,
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: data.message,
                });
            }
        })

        // handle delete import
        $('#confirmDelete').on('click', async function() {
            const idInput = document.getElementById('deleteId');
            const id = idInput.value;

            // Call API to delete import
            const response = await fetch(`backend/delete_import.php?import_id=${id}&inventory_id=${inventoryData.id}`, {
                method: 'GET'
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: data.message,
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: data.message,
                });
            }
        })


        // =================== EXPORT ==================
        // Open Edit Modal with existing export data
        const updateExportModal = document.getElementById('updateExportModal')
        if (updateExportModal) {
            updateExportModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget
                // Extract info from data-bs-* attributes
                const id = button.getAttribute('data-bs-id')
                // If necessary, you could initiate an Ajax request here
                // and then do the updating in a callback.
                const exportData = inventoryData.export_list.find(item => item.id == id)
                console.log(exportData)

                // Update the modal's content.
                const exportIdInput = document.getElementById('export_id')
                const remainingWeightInput = document.getElementById('remaining_weight_inventory')
                const customerSelect = document.getElementById('customer')
                const exportWeightInput = document.getElementById('export_weight')
                const lostWeightInput = document.getElementById('lost_weight')
                const imagesInput = document.getElementById('images')

                // Fill data to form
                exportIdInput.value = id
                remainingWeightInput.value = inventoryData.tonCuoi + exportData.export_weight
                customerSelect.value = exportData.customer
                exportWeightInput.value = exportData.export_weight
                lostWeightInput.value = exportData.lost_weight

                // Display image
                const imagePreview = document.getElementById('image-preview-export')
                imagePreview.innerHTML = ''
                exportData.images.forEach(image => {
                    console.log(image)
                    const imageElement = document.createElement('div')
                    imageElement.classList.add('col-md-4')
                    imageElement.innerHTML = `<img src="../${image}" alt="" class="img-thumbnail" style="width: 100%;">`
                    imagePreview.appendChild(imageElement)
                })
            })
        }

        // Open Delete Modal with existing export data
        const deleteExportModal = document.getElementById('deleteExportModal')
        if (deleteExportModal) {
            deleteExportModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget
                // Extract info from data-bs-* attributes
                const id = button.getAttribute('data-bs-id')

                // Update the modal's content.
                const deleteExportId = deleteExportModal.querySelector('#deleteExportId')
                deleteExportId.value = id
            })
        }

        // handle update export
        $('#btn-update-export').on('click', async function() {
            const idInput = document.getElementById('export_id');
            const remainingWeightInput = document.getElementById('remaining_weight_inventory');
            const customerSelect = document.getElementById('customer');
            const exportWeightInput = document.getElementById('export_weight');
            const lostWeightInput = document.getElementById('lost_weight');
            const imagesInput = document.getElementById('images');

            const id = idInput.value;
            const remainingWeight = remainingWeightInput.value;
            const customer = customerSelect.value;
            const exportWeight = exportWeightInput.value;
            const lostWeight = lostWeightInput.value;
            const images = imagesInput.files;

            const exportData = inventoryData.export_list.find(item => item.id == id);

            // Validate form
            if (!remainingWeight || !customer || !exportWeight || !lostWeight) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Vui lòng nhập đầy đủ thông tin.',
                });
                return;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('export_id', exportData.id);
            formData.append('inventory_id', inventoryData.id);
            formData.append('supplier', exportData.supplier);
            formData.append('item', exportData.item);
            formData.append('remaining_weight', remainingWeight);
            formData.append('customer', customer);
            formData.append('export_weight', exportWeight);
            formData.append('lost_weight', lostWeight);
            if (images.length === 0) {
                formData.append('images[]', exportData.images);
            } else {
                for (let i = 0; i < images.length; i++) {
                    formData.append('images[]', images[i]);
                }
            }

            // Call API to update export
            const response = await fetch(`backend/update_export.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: data.message,
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: data.message,
                });
            }
        })

        // handle delete export
        $('#confirmDeleteExport').on('click', async function() {
            const idInput = document.getElementById('deleteExportId');
            const id = idInput.value;

            // Call API to delete export
            const response = await fetch(`backend/delete_export.php?export_id=${id}&inventory_id=${inventoryData.id}`, {
                method: 'GET'
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: data.message,
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: data.message,
                });
            }
        })

    </script>

</body>

</html>