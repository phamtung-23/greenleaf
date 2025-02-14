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
            <h4>BẢNG THEO DÕI HÀNG NHẬP-XUẤT-TỒN</h4>
            <p>Welcome, <?php echo $fullname; ?>!</p>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-12 d-flex justify-content-start gap-2">
                    <a href="import.php">
                        <button class="btn btn-success mt-2 mb-2"><i class="fa-solid fa-file-import"></i> Nhập hàng</button>
                    </a>
                    <a href="export.php">
                        <button class="btn btn-warning mt-2 mb-2"><i class="fa-solid fa-file-export"></i> Xuất hàng</button>
                    </a>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <div class="table-responsive" style="margin-bottom: 80px !important;">
                <table id="adminTable" class="table table-bordered border border-1">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" rowspan="2" class="align-top" style="width: 80px;">Ngày</th>
                            <th scope="col" rowspan="2" class="align-top">MH</th>
                            <th scope="col" rowspan="2" class="align-top">Mã NCC</th>
                            <th scope="col" rowspan="2" class="align-top" style="width: 150px;">Tên mặt hàng</th>
                            <th scope="col" rowspan="2" class="align-top" style="width: 150px;">Tên NCC</th>
                            <th scope="col" rowspan="2" class="align-top">ĐVT</th>
                            <th scope="col" rowspan="2" class="align-top">Tồn đầu</th>
                            <th scope="col" rowspan="2" class="align-top">SLN</th>
                            <th scope="col" rowspan="2" class="align-top">Xả</th>
                            <th scope="col" rowspan="2" class="align-top">Còn lại</th>
                            <th scope="col" rowspan="2" class="align-top">Đơn giá</th>
                            <th scope="col" rowspan="2" class="align-top">Thành tiền</th>
                            <th scope="col" colspan="12" class="text-center">SỐ LƯỢNG XUẤT</th>
                            <th scope="col" rowspan="2" class="align-top">Tổng SLX</th>
                            <th scope="col" rowspan="2" class="align-top">Hao hụt</th>
                            <th scope="col" rowspan="2" class="align-top">CP hao hụt</th>
                            <th scope="col" rowspan="2" class="align-top">TỒN CUỐI</th>
                            <th scope="col" rowspan="2" class="align-top">CP TỒN</th>
                        </tr>
                        <tr>
                            <?php
                            foreach ($customerData as $customer) {
                                echo "<th scope='col' class='align-top' style='width: 70px;'>" . $customer['nameCustomer'] . "</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($dataList as $data) {
                            $reportData = calculateInventoryReport($data);
                        ?>
                            <tr class="border border-1" onclick="alert('Chức năng đang được phát triển!');" style="cursor: pointer;">
                                <td style="border: solid 1px #dbdbdb;"><?php echo $reportData['createdAt']; ?></td>
                                <td style="border: solid 1px #dbdbdb;"><?php echo $reportData['codeGoods']; ?></td>
                                <td style="border: solid 1px #dbdbdb;"><?php echo $reportData['codeNCC']; ?></td>
                                <td style="border: solid 1px #dbdbdb;"><?php echo $reportData['nameGoods']; ?></td>
                                <td style="border: solid 1px #dbdbdb;"><?php echo $reportData['nameNCC']; ?></td>
                                <td style="border: solid 1px #dbdbdb; text-align: center;"><?php echo $reportData['DVT']; ?></td>
                                <td style="border: solid 1px #dbdbdb; text-align: center;"><?php echo $reportData['tonDau']; ?></td>
                                <td style="border: solid 1px #dbdbdb; background-color: #f2bd64; font-weight: bold; text-align: center;"><?php echo $reportData['SLN']; ?></td>
                                <td style="border: solid 1px #dbdbdb; text-align: center;"><?php echo $reportData['xa']; ?></td>
                                <td style="border: solid 1px #dbdbdb; font-weight: bold; text-align: center;"><?php echo $reportData['conLai']; ?></td>
                                <td style="border: solid 1px #dbdbdb; text-align: end;"><?php echo number_format($reportData['unit_price'], 0, ',', '.'); ?></td>
                                <td style="border: solid 1px #dbdbdb; background-color:rgb(83, 150, 244); font-weight: bold; text-align: end;"><?php echo number_format($reportData['thanhTien'], 0, ',', '.'); ?></td>
                                <?php
                                foreach ($customerData as $customer) {
                                    $isExist = checkCustomerDataById($customer['id'], $reportData['customerData']);
                                    if ($isExist[0]) {
                                        echo "<td style='border: solid 1px #dbdbdb; text-align: center;'>" . $isExist[1] . "</td>";
                                    } else {
                                        echo "<td style='border: solid 1px #dbdbdb;'></td>";
                                    }
                                }
                                ?>
                                <td style="border: solid 1px #dbdbdb; background-color:rgb(223, 165, 255); font-weight: bold; text-align: center;"><?php echo $reportData['SLX']; ?></td>
                                <td style="border: solid 1px #dbdbdb; background-color:rgb(238, 208, 255); font-weight: bold; text-align: center;"><?php echo $reportData['haoHut']; ?></td>
                                <td style="border: solid 1px #dbdbdb; text-align: end;"><?php echo number_format($reportData['CPHaoHut'], 0, ',', '.'); ?></td>
                                <td style="border: solid 1px #dbdbdb; background-color: lightgreen; color: #f20000; font-weight: bold; text-align: center;"><?php echo $reportData['tonCuoi']; ?></td>
                                <td style="border: solid 1px #dbdbdb; background-color: bisque; font-weight: bold; text-align: end;"><?php echo  number_format($reportData['CPTonCuoi'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer id="sticky-footer" class="flex-shrink-0 py-2 bg-dark text-white-50">
        <div class="container text-center">
            <small>© 2025 Phần mềm phát triển bởi PTTung 0359663439</small>
        </div>
    </footer>
    <script>
        $(document).ready(function() {
            if (window.innerWidth <= 550) {
                $('#adminTable').DataTable({
                    scrollY: true, // Set the vertical scrolling height
                    scrollX: true, // Set the vertical scrolling height
                    scrollCollapse: true, // Allow the table to reduce height if less content
                    paging: true, // Enable pagination
                    searching: true, // Enable searching
                    ordering: true, // Enable column sorting
                    info: true, // Show table info
                    language: {
                        search: "Search:",
                        paginate: {
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
            } else {
                $('#adminTable').DataTable({
                    scrollY: true, // Set the vertical scrolling height
                    scrollX: true, // Set the vertical scrolling height
                    scrollCollapse: true, // Allow the table to reduce height if less content
                    paging: true, // Enable pagination
                    searching: true, // Enable searching
                    ordering: true, // Enable column sorting
                    info: true, // Show table info
                    language: {
                        search: "Search:",
                        paginate: {
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
            }
        });
    </script>

</body>

</html>