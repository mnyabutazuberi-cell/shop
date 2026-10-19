<?php
require_once __DIR__ . '/functions.php';
$menu = [
    ['index.php',       'Dashboard',     'fa-tachometer-alt'],
    ['products.php',    'Products',      'fa-box'],
    ['categories.php',  'Categories',    'fa-tags'],
    ['suppliers.php',   'Suppliers',     'fa-truck'],
    ['new_sale.php',    'New Sale',      'fa-cash-register'],
    ['sales.php',       'Sales History', 'fa-receipt'],
    ['stock_in.php',    'Stock In',      'fa-dolly'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ShopTrack | <?= e($title) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="assets/css/shoptrack.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link"><span class="brand-text font-weight-bold">ShopTrack</span></a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <?php foreach ($menu as $m): ?>
            <li class="nav-item">
              <a href="<?= $m[0] ?>" class="nav-link <?= ($active === $m[0]) ? 'active' : '' ?>">
                <i class="nav-icon fas <?= $m[2] ?>"></i><p><?= $m[1] ?></p>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1><?= e($title) ?></h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item active"><?= e($title) ?></li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="container-fluid">
