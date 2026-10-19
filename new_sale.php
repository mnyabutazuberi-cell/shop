<?php
include 'config/connection.php';
$title  = 'New Sale';
$active = 'new_sale.php';
$error  = '';
$staffId = 2;   // Demo: the cashier "Amina" (no login screen in this design)

$products = $pdo->query("SELECT product_id, name, unit_price, stock_qty FROM products
                         WHERE stock_qty > 0 ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Collect lines; merge the same product chosen twice
    $lines = [];
    foreach ($_POST['product_id'] ?? [] as $i => $pid) {
        $qty = (int)($_POST['qty'][$i] ?? 0);
        if ((int)$pid > 0 && $qty > 0) {
            $lines[(int)$pid] = ($lines[(int)$pid] ?? 0) + $qty;
        }
    }
    $payment = in_array($_POST['payment_method'] ?? '', ['cash','mobile','card']) ? $_POST['payment_method'] : 'cash';

    if (!$lines) {
        $error = 'Add at least one product with a quantity.';
    } else {
        try {
            $pdo->beginTransaction();   // all steps succeed together, or none are saved
            $pdo->prepare("INSERT INTO sales (staff_id, payment_method) VALUES (?, ?)")->execute([$staffId, $payment]);
            $saleId = $pdo->lastInsertId();
            $total  = 0;

            foreach ($lines as $pid => $qty) {
                $row = $pdo->prepare("SELECT name, unit_price, stock_qty FROM products WHERE product_id = ? FOR UPDATE");
                $row->execute([$pid]);
                $p = $row->fetch();
                if (!$p || $p['stock_qty'] < $qty) {
                    throw new Exception('Not enough stock for ' . ($p['name'] ?? 'a product') . '.');
                }
                $lineTotal = $p['unit_price'] * $qty;
                $total    += $lineTotal;
                $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, line_total)
                               VALUES (?,?,?,?,?)")->execute([$saleId, $pid, $qty, $p['unit_price'], $lineTotal]);
                $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE product_id = ?")->execute([$qty, $pid]);
            }
            $pdo->prepare("UPDATE sales SET total_amount = ? WHERE sale_id = ?")->execute([$total, $saleId]);
            $pdo->commit();
            header("Location: sales.php?id=$saleId&msg=Sale+recorded");
            exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $error = $ex->getMessage();
        }
    }
}
include 'includes/header.php';
?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<form method="post">
  <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Sale Items</h3></div>
    <div class="card-body">
      <div id="lines">
        <div class="form-row line mb-2">
          <div class="col-md-7">
            <select class="form-control" name="product_id[]">
              <option value="0">Choose product</option>
              <?php foreach ($products as $p): ?>
                <option value="<?= $p['product_id'] ?>"><?= e($p['name']) ?> - TSh <?= money($p['unit_price']) ?> (<?= $p['stock_qty'] ?> in stock)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3"><input type="number" min="1" class="form-control" name="qty[]" placeholder="Qty"></div>
          <div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-block remove">Remove</button></div>
        </div>
      </div>
      <button type="button" class="btn btn-info btn-sm" id="addLine"><i class="fas fa-plus"></i> Add another product</button>
      <hr>
      <div class="form-group col-md-4 p-0">
        <label for="payment_method">Payment method</label>
        <select class="form-control" id="payment_method" name="payment_method">
          <option value="cash">Cash</option><option value="mobile">Mobile money</option><option value="card">Card</option>
        </select>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-success">Save Sale</button>
      <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
  </div>
</form>

<?php
$extraJs = <<<'JS'
$('#addLine').on('click', function () {
  var copy = $('#lines .line:first').clone();
  copy.find('select').val('0');
  copy.find('input').val('');
  $('#lines').append(copy);
});
$('#lines').on('click', '.remove', function () {
  if ($('#lines .line').length > 1) { $(this).closest('.line').remove(); }
});
JS;
include 'includes/footer.php';
