<?php
include 'config/connection.php';
$title  = 'Categories';
$active = 'categories.php';
$error  = '';

// Add a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        try {
            $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)")
                ->execute([$name, trim($_POST['description'] ?? '')]);
            header('Location: categories.php?msg=Category+added');
            exit;
        } catch (PDOException $ex) {
            $error = 'That category name already exists.';
        }
    }
}

$rows = $pdo->query("SELECT c.category_id, c.name, c.description, COUNT(p.product_id) AS total
                     FROM categories c LEFT JOIN products p ON p.category_id = c.category_id
                     GROUP BY c.category_id ORDER BY c.name")->fetchAll();
include 'includes/header.php';
?>
<?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="row">
  <div class="col-md-4">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Add Category</h3></div>
      <form method="post">
        <div class="card-body">
          <div class="form-group"><label for="name">Name *</label>
            <input type="text" class="form-control" id="name" name="name" required></div>
          <div class="form-group"><label for="description">Description</label>
            <input type="text" class="form-control" id="description" name="description"></div>
        </div>
        <div class="card-footer"><button type="submit" class="btn btn-success">Save</button></div>
      </form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h3 class="card-title">All Categories</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped">
          <thead><tr><th>Name</th><th>Description</th><th>Products</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr><td><?= e($r['name']) ?></td><td><?= e($r['description']) ?></td>
                  <td><span class="badge badge-info"><?= $r['total'] ?></span></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
