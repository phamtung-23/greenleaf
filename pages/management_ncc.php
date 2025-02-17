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
$suppliers = readJsonFile('../database/database/suppliers.json');

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
              <a class="nav-link " aria-current="page" href="index.php">Trang chủ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" aria-current="page" href="import.php">Nhập hàng</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" aria-current="page" href="export.php">Xuất hàng</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="management_ncc.php">Nhà cung cấp</a>
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
      <h2>Quản lý nhà cung cấp</h2>
      <p>Welcome, <?php echo $fullname; ?>!</p>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2">
          <button class="btn btn-success mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#addNCCModal" data-bs-whatever="@mdo"><i class="fa-solid fa-plus"></i> Thêm nhà cung cấp</button>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="table-responsive" style="margin-bottom: 80px !important;">
        <table id="adminTable" class="table">
          <thead class="table-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Mã nhà cung cấp</th>
              <th scope="col">Tên nhà cung cấp</th>
              <th scope="col">Địa chỉ</th>
              <th scope="col">SĐT</th>
              <th scope="col">Số TK Ngân hàng</th>
              <th scope="col">Tên ngân hàng</th>
              <th scope="col">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($suppliers as $index => $supplier) {
              echo "<tr>";
              echo "<th scope='row'>" . ($index + 1) . "</th>";
              echo "<td>" . $supplier['codeNCC'] . "</td>";
              echo "<td>" . $supplier['nameNCC'] . "</td>";
              echo "<td>" . $supplier['address'] . "</td>";
              echo "<td>" . $supplier['phone'] . "</td>";
              echo "<td>" . $supplier['bankNumber'] . "</td>";
              echo "<td>" . $supplier['bankName'] . "</td>";
              echo "<td class='align-middle h-100 d-flex gap-1 justify-content-center align-items-center'>
                        <button type='button' class='btn btn-secondary btn-edit-NCC' 
                          data-bs-id='{$supplier['id']}' 
                          data-bs-name='{$supplier['nameNCC']}' 
                          data-bs-code='{$supplier['codeNCC']}' 
                          data-bs-address='{$supplier['address']}'
                          data-bs-phone='{$supplier['phone']}'
                          data-bs-bank-number='{$supplier['bankNumber']}'
                          data-bs-bank-name='{$supplier['bankName']}'
                          
                          data-bs-toggle='modal' 
                          data-bs-target='#editNCCModal'>
                          <i class='fa-solid fa-pen-to-square'></i>
                        </button>
                        <button type='button' class='btn btn-danger btn-delete-NCC' data-bs-toggle='modal' data-bs-target='#deleteNCCModal' 
                        data-supplier-id='{$supplier['id']}'
                        data-supplier-code='{$supplier['codeNCC']}' data-supplier-name='{$supplier['nameNCC']}'>
                          <i class='fa-solid fa-trash'></i>
                        </button>
                    </td>";
              echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Add -->
    <div class="modal fade" id="addNCCModal" tabindex="-1" aria-labelledby="addNCCModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="addNCCModalLabel">Thêm nhà cung cấp</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="codeNCC" class="col-form-label">Mã NCC:</label>
                <input type="text" class="form-control" id="codeNCC" name="codeNCC">
              </div>
              <div class="mb-3">
                <label for="nameNCC" class="col-form-label">Tên NCC:</label>
                <input type="text" class="form-control" id="nameNCC" name="nameNCC">
              </div>
              <div class="mb-3">
                <label for="address" class="col-form-label">Địa chỉ:</label>
                <input type="text" class="form-control" id="address" name="address">
              </div>
              <div class="mb-3">
                <label for="phone" class="col-form-label">SĐT:</label>
                <input type="text" class="form-control" id="phone" name="phone">
              </div>
              <div class="mb-3">
                <label for="bankNumber" class="col-form-label">Số TK Ngân hàng:</label>
                <input type="text" class="form-control" id="bankNumber" name="bankNumber">
              </div>
              <div class="mb-3">
                <label for="bankName" class="col-form-label">Tên ngân hàng:</label>
                <input type="text" class="form-control" id="bankName" name="bankName">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-success" id="btn-add-NCC">Thêm</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Edit -->
    <div class="modal fade" id="editNCCModal" tabindex="-1" aria-labelledby="editNCCModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="editNCCModalLabel">Chỉnh sửa nhà cung cấp</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <input type="hidden" id="editIdNCC"> <!-- Hidden field to store the supplier's ID -->
              <div class="mb-3">
                <label for="editCodeNCC" class="col-form-label">Mã NCC:</label>
                <input type="text" class="form-control" id="editCodeNCC" name="editCodeNCC">
              </div>
              <div class="mb-3">
                <label for="editNameNCC" class="col-form-label">Tên NCC:</label>
                <input type="text" class="form-control" id="editNameNCC" name="editNameNCC">
              </div>
              <div class="mb-3">
                <label for="editAddress" class="col-form-label">Địa chỉ:</label>
                <input type="text" class="form-control" id="editAddress" name="editAddress">
              </div>
              <div class="mb-3">
                <label for="editPhone" class="col-form-label">SĐT:</label>
                <input type="text" class="form-control" id="editPhone" name="editPhone">
              </div>
              <div class="mb-3">
                <label for="editBankNumber" class="col-form-label">Số TK Ngân hàng:</label>
                <input type="text" class="form-control" id="editBankNumber" name="editBankNumber">
              </div>
              <div class="mb-3">
                <label for="editBankName" class="col-form-label">Tên ngân hàng:</label>
                <input type="text" class="form-control" id="editBankName" name="editBankName">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" id="btn-update-NCC">Cập nhật</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Delete Supplier -->
    <div class="modal fade" id="deleteNCCModal" tabindex="-1" aria-labelledby="deleteNCCModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h1 class="modal-title fs-5" id="deleteNCCModalLabel">Xóa nhà cung cấp</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa nhà cung cấp <strong id="deleteSupplierName"></strong>?</p>
            <input type="hidden" id="deleteSupplierId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteNCC">Xóa</button>
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

      // Filter table when the status dropdown changes
      $('#statusFilter').on('change', function() {
        let selectedStatus = $(this).val();

        $('#adminTable').DataTable().columns(6).search(selectedStatus).draw();
      });

      // Open Edit Modal with existing supplier data
      const exampleModal = document.getElementById('editNCCModal')
      if (exampleModal) {
        exampleModal.addEventListener('show.bs.modal', event => {
          // Button that triggered the modal
          const button = event.relatedTarget
          // Extract info from data-bs-* attributes
          const id = button.getAttribute('data-bs-id')
          const name = button.getAttribute('data-bs-name')
          const code = button.getAttribute('data-bs-code')
          const address = button.getAttribute('data-bs-address')
          const phone = button.getAttribute('data-bs-phone')
          const bankNumber = button.getAttribute('data-bs-bank-number')
          const bankName = button.getAttribute('data-bs-bank-name')
          // If necessary, you could initiate an Ajax request here
          // and then do the updating in a callback.

          // Update the modal's content.
          const editIdNCC = exampleModal.querySelector('#editIdNCC')
          const editCodeNCC = exampleModal.querySelector('#editCodeNCC')
          const editNameNCC = exampleModal.querySelector('#editNameNCC')
          const editAddress = exampleModal.querySelector('#editAddress')
          const editPhone = exampleModal.querySelector('#editPhone')
          const editBankNumber = exampleModal.querySelector('#editBankNumber')
          const editBankName = exampleModal.querySelector('#editBankName')

          editIdNCC.value = id
          editCodeNCC.value = code
          editNameNCC.value = name
          editAddress.value = address
          editPhone.value = phone
          editBankNumber.value = bankNumber
          editBankName.value = bankName
        })
      }

      // Open Delete Modal with existing supplier data
      const deleteModal = document.getElementById('deleteNCCModal')
      if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
          // Button that triggered the modal
          const button = event.relatedTarget
          // Extract info from data-bs-* attributes
          const id = button.getAttribute('data-supplier-id')
          const name = button.getAttribute('data-supplier-name')

          // Update the modal's content.
          const deleteSupplierName = deleteModal.querySelector('#deleteSupplierName')
          const deleteSupplierId = deleteModal.querySelector('#deleteSupplierId')

          deleteSupplierName.textContent = name
          deleteSupplierId.value = id
        })
      }


      // Handle add NCC
      $('#btn-add-NCC').on('click', function() {
        let codeNCC = $('#codeNCC').val();
        let nameNCC = $('#nameNCC').val();
        let address = $('#address').val();
        let phone = $('#phone').val();
        let bankNumber = $('#bankNumber').val();
        let bankName = $('#bankName').val();

        if (codeNCC == '' || nameNCC == '') {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Vui lòng nhập đầy đủ thông tin!'
          });
        } else {
          $.ajax({
            url: 'backend/addNCC.php',
            type: 'POST',
            data: {
              codeNCC: codeNCC,
              nameNCC: nameNCC,
              address: address,
              phone: phone,
              bankNumber: bankNumber,
              bankName: bankName
            },
            success: function(response) {
              if (response.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Thành công',
                  text: response.message
                }).then((result) => {
                  if (result.isConfirmed) {
                    location.reload();
                  }
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Lỗi',
                  text: response.message
                });
              }
            }
          });
        }
      });

      // Handle Update Supplier
      $('#btn-update-NCC').on('click', function() {
        let id = $('#editIdNCC').val();
        let updatedCodeNCC = $('#editCodeNCC').val();
        let updatedNameNCC = $('#editNameNCC').val();
        let updatedAddress = $('#editAddress').val();
        let updatedPhone = $('#editPhone').val();
        let updatedBankNumber = $('#editBankNumber').val();
        let updatedBankName = $('#editBankName').val();

        if (updatedCodeNCC === '' || updatedNameNCC === '') {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Vui lòng nhập đầy đủ thông tin!'
          });
        } else {
          $.ajax({
            url: 'backend/editNCC.php',
            type: 'POST',
            data: {
              id: id,
              updatedCodeNCC: updatedCodeNCC,
              updatedNameNCC: updatedNameNCC,
              updatedAddress: updatedAddress,
              updatedPhone: updatedPhone,
              updatedBankNumber: updatedBankNumber,
              updatedBankName: updatedBankName
            },
            success: function(response) {
              if (response.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Thành công',
                  text: response.message
                }).then((result) => {
                  if (result.isConfirmed) {
                    location.reload();
                  }
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Lỗi',
                  text: response.message
                });
              }
            }
          });
        }
      });

      // Handle Delete Supplier
      $('#confirmDeleteNCC').on('click', function() {
        let id = $('#deleteSupplierId').val();

        $.ajax({
          url: 'backend/deleteNCC.php',
          type: 'POST',
          data: {
            id: id
          },
          success: function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: response.message
              }).then((result) => {
                if (result.isConfirmed) {
                  location.reload();
                }
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: response.message
              });
            }
          }
        });
      });
    });
  </script>

</body>

</html>