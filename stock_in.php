<?php
include 'config/connection.php';
$title   = 'Stock In';
$active  = 'stock_in.php';
$error   = '';
$staffId = 1;   // Demo: the owner (no login screen in this design)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['product_id'];
    $sid = (int)$_POST['supplier_id'];
    $qty = (int)$_POST['quantity'];
    if (!$pid || !$sid || $qty < 1) {
        $error = 'Choose a product, a supplier and a quantity of 1 or more.';
    } else {
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO stock_receipts (product_id, supplier_id, staff_id, quantity) VALUES (?,?,?,?)")
            ->execute([$pid, $sid, $staffId, $qty]);
        $pdo->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE product_id = ?")->execute([$qty, $pid]);
        $pdo->commit();
        header('Location: stock_in.php?msg=Stock+received');
        exit;
    }
}

$products  = $pdo->query("SELECT product_id, name, stock_qty FROM products ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name")->fetchAll();
$recent    = $pdo->query("SELECT r.received_date, r.quantity, p.name AS product, s.name AS supplier, st.full_name
                          FROM stock_receipts r
                          JOIN products p ON p.product_id = r.product_id
                          JOIN suppliers s ON s.supplier_id = r.supplier_id
                          JOIN staff st ON st.staff_id = r.staff_id
                          ORDER BY r.receipt_id DESC LIMIT 10")->fetchAll();
include 'includes/header.php';
?>
<?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="row">
  <div class="col-md-4">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Receive Stock</h3></div>
      <form method="post">
        <div class="card-body">
          <div class="form-group"><label for="product_id">Product *</label>
            <select class="form-control" id="product_id" name="product_id" required>
              <option value="">Select product</option>
              <?php foreach ($products as $p): ?>
                <option value="<?= $p['product_id'] ?>"><?= e($p['name']) ?> (now <?= $p['stock_qty'] ?>)</option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group"><label for="supplier_id">Supplier *</label>
            <select class="form-control" id="supplier_id" name="supplier_id" required>
              <option value="">Select supplier</option>
              <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['supplier_id'] ?>"><?= e($s['name']) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group"><label for="quantity">Quantity received *</label>
            <input type="number" min="1" class="form-control" id="quantity" name="quantity" required></div>
        </div>
        <div class="card-footer"><button type="submit" class="btn btn-success">Add to Stock</button></div>
      </form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Recent Deliveries</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped">
          <thead><tr><th>Date</th><th>Product</th><th>Qty</th><th>Supplier</th><th>Received by</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $r): ?>
              <tr><td><?= e($r['received_date']) ?></td><td><?= e($r['product']) ?></td><td>+<?= $r['quantity'] ?></td>
                  <td><?= e($r['supplier']) ?></td><td><?= e($r['full_name']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="5" class="text-center text-muted">No deliveries yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
