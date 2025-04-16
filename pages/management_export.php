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

$customerList = readJsonFile(CUSTOMER_JSON_LINK);

// Đọc danh sách nhà cung cấp từ goodss.csv (bỏ dòng đầu tiên)
$exportList = readJsonFile('../database/export_goods.json');

// Đọc danh sách nhà cung cấp từ suppliers.csv (bỏ dòng đầu tiên)
$suppliers = readJsonFile(SUPPLIERS_JSON_LINK);

// Đọc danh sách mặt hàng từ goods.csv (bỏ dòng đầu tiên)
$goods = readJsonFile(GOODS_JSON_LINK);

$filePath = IMPORT_EXPORT_INVENTORY_JSON_PATH;
$importExportInventory = readJsonFile($filePath);

echo "<script>
    const exportList = " . json_encode($exportList) . ";
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
              <a class="nav-link" aria-current="page" href="management_import.php">Nhập hàng</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="management_export.php">Xuất hàng</a>
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
      <h2>Quản lý danh sách xuất hàng</h2>
      <p>Welcome, <?php echo $fullname; ?>!</p>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="export.php">
            <button class="btn btn-warning mt-2 mb-2"><i class="fa-solid fa-file-export"></i> Xuất hàng</button>
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
              <th scope="col">Khách hàng</th>
              <th scope="col">Khối lượng xuất</th>
              <th scope="col">Hao hụt</th>
              <th scope="col">Đơn giá</th>
              <th scope="col">Tổng tiền</th>
              <th scope="col">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $ixNumber = 1;
            foreach ($exportList as $index => $export) {
              $supplierData = getDataById($export['supplier'], '../database/database/suppliers.json');
              $itemData = getDataById($export['item'], '../database/database/goods.json');
              $customerData = getDataById($export['customer'], '../database/database/customers.json');
              $totalPriceExport = ($export['export_weight'] + $export['lost_weight']) * $export['unit_price'];

              echo "<tr>";
              echo "<th scope='row'>" . ($ixNumber) . "</th>";
              echo "<td>" . date('d-m-Y', strtotime($export['createdAt'])) . "</td>";
              echo "<td>" . $itemData['nameGoods'] . "</td>";
              echo "<td>" . $supplierData['nameNCC'] . "</td>";
              echo "<td>" . $customerData['nameCustomer'] . "</td>";
              echo "<td>" . $export['export_weight'] . " kg</td>";
              echo "<td>" . $export['lost_weight'] . " kg</td>";
              echo "<td>" . number_format($export['unit_price'], 0, ',', '.') . " VND</td>";
              echo "<td>" . number_format($totalPriceExport, 0, ',', '.') . " VND</td>";
              echo "<td>
              <div class='align-middle h-100 d-flex gap-1 justify-content-center align-items-center'>
                      <button type='button' class='btn btn-secondary btn-edit-goods' 
                        data-bs-id='{$export['id']}'
                        data-bs-toggle='modal' 
                        data-bs-target='#updateExportModal'>
                          <i class='fa-solid fa-pen-to-square'></i>
                      </button>
                      <button type='button' class='btn btn-danger btn-delete-goods' 
                        data-bs-id='{$export['id']}' 
                        data-bs-toggle='modal' 
                        data-bs-target='#deleteExportModal'>
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

    <!-- Modal Delete export -->
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
        const exportData = exportList.find(item => item.id == id)

        // Update the modal's content.
        const exportIdInput = document.getElementById('export_id')
        const remainingWeightInput = document.getElementById('remaining_weight_inventory')
        const customerSelect = document.getElementById('customer')
        const exportWeightInput = document.getElementById('export_weight')
        const lostWeightInput = document.getElementById('lost_weight')
        const imagesInput = document.getElementById('images')

        let inventoryId = null;
        let inventoryData = null;
        importExportInventory.find(item => {
          if (item.export_list) {
            item.export_list.forEach(exportItem => {
              if (exportItem.id == exportData.id) {
                inventoryId = item.id;
                return;
              }
            });
          }
        })

        const formData = new FormData();
        formData.append('id', inventoryId);
        fetch(`backend/getInventoryById.php`, {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              inventoryData = data.data;

              // Fill data to form
              exportIdInput.value = id
              remainingWeightInput.value = inventoryData.tonCuoi + exportData.export_weight
              customerSelect.value = exportData.customer
              exportWeightInput.value = exportData.export_weight
              lostWeightInput.value = exportData.lost_weight
            } else {
              console.error('Error fetching inventory data:', data.message);
            }
          })
          .catch(error => {
            console.error('Error fetching inventory data:', error);
          });

        // Display image
        const imagePreview = document.getElementById('image-preview-export')
        imagePreview.innerHTML = ''
        exportData.images.forEach(image => {
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

      let inventoryId = null;
      let exportData = null;
      importExportInventory.find(item => {
        if (item.export_list) {
          item.export_list.forEach(exportItem => {
            if (exportItem.id == id) {
              inventoryId = item.id;
              exportData = exportItem;
              return;
            }
          });
        }

      });

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
      formData.append('inventory_id', inventoryId);
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

      let inventoryId = null;
      importExportInventory.find(item => {
        if (item.export_list) {
          item.export_list.forEach(exportItem => {
            if (exportItem.id == id) {
              inventoryId = item.id;
              return;
            }
          });
        }
      });

      // Call API to delete export
      const response = await fetch(`backend/delete_export.php?export_id=${id}&inventory_id=${inventoryId}`, {
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