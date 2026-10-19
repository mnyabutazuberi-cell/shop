<?php
require 'config/connection.php';
$title  = 'Products';
$active = 'products.php';
$error  = '';

// Delete a product (sent by the modal form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $pdo->prepare("DELETE FROM products WHERE product_id = ?")->execute([$_POST['delete_id']]);
        header('Location: products.php?msg=Product+deleted');
        exit;
    } catch (PDOException $ex) {
        $error = 'This product is used in sales or stock records, so it cannot be deleted.';
    }
}

// Search and filter
$q    = trim($_GET['q'] ?? '');
$cat  = (int)($_GET['category'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;

$where  = " WHERE (p.name LIKE ? OR p.sku LIKE ?) ";
$params = ["%$q%", "%$q%"];
if ($cat > 0) { $where .= " AND p.category_id = ? "; $params[] = $cat; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$offset = ($page - 1) * $per;

$stmt = $pdo->prepare("SELECT p.*, c.name AS category FROM products p
                       JOIN categories c ON c.category_id = p.category_id
                       $where ORDER BY p.name LIMIT $per OFFSET $offset");
$stmt->execute($params);
$products   = $stmt->fetchAll();
$categories = $pdo->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll();

include 'includes/header.php';
?>
<?php if (!empty($_GET['msg'])): ?>
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button><?= e($_GET['msg']) ?>
  </div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <form class="form-inline" method="get">
      <input class="form-control mr-2 mb-2" type="search" name="q" value="<?= e($q) ?>" placeholder="Search name or SKU">
      <select class="form-control mr-2 mb-2" name="category">
        <option value="0">All categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['category_id'] ?>" <?= $cat == $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary mr-2 mb-2" type="submit">Search</button>
      <a class="btn btn-secondary mb-2" href="products.php">Reset</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">All Products (<?= $total ?>)</h3>
    <div class="card-tools">
      <a href="product_form.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add Product</a>
    </div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-striped">
      <thead>
        <tr><th>SKU</th><th>Name</th><th>Category</th><th>Price (TSh)</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= e($p['sku']) ?></td>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['category']) ?></td>
            <td><?= money($p['unit_price']) ?></td>
            <td><?= $p['stock_qty'] ?></td>
            <td><?= stock_badge($p['stock_qty'], $p['reorder_level']) ?></td>
            <td>
              <a href="product_form.php?id=<?= $p['product_id'] ?>" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></a>
              <button class="btn btn-danger btn-xs btn-delete" data-id="<?= $p['product_id'] ?>"
                      data-name="<?= e($p['name']) ?>" data-toggle="modal" data-target="#deleteModal">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="7" class="text-center text-muted">No products found</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer clearfix">
    <ul class="pagination pagination-sm m-0 float-right">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?q=<?= urlencode($q) ?>&category=<?= $cat ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post">
      <div class="modal-header"><h5 class="modal-title">Delete product?</h5></div>
      <div class="modal-body">Delete <strong id="delName"></strong>? This cannot be undone.
        <input type="hidden" name="delete_id" id="delId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete</button>
      </div>
    </form>
  </div>
</div>

<?php
$extraJs = <<<'JS'
  // Put the clicked product's id and name into the modal
  $('.btn-delete').on('click', function () {
    $('#delId').val($(this).data('id'));
    $('#delName').text($(this).data('name'));
  });
JS;
include 'includes/footer.php';
