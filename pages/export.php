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

include('../helper/general.php');

// Lấy email và tên đầy đủ từ session
$email = $_SESSION['email'];
$fullname = $_SESSION['full_name'];

// Đọc danh sách nhà cung cấp từ suppliers.csv (bỏ dòng đầu tiên)
$suppliers = readJsonFile(SUPPLIERS_JSON_LINK);

// Đọc danh sách mặt hàng từ goods.csv (bỏ dòng đầu tiên)
$goods = readJsonFile(GOODS_JSON_LINK);

$customerList = readJsonFile(CUSTOMER_JSON_LINK);

?>

<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <title>Xuất hàng</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
                            <a class="nav-link" aria-current="page" href="index.php">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " aria-current="page" href="import.php">Nhập hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="export.php">Xuất hàng</a>
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
            <h2>Xuất Hàng</h2>
            <p>Welcome, <?php echo $fullname; ?>!</p>
        </div>
        <div class="container" style="padding-bottom: 100px;">
            <div class="row">
                <div class="col-12 d-flex justify-content-start gap-2">
                    <form id="formSubmit" class="row g-3 needs-validation" novalidate>
                        <!-- <div class="w-100 rounded border p-2 text-bg-secondary" role="alert">
                            Vui lòng nhập đúng nhà cung cấp và mặt hàng để xem thông tin trong kho!
                        </div> -->
                        <div class="col-md-6">
                            <label for="supplier" class="form-label">Nhà cung cấp:</label>
                            <select class="form-select" id="supplier" name="supplier" required onchange="getGoodsBySupplier(this.value)">
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
                            <select class="form-select" id="item" name="item" required disabled onchange="getWeightPriceByItem(this.value)">
                                <option selected disabled value="">Chọn mặt hàng</option>
                            </select>
                            <div class="invalid-feedback">
                                Vui lòng chọn mặt hàng.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="unit_price" class="form-label">Đơn giá (VNĐ):</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="inputGroupPrepend">VNĐ</span>
                                <select class="form-select" id="unit_price" name="unit_price" required disabled onchange="getWeight(this.value)">
                                    <option selected disabled value="">Chọn đơn giá</option>
                                </select>
                                <div class="invalid-feedback">
                                    Vui lòng nhập đơn giá.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="remaining_weight" class="form-label">Khối lượng trong kho (kg):</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="inputGroupPrepend">kg</span>
                                <input type="number" class="form-control" id="remaining_weight" name="remaining_weight" step="0.01" placeholder="Khối lượng trong kho" aria-describedby="inputGroupPrepend" required disabled>
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

                        <div class="col-md-6">
                            <label for="images" class="form-label">Upload hình ảnh:</label>
                            <input class="form-control" type="file" id="images" name="images[]" multiple accept="image/*">
                            <div class="invalid-feedback">
                                Vui lòng chọn hình ảnh.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12 d-flex justify-content-end mt-3">
                    <button class="btn btn-primary btn-create-order" type="submit">Xuất</button>
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

            // Khởi tạo Select2
            $('#supplier').select2({
                placeholder: "Chọn nhà cung cấp",
                allowClear: true,
                width: '100%'
            });

            $('#item').select2({
                placeholder: "Chọn mặt hàng",
                allowClear: true,
                width: '100%'
            });

            $('#customer').select2({
                placeholder: "Chọn khách hàng",
                allowClear: true,
                width: '100%'
            });
        });

        // Create order
        const formSubmit = document.getElementById('formSubmit');
        $('.btn-create-order').click(function(e) {
            if (!formSubmit.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                formSubmit.classList.add("was-validated");
            } else {
                e.preventDefault();

                const supplier = $('#supplier').val();
                const item = $('#item').val();
                const unit_price = $('#unit_price').val();
                const customer = $('#customer').val();
                const weight = $('#export_weight').val();
                const disposedWeight = $('#lost_weight').val();
                const images = $('#images')[0].files;

                // Tạo FormData
                const importData = new FormData();
                importData.append('supplier', supplier);
                importData.append('item', item);
                importData.append('unit_price', unit_price);
                importData.append('customer', customer);
                importData.append('export_weight', weight);
                importData.append('lost_weight', disposedWeight);

                // Only append images if they exist
                if (images.length > 0) {
                    for (let i = 0; i < images.length; i++) {
                        importData.append('images[]', images[i]);
                    }
                }


                // add alert processing
                Swal.fire({
                    title: 'Vui lòng chờ',
                    text: 'Đang xử lý...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    },
                });

                // log data
                for (var pair of importData.entries()) {
                    console.log(pair[0] + ', ' + pair[1]);
                }

                // Gửi dữ liệu
                $.ajax({
                    url: 'backend/export_goods.php',
                    type: 'POST',
                    data: importData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: response.message,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'export.php';
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: response.message,
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: 'Đã có lỗi xảy ra. Vui lòng thử lại sau!',
                        });
                    }
                });

            }

        });

        const supplierSelect = document.getElementById('supplier');
        const itemSelect = document.getElementById('item');
        const unitPriceInput = document.getElementById('unit_price');
        const remainingWeightInput = document.getElementById('remaining_weight');

        // Lấy thông tin mặt hàng theo nhà cung cấp
        function getGoodsBySupplier(supplierId) {

            // Nếu chưa chọn nhà cung cấp thì disable mặt hàng
            if (!supplierId) {
                itemSelect.disabled = true;
                itemSelect.innerHTML = '<option selected disabled value="">Chọn mặt hàng</option>';
                unitPriceInput.disabled = true;
                unitPriceInput.innerHTML = '<option selected disabled value="">Chọn đơn giá</option>';
                remainingWeightInput.value = '';
                remainingWeightInput.disabled = true;
                return;
            }

            // Fetch dữ liệu mặt hàng theo nhà cung cấp
            $.ajax({
                url: 'backend/get_goods_by_supplier.php',
                type: 'POST',
                data: {
                    supplierId: supplierId
                },
                success: function(response) {
                    if (response.success) {
                        // Hiển thị danh sách mặt hàng
                        if (response.data.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Không có mặt hàng',
                                text: 'Nhà cung cấp này không có mặt hàng nào trong kho!',
                            })
                            itemSelect.disabled = true;
                            itemSelect.innerHTML = '<option selected disabled value="">Chọn mặt hàng</option>';
                            unitPriceInput.disabled = true;
                            unitPriceInput.innerHTML = '<option selected disabled value="">Chọn đơn giá</option>';
                            remainingWeightInput.value = '';
                            remainingWeightInput.disabled = true;
                        } else {
                            itemSelect.innerHTML = '<option selected disabled value="">Chọn mặt hàng</option>';
                            const listGoodsInfo = [];
                            response.data.forEach(item => {
                                // kiểm tra goodsInfo có trong listGoodsInfo chưa
                                if (!listGoodsInfo.includes(item.goodsInfo.id)) {
                                    listGoodsInfo.push(item.goodsInfo.id);
                                    itemSelect.innerHTML += `<option value="${item.goodsInfo.id}">${item.goodsInfo.codeGoods} - ${item.goodsInfo.nameGoods}</option>`;
                                }
                            });

                            // Enable mặt hàng
                            itemSelect.disabled = false;
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Đã có lỗi xảy ra. Vui lòng thử lại sau!',
                    });
                }
            });
        }

        let weightPriceData = [];
        // Lấy thông tin khối lượng và đơn giá theo mặt hàng và nhà cung cấp
        function getWeightPriceByItem(itemId) {
            const supplierId = $('#supplier').val();

            // Nếu chưa chọn mặt hàng thì disable khối lượng và đơn giá
            if (!itemId) {
                unitPriceInput.value = '';
                unitPriceInput.disabled = true;
                return;
            }

            // Fetch dữ liệu khối lượng và đơn giá theo mặt hàng
            $.ajax({
                url: 'backend/get_weight_price_by_item.php',
                type: 'POST',
                data: {
                    supplierId: supplierId,
                    itemId: itemId
                },
                success: function(response) {
                    if (response.success) {
                        weightPriceData = response.data;
                        // Hiển thị thông tin đơn giá
                        unitPriceInput.innerHTML = '<option selected disabled value="">Chọn đơn giá</option>';
                        response.data.forEach(item => {
                            unitPriceInput.innerHTML += `<option value="${item.unit_price}">${formatCurrency(item.unit_price)} VND</option>`;
                        });

                        unitPriceInput.disabled = false;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Đã có lỗi xảy ra. Vui lòng thử lại sau!',
                    });
                }
            });
        }

        // Lấy thông tin khối lượng theo đơn giá
        function getWeight(price) {
            const unitPrice = parseFloat(price.replace(/\./g, '')) || 0; // Bỏ dấu chấm trước khi parse

            // Tìm khối lượng còn lại theo đơn giá
            const weight = weightPriceData.find(item => item.unit_price === unitPrice).weight;

            // Hiển thị khối lượng còn lại
            remainingWeightInput.value = weight.toFixed(2);
        }
    </script>

</body>

</html>