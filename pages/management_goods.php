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
$goodsList = readJsonFile('../database/database/goods.json');

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
              <a class="nav-link" aria-current="page" href="management_import.php">Nhập hàng</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" aria-current="page" href="management_export.php">Xuất hàng</a>
            </li>
            <li class="nav-item">
              <a class="nav-link " aria-current="page" href="management_ncc.php">Nhà cung cấp</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="management_goods.php">Hàng hóa</a>
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
      <h2>Quản lý hàng hóa</h2>
      <p>Welcome, <?php echo $fullname; ?>!</p>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2">
          <button class="btn btn-success mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#addGoodsModal" data-bs-whatever="@mdo"><i class="fa-solid fa-plus"></i> Thêm hàng hóa</button>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="table-responsive" style="margin-bottom: 80px !important;">
        <table id="adminTable" class="table">
          <thead class="table-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Mã hàng hóa</th>
              <th scope="col">Tên hàng hóa</th>
              <th scope="col">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($goodsList as $index => $goods) {
              echo "<tr>";
              echo "<th scope='row'>" . ($index + 1) . "</th>";
              echo "<td>" . $goods['codeGoods'] . "</td>";
              echo "<td>" . $goods['nameGoods'] . "</td>";
              echo "<td class='align-middle h-100 d-flex gap-1 justify-content-center align-items-center'>
                      <button type='button' class='btn btn-secondary btn-edit-goods' 
                      data-bs-id='{$goods['id']}' 
                      data-bs-name='{$goods['nameGoods']}' 
                      data-bs-code='{$goods['codeGoods']}' 
                      
                      data-bs-toggle='modal' 
                      data-bs-target='#editGoodsModal'>
                        <i class='fa-solid fa-pen-to-square'></i>
                      </button>
                      <button type='button' class='btn btn-danger btn-delete-NCC' data-bs-toggle='modal' data-bs-target='#deleteGoodsModal' 
                      data-goods-id='{$goods['id']}'
                      data-goods-code='{$goods['codeGoods']}' data-goods-name='{$goods['nameGoods']}'>
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
    <div class="modal fade" id="addGoodsModal" tabindex="-1" aria-labelledby="addGoodsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="addGoodsModalLabel">Thêm loại hàng hóa</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="codeGoods" class="col-form-label">Mã hàng hóa:</label>
                <input type="text" class="form-control" id="codeGoods" name="codeGoods">
              </div>
              <div class="mb-3">
                <label for="nameGoods" class="col-form-label">Tên hàng hóa:</label>
                <input type="text" class="form-control" id="nameGoods" name="nameGoods">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-success" id="btn-add-goods">Thêm</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Edit -->
    <div class="modal fade" id="editGoodsModal" tabindex="-1" aria-labelledby="editGoodsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="editGoodsModalLabel">Chỉnh sửa hàng hóa</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <input type="hidden" id="editIdGoods"> <!-- Hidden field to store the goods's ID -->
              <div class="mb-3">
                <label for="editCodeGoods" class="col-form-label">Mã hàng hóa:</label>
                <input type="text" class="form-control" id="editCodeGoods" name="editCodeGoods">
              </div>
              <div class="mb-3">
                <label for="editNameGoods" class="col-form-label">Tên hàng hóa:</label>
                <input type="text" class="form-control" id="editNameGoods" name="editNameGoods">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" id="btn-update-goods">Cập nhật</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Delete goods -->
    <div class="modal fade" id="deleteGoodsModal" tabindex="-1" aria-labelledby="deleteGoodsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h1 class="modal-title fs-5" id="deleteGoodsModalLabel">Xóa hàng hóa</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa hàng hóa <strong id="deleteGoodsName"></strong>?</p>
            <input type="hidden" id="deleteGoodsId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteGoods">Xóa</button>
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
      const exampleModal = document.getElementById('editGoodsModal')
      if (exampleModal) {
        exampleModal.addEventListener('show.bs.modal', event => {
          // Button that triggered the modal
          const button = event.relatedTarget
          // Extract info from data-bs-* attributes
          const id = button.getAttribute('data-bs-id')
          const name = button.getAttribute('data-bs-name')
          const code = button.getAttribute('data-bs-code')
          // If necessary, you could initiate an Ajax request here
          // and then do the updating in a callback.

          // Update the modal's content.
          const editIdGoods = exampleModal.querySelector('#editIdGoods')
          const editCodeGoods = exampleModal.querySelector('#editCodeGoods')
          const editNameGoods = exampleModal.querySelector('#editNameGoods')

          editIdGoods.value = id
          editCodeGoods.value = code
          editNameGoods.value = name
        })
      }

      // Open Delete Modal with existing goods data
      const deleteModal = document.getElementById('deleteGoodsModal')
      if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
          // Button that triggered the modal
          const button = event.relatedTarget
          // Extract info from data-bs-* attributes
          const id = button.getAttribute('data-goods-id')
          const name = button.getAttribute('data-goods-name')

          // Update the modal's content.
          const deleteGoodsName = deleteModal.querySelector('#deleteGoodsName')
          const deleteGoodsId = deleteModal.querySelector('#deleteGoodsId')

          deleteGoodsName.textContent = name
          deleteGoodsId.value = id
        })
      }

      // Handle add goods
      $('#btn-add-goods').on('click', function() {
        let codeGoods = $('#codeGoods').val();
        let nameGoods = $('#nameGoods').val();

        if (codeGoods == '' || nameGoods == '') {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Vui lòng nhập đầy đủ thông tin!'
          });
        } else {
          $.ajax({
            url: 'backend/addGoods.php',
            type: 'POST',
            data: {
              codeGoods: codeGoods,
              nameGoods: nameGoods
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
      $('#btn-update-goods').on('click', function() {
        let id = $('#editIdGoods').val();
        let updatedCodeGoods = $('#editCodeGoods').val();
        let updatedNameGoods = $('#editNameGoods').val();

        if (updatedCodeGoods === '' || updatedNameGoods === '') {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Vui lòng nhập đầy đủ thông tin!'
          });
        } else {
          $.ajax({
            url: 'backend/editGoods.php',
            type: 'POST',
            data: {
              id: id,
              updatedCodeGoods: updatedCodeGoods,
              updatedNameGoods: updatedNameGoods
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
      $('#confirmDeleteGoods').on('click', function() {
        let id = $('#deleteGoodsId').val();

        $.ajax({
          url: 'backend/deleteGoods.php',
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