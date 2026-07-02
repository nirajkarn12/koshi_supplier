<?php
require_once __DIR__ . '/../inc/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}
$pageTitle = 'Profile';
$customer = currentCustomer();
include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'url' => BASE_URL],
    ['label' => 'Profile', 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3">My account</h4>
      <div class="d-grid gap-2">
        <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-dark btn-sm">Profile</a>
        <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-outline-secondary btn-sm">Order history</a>
        <a href="<?php echo BASE_URL; ?>account/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
      </div>
      <hr>
      <p class="mb-1"><strong>Name:</strong> <?php echo e($customer['cust_name']); ?></p>
      <p class="mb-1"><strong>Email:</strong> <?php echo e($customer['cust_email']); ?></p>
      <p class="mb-1"><strong>Phone:</strong> <?php echo e($customer['cust_phone']); ?></p>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3">Profile details</h4>
      <p class="text-muted">Your account is linked to the existing customer table and can be used to view order requests and manage your enquiries.</p>
      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <div class="border rounded-4 p-3">
            <div class="small text-muted mb-1">Customer name</div>
            <div class="fw-semibold"><?php echo e($customer['cust_name']); ?></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="border rounded-4 p-3">
            <div class="small text-muted mb-1">Email address</div>
            <div class="fw-semibold"><?php echo e($customer['cust_email']); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
