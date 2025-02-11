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

// Đọc danh sách nhà cung cấp từ goodss.csv (bỏ dòng đầu tiên)
$customerList = readJsonFile('../database/database/customers.json');

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
              <a class="nav-link " aria-current="page" href="management_ncc.php">Nhà cung cấp</a>
            </li>
            <li class="nav-item">
              <a class="nav-link " aria-current="page" href="management_goods.php">Hàng hóa</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="management_customer.php">Khách hàng</a>
            </li>
          </ul>
          <form action="../logout.php" class="d-flex" role="search">
            <button class="btn btn-outline-danger" type="submit">Đăng xuất</button>
          </form>
        </div>
      </div>
    </nav>
    <div class="container pt-3">
      <h2>Quản lý khách hàng</h2>
      <p>Welcome, <?php echo $fullname; ?>!</p>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2">
          <button class="btn btn-success mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal" data-bs-whatever="@mdo"><i class="fa-solid fa-plus"></i> Thêm khách hàng</button>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="table-responsive" style="margin-bottom: 80px !important;">
        <table id="adminTable" class="table">
          <thead class="table-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Tên khách hàng</th>
              <th scope="col">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($customerList as $index => $customer) {
              echo "<tr>";
              echo "<th scope='row'>" . ($index + 1) . "</th>";
              echo "<td>" . $customer['nameCustomer'] . "</td>";
              echo "<td class='align-middle h-100 d-flex gap-1 justify-content-center align-items-center'>
                      <button type='button' class='btn btn-secondary btn-edit-goods' 
                      data-bs-id='{$customer['id']}' 
                      data-bs-name='{$customer['nameCustomer']}' 
                      
                      data-bs-toggle='modal' 
                      data-bs-target='#editCustomerModal'>
                        <i class='fa-solid fa-pen-to-square'></i>
                      </button>
                      <button type='button' class='btn btn-danger btn-delete-NCC' data-bs-toggle='modal' data-bs-target='#deleteCustomerModal' 
                      data-goods-id='{$customer['id']}'
                      data-goods-name='{$customer['nameCustomer']}'>
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
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="addCustomerModalLabel">Thêm khách hàng</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="nameCustomer" class="col-form-label">Tên khách hàng:</label>
                <input type="text" class="form-control" id="nameCustomer" name="nameCustomer">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-success" id="btn-add-customer">Thêm</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Edit -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="editCustomerModalLabel">Chỉnh sửa khách hàng</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <input type="hidden" id="editIdCustomer"> <!-- Hidden field to store the goods's ID -->
              <div class="mb-3">
                <label for="editNameCustomer" class="col-form-label">Tên khách hàng:</label>
                <input type="text" class="form-control" id="editNameCustomer" name="editNameCustomer">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" id="btn-update-customer">Cập nhật</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Delete goods -->
    <div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h1 class="modal-title fs-5" id="deleteCustomerModalLabel">Xóa khách hàng</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa khách hàng <strong id="deleteCustomerName"></strong>?</p>
            <input type="hidden" id="deleteCustomerId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteCustomer">Xóa</button>
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

      // Open Edit Modal with existing goods data
      const exampleModal = document.getElementById('editCustomerModal')
      if (exampleModal) {
        exampleModal.addEventListener('show.bs.modal', event => {
          // Button that triggered the modal
          const button = event.relatedTarget
          // Extract info from data-bs-* attributes
          const id = button.getAttribute('data-bs-id')
          const name = button.getAttribute('data-bs-name')
          // If necessary, you could initiate an Ajax request here
          // and then do the updating in a callback.

          // Update the modal's content.
          const editIdCustomer = exampleModal.querySelector('#editIdCustomer')
          const editNameCustomer = exampleModal.querySelector('#editNameCustomer')

          editIdCustomer.value = id
          editNameCustomer.value = name
        })
      }

      // Open Delete Modal with existing goods data
      const deleteModal = document.getElementById('deleteCustomerModal')
      if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
          // Button that triggered the modal
          const button = event.relatedTarget
          // Extract info from data-bs-* attributes
          const id = button.getAttribute('data-goods-id')
          const name = button.getAttribute('data-goods-name')

          // Update the modal's content.
          const deleteCustomerName = deleteModal.querySelector('#deleteCustomerName')
          const deleteCustomerId = deleteModal.querySelector('#deleteCustomerId')

          deleteCustomerName.textContent = name
          deleteCustomerId.value = id
        })
      }

      // Handle add goods
      $('#btn-add-customer').on('click', function() {
        let nameCustomer = $('#nameCustomer').val();

        if (nameCustomer == '') {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Vui lòng nhập đầy đủ thông tin!'
          });
        } else {
          $.ajax({
            url: 'backend/addCustomer.php',
            type: 'POST',
            data: {
              nameCustomer: nameCustomer
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

      // Handle Update goods
      $('#btn-update-customer').on('click', function() {
        let id = $('#editIdCustomer').val();
        let updatedNameCustomer = $('#editNameCustomer').val();

        if (updatedNameCustomer === '') {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Vui lòng nhập đầy đủ thông tin!'
          });
        } else {
          $.ajax({
            url: 'backend/editCustomer.php',
            type: 'POST',
            data: {
              id: id,
              updatedNameCustomer: updatedNameCustomer
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
      // Handle Delete goods
      $('#confirmDeleteCustomer').on('click', function() {
        let id = $('#deleteCustomerId').val();

        $.ajax({
          url: 'backend/deleteCustomer.php',
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