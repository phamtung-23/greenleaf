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

// get id from url
$inventoryId = $_GET['id'];
$inventoryData = calculateInventoryReportById($inventoryId, $dataList);

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
                            Lịch sử nhập hàng
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
                            Lịch sử xuất hàng
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
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
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

    </script>

</body>

</html>