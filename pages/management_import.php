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
$importList = readJsonFile('../database/import_goods.json');

// Đọc danh sách nhà cung cấp từ suppliers.csv (bỏ dòng đầu tiên)
$suppliers = readJsonFile(SUPPLIERS_JSON_LINK);

// Đọc danh sách mặt hàng từ goods.csv (bỏ dòng đầu tiên)
$goods = readJsonFile(GOODS_JSON_LINK);

$filePath = IMPORT_EXPORT_INVENTORY_JSON_PATH;
$importExportInventory = readJsonFile($filePath);

echo "<script>
    const importList = " . json_encode($importList) . ";
    const importExportInventory = " . json_encode($importExportInventory) . ";
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
              <a class="nav-link " aria-current="page" href="index.php">Trang chủ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="management_import.php">Nhập hàng</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" aria-current="page" href="management_export.php">Xuất hàng</a>
            </li>
            <li class="nav-item">
              <a class="nav-link " aria-current="page" href="management_ncc.php">Nhà cung cấp</a>
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
      <h2>Quản lý danh sách nhập hàng</h2>
      <p>Welcome, <?php echo $fullname; ?>!</p>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="import.php">
            <button class="btn btn-success mt-2 mb-2"><i class="fa-solid fa-file-import"></i> Nhập hàng</button>
          </a>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="table-responsive" style="margin-bottom: 80px !important;">
        <table id="adminTable" class="table">
          <thead class="table-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Ngày Tạo</th>
              <th scope="col">Mặt hàng</th>
              <th scope="col">Nhà Cung cấp</th>
              <th scope="col">Khối lượng</th>
              <th scope="col">Xả</th>
              <th scope="col">khối lượng còn lại</th>
              <th scope="col">Đơn giá</th>
              <th scope="col">Tổng tiền</th>
              <th scope="col">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $ixNumber = 1;
            foreach ($importList as $index => $import) {
              $supplierData = getDataById($import['supplier'], '../database/database/suppliers.json');
              $itemData = getDataById($import['item'], '../database/database/goods.json');

              echo "<tr>";
              echo "<th scope='row'>" . ($ixNumber) . "</th>";
              echo "<td>" . date('d-m-Y', strtotime($import['createdAt'])) . "</td>";
              echo "<td>" . $itemData['nameGoods'] . "</td>";
              echo "<td>" . $supplierData['nameNCC'] . "</td>";
              echo "<td>" . $import['weight'] . " kg</td>";
              echo "<td>" . $import['disposed_weight'] . " kg</td>";
              echo "<td>" . $import['remaining_weight'] . " kg</td>";
              echo "<td>" . number_format($import['unit_price'], 0, ',', '.') . " VND</td>";
              echo "<td>" . number_format($import['total_price'], 0, ',', '.') . " VND</td>";
              echo "<td>
              <div class='align-middle h-100 d-flex gap-1 justify-content-center align-items-center'>
                      <button type='button' class='btn btn-secondary btn-edit-goods' 
                        data-bs-id='{$import['id']}'
                        data-bs-toggle='modal' 
                        data-bs-target='#updateImportModal'>
                          <i class='fa-solid fa-pen-to-square'></i>
                      </button>
                      <button type='button' class='btn btn-danger btn-delete-goods' 
                        data-bs-id='{$import['id']}' 
                        data-bs-toggle='modal' 
                        data-bs-target='#deleteModal'>
                          <i class='fa-solid fa-trash'></i>
                      </button>
              </div>
                      
                    </td>";
              echo "</tr>";
              $ixNumber++;
            }
            ?>
          </tbody>
        </table>
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
                <input class="form-control" type="file" id="images" name="images[]" multiple accept="image/*">
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
    });


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
        const importData = importList.find(item => item.id == id)

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

    // Hàm định dạng số tiền
    function formatCurrency(value) {
      return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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

      let inventoryId = null;
      let importData = null;
      importExportInventory.find(item => {
        if (item.import_list) {
          item.import_list.forEach(importItem => {
            if (importItem.id == id) {
              inventoryId = item.id;
              importData = importItem;
              return;
            }
          });
        }

      });

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
      formData.append('inventory_id', inventoryId);
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

      let inventoryId = null;
      importExportInventory.find(item => {
        if (item.import_list) {
          item.import_list.forEach(importItem => {
            if (importItem.id == id) {
              inventoryId = item.id;
              return;
            }
          });
        }
      });

      // Call API to delete import
      const response = await fetch(`backend/delete_import.php?import_id=${id}&inventory_id=${inventoryId}`, {
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