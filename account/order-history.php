<?php
require_once __DIR__ . '/../inc/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}
$pageTitle = 'Order history';
$customerId = (int)$_SESSION['customer_id'];
$payments = $pdo->prepare('SELECT * FROM tbl_payment WHERE customer_id = ? ORDER BY id DESC');
$payments->execute([$customerId]);
$payments = $payments->fetchAll();
include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'url' => BASE_URL],
    ['label' => 'Order History', 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3">My account</h4>
      <div class="d-grid gap-2">
        <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-outline-secondary btn-sm">Profile</a>
        <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-dark btn-sm">Order history</a>
        <a href="<?php echo BASE_URL; ?>account/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-4">Order history</h3>
      <?php if ($payments) { foreach ($payments as $payment) { ?>
        <div class="border rounded-4 p-3 mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-semibold mb-0">#<?php echo e($payment['payment_id']); ?></h5>
            <span class="badge bg-dark"><?php echo e($payment['payment_status']); ?></span>
          </div>
          <p class="text-muted small mb-2">Date: <?php echo e($payment['payment_date']); ?> | Status: <?php echo e($payment['shipping_status']); ?></p>
          <p class="mb-0 text-muted">Remarks: <?php echo e($payment['notes']); ?></p>
        </div>
      <?php } } else { ?><div class="alert alert-light rounded-4">No orders yet.</div><?php } ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
