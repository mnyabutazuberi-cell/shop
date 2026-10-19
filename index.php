<?php
include 'config/connection.php';
$title  = 'Dashboard';
$active = 'index.php';

// Numbers for the small boxes
$today = $pdo->query("SELECT COUNT(*) AS c, COALESCE(SUM(total_amount),0) AS r
                      FROM sales WHERE DATE(sale_date) = CURRENT_DATE")->fetch();
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowCount     = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_qty <= reorder_level")->fetchColumn();

// Tables
$recent = $pdo->query("SELECT s.sale_id, s.sale_date, s.total_amount, st.full_name
                       FROM sales s JOIN staff st ON st.staff_id = s.staff_id
                       ORDER BY s.sale_date DESC LIMIT 5")->fetchAll();
$low = $pdo->query("SELECT name, stock_qty, reorder_level FROM products
                    WHERE stock_qty <= reorder_level ORDER BY stock_qty LIMIT 5")->fetchAll();

include 'includes/header.php';
?>
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3><?= $today['c'] ?></h3><p>Sales today</p></div>
      <div class="icon"><i class="fas fa-shopping-cart"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3>TSh <?= money($today['r']) ?></h3><p>Revenue today</p></div>
      <div class="icon"><i class="fas fa-coins"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3><?= $productCount ?></h3><p>Products</p></div>
      <div class="icon"><i class="fas fa-box"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner"><h3><?= $lowCount ?></h3><p>Low stock</p></div>
      <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Recent Sales</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover">
          <thead><tr><th>Sale #</th><th>Date</th><th>Cashier</th><th>Total (TSh)</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $r): ?>
              <tr>
                <td><?= $r['sale_id'] ?></td>
                <td><?= e(date('d M Y H:i', strtotime($r['sale_date']))) ?></td>
                <td><?= e($r['full_name']) ?></td>
                <td><?= money($r['total_amount']) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="4" class="text-center text-muted">No sales yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer"><a href="sales.php">View all sales</a></div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Low-Stock Items</h3></div>
      <div class="card-body">
        <?php foreach ($low as $p):
            $pct   = $p['reorder_level'] > 0 ? round($p['stock_qty'] / ($p['reorder_level'] * 2) * 100) : 0;
            $pct   = max(5, min(100, $pct));
            $color = $p['stock_qty'] <= 0 ? 'danger' : 'warning'; ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between">
              <span><?= e($p['name']) ?></span><small><?= $p['stock_qty'] ?> left</small>
            </div>
            <div class="progress progress-sm">
              <div class="progress-bar bg-<?= $color ?>" style="width: <?= $pct ?>%"></div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$low): ?><p class="text-muted mb-0">All products have enough stock.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
