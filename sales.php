<?php
include 'config/connection.php';
$title  = 'Sales History';
$active = 'sales.php';

$sales = $pdo->query("SELECT s.sale_id, s.sale_date, s.payment_method, s.total_amount, st.full_name,
                      (SELECT COUNT(*) FROM sale_items i WHERE i.sale_id = s.sale_id) AS items
                      FROM sales s JOIN staff st ON st.staff_id = s.staff_id
                      ORDER BY s.sale_date DESC, s.sale_id DESC LIMIT 50")->fetchAll();

// Show one sale's lines when ?id= is given
$id = (int)($_GET['id'] ?? 0);
$detail = [];
if ($id) {
    $stmt = $pdo->prepare("SELECT p.name, i.quantity, i.unit_price, i.line_total
                           FROM sale_items i JOIN products p ON p.product_id = i.product_id
                           WHERE i.sale_id = ?");
    $stmt->execute([$id]);
    $detail = $stmt->fetchAll();
}
include 'includes/header.php';
?>
<?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>

<?php if ($id && $detail): ?>
<div class="callout callout-info"><h5>Receipt #<?= $id ?></h5>
  <table class="table table-sm mb-0">
    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Line total</th></tr></thead>
    <tbody>
      <?php foreach ($detail as $d): ?>
        <tr><td><?= e($d['name']) ?></td><td><?= $d['quantity'] ?></td><td><?= money($d['unit_price']) ?></td><td><?= money($d['line_total']) ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Recent Sales</h3>
    <div class="card-tools"><a href="new_sale.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> New Sale</a></div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Sale #</th><th>Date</th><th>Cashier</th><th>Items</th><th>Payment</th><th>Total (TSh)</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($sales as $s): ?>
          <tr>
            <td><?= $s['sale_id'] ?></td>
            <td><?= e(date('d M Y H:i', strtotime($s['sale_date']))) ?></td>
            <td><?= e($s['full_name']) ?></td>
            <td><?= $s['items'] ?></td>
            <td><span class="badge badge-secondary"><?= e($s['payment_method']) ?></span></td>
            <td><?= money($s['total_amount']) ?></td>
            <td><a href="sales.php?id=<?= $s['sale_id'] ?>" class="btn btn-info btn-xs">View</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$sales): ?><tr><td colspan="7" class="text-center text-muted">No sales yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
