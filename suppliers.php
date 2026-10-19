<?php
include 'config/connection.php';
$title  = 'Suppliers';
$active = 'suppliers.php';
$error  = '';

// Add a new supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '' || $phone === '') {
        $error = 'Name and phone are required.';
    } else {
        $pdo->prepare("INSERT INTO suppliers (name, phone, email) VALUES (?, ?, ?)")
            ->execute([$name, $phone, trim($_POST['email'] ?? '') ?: null]);
        header('Location: suppliers.php?msg=Supplier+added');
        exit;
    }
}

$rows = $pdo->query("SELECT s.*, COUNT(p.product_id) AS total
                     FROM suppliers s LEFT JOIN products p ON p.supplier_id = s.supplier_id
                     GROUP BY s.supplier_id ORDER BY s.name")->fetchAll();
include 'includes/header.php';
?>
<?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="row">
  <div class="col-md-4">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Add Supplier</h3></div>
      <form method="post">
        <div class="card-body">
          <div class="form-group"><label for="name">Name *</label>
            <input type="text" class="form-control" id="name" name="name" required></div>
          <div class="form-group"><label for="phone">Phone *</label>
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
              <input type="text" class="form-control" id="phone" name="phone" required>
            </div></div>
          <div class="form-group"><label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email"></div>
        </div>
        <div class="card-footer"><button type="submit" class="btn btn-success">Save</button></div>
      </form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h3 class="card-title">All Suppliers</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped">
          <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Products</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr><td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['email']) ?></td>
                  <td><span class="badge badge-info"><?= $r['total'] ?></span></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
