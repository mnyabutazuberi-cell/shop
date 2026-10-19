<?php
include 'config/connection.php';
$title  = 'Add / Edit Product';
$active = 'products.php';
$error  = '';

$id = (int)($_GET['id'] ?? 0);

// Empty values for a new product
$p = ['sku'=>'','name'=>'','category_id'=>'','supplier_id'=>'','cost_price'=>'','unit_price'=>'','stock_qty'=>0,'reorder_level'=>5];

// If editing, load the existing product
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch() ?: $p;
}

// When the form is sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = array_merge($p, [
        'sku' => trim($_POST['sku']), 'name' => trim($_POST['name']),
        'category_id' => (int)$_POST['category_id'], 'supplier_id' => (int)$_POST['supplier_id'],
        'cost_price' => (float)$_POST['cost_price'], 'unit_price' => (float)$_POST['unit_price'],
        'stock_qty' => (int)$_POST['stock_qty'], 'reorder_level' => (int)$_POST['reorder_level'],
    ]);

    if ($p['sku'] === '' || $p['name'] === '' || !$p['category_id'] || !$p['supplier_id']) {
        $error = 'Please fill in all fields marked with *.';
    } elseif ($p['unit_price'] < $p['cost_price']) {
        $error = 'Selling price must not be lower than cost price.';
    } elseif ($p['stock_qty'] < 0) {
        $error = 'Stock cannot be negative.';
    } else {
        try {
            $data = [$p['category_id'], $p['supplier_id'], $p['sku'], $p['name'],
                     $p['cost_price'], $p['unit_price'], $p['stock_qty'], $p['reorder_level']];
            if ($id) {
                $sql = "UPDATE products SET category_id=?, supplier_id=?, sku=?, name=?, cost_price=?,
                        unit_price=?, stock_qty=?, reorder_level=? WHERE product_id=?";
                $data[] = $id;
            } else {
                $sql = "INSERT INTO products (category_id, supplier_id, sku, name, cost_price,
                        unit_price, stock_qty, reorder_level) VALUES (?,?,?,?,?,?,?,?)";
            }
            $pdo->prepare($sql)->execute($data);
            header('Location: products.php?msg=Product+saved+successfully');
            exit;
        } catch (PDOException $ex) {
            $error = 'Could not save. The SKU may already exist.';
        }
    }
}

$categories = $pdo->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll();
$suppliers  = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name")->fetchAll();

include 'includes/header.php';
?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="card card-primary">
  <div class="card-header"><h3 class="card-title"><?= $id ? 'Edit Product' : 'New Product Details' ?></h3></div>
  <form method="post">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 form-group">
          <label for="name">Product name *</label>
          <input type="text" class="form-control" id="name" name="name" value="<?= e($p['name']) ?>" placeholder="e.g. Sugar 1kg" required>
        </div>
        <div class="col-md-6 form-group">
          <label for="sku">SKU (unique code) *</label>
          <input type="text" class="form-control" id="sku" name="sku" value="<?= e($p['sku']) ?>" placeholder="e.g. P001" required>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 form-group">
          <label for="category_id">Category *</label>
          <select class="form-control" id="category_id" name="category_id" required>
            <option value="">Select category</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['category_id'] ?>" <?= $p['category_id'] == $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6 form-group">
          <label for="supplier_id">Supplier *</label>
          <select class="form-control" id="supplier_id" name="supplier_id" required>
            <option value="">Select supplier</option>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?= $s['supplier_id'] ?>" <?= $p['supplier_id'] == $s['supplier_id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3 form-group">
          <label for="cost_price">Cost price</label>
          <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">TSh</span></div>
            <input type="number" step="0.01" min="0" class="form-control" id="cost_price" name="cost_price" value="<?= e($p['cost_price']) ?>" required>
          </div>
        </div>
        <div class="col-md-3 form-group">
          <label for="unit_price">Selling price</label>
          <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">TSh</span></div>
            <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" value="<?= e($p['unit_price']) ?>" required>
          </div>
        </div>
        <div class="col-md-3 form-group">
          <label for="stock_qty">Stock</label>
          <input type="number" min="0" class="form-control" id="stock_qty" name="stock_qty" value="<?= e($p['stock_qty']) ?>">
        </div>
        <div class="col-md-3 form-group">
          <label for="reorder_level">Reorder at</label>
          <input type="number" min="0" class="form-control" id="reorder_level" name="reorder_level" value="<?= e($p['reorder_level']) ?>">
        </div>
      </div>
      <div class="callout callout-warning mb-0"><p>Selling price must not be lower than cost price.</p></div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-success">Save</button>
      <a href="products.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
