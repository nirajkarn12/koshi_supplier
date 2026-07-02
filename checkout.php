<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = 'Checkout';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.');
        header('Location: checkout.php');
        exit;
    }

    $customerName = trim($_POST['customer_name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $municipality = trim($_POST['municipality'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $deliveryInstructions = trim($_POST['delivery_instructions'] ?? '');

    if ($customerName === '' || $phone === '' || $email === '' || $address === '') {
        setFlash('danger', 'Please complete the required fields.');
        header('Location: checkout.php');
        exit;
    }

    $paymentId = 'ORD-' . date('YmdHis');
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO tbl_payment (customer_id, customer_name, customer_email, payment_date, txnid, paid_amount, card_number, card_cvv, card_month, card_year, bank_transaction_info, payment_method, payment_status, shipping_status, payment_id, subtotal, discount_type, discount_value, discount_amount, vat_percent, vat_amount, grand_total, due_amount, notes, created_at, updated_at, customer_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            isLoggedIn() ? $_SESSION['customer_id'] : 0,
            $customerName,
            $email,
            date('Y-m-d H:i:s'),
            '',
            0,
            '',
            '',
            '',
            '',
            '',
            'enquiry',
            'Pending',
            'Pending',
            $paymentId,
            0,
            'percent',
            0,
            0,
            0,
            0,
            0,
            0,
            $remarks . "\n" . $deliveryInstructions,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            $phone,
        ]);
        $paymentIdDb = $pdo->lastInsertId();

        foreach ($_SESSION['cart'] as $item) {
            $orderStmt = $pdo->prepare('INSERT INTO tbl_order (product_id, product_name, size, color, quantity, unit_price, payment_id, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $orderStmt->execute([
                $item['product_id'],
                $item['product_name'],
                '',
                '',
                $item['quantity'],
                0,
                $paymentId,
                0,
            ]);
        }

        $pdo->commit();
        unset($_SESSION['cart']);
        setFlash('success', 'Your enquiry has been submitted successfully.');
        header('Location: account/order-history.php');
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        setFlash('danger', 'Could not save your enquiry right now.');
        header('Location: checkout.php');
        exit;
    }
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'url' => BASE_URL],
    ['label' => 'Cart', 'url' => BASE_URL . 'cart.php'],
    ['label' => 'Checkout', 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-4">Enquiry details</h3>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-md-6"><label class="form-label">Customer name</label><input class="form-control" name="customer_name" required></div>
        <div class="col-md-6"><label class="form-label">Company</label><input class="form-control" name="company"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
        <div class="col-md-4"><label class="form-label">Province</label><input class="form-control" name="province"></div>
        <div class="col-md-4"><label class="form-label">District</label><input class="form-control" name="district"></div>
        <div class="col-md-4"><label class="form-label">Municipality</label><input class="form-control" name="municipality"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3" required></textarea></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="3"></textarea></div>
        <div class="col-12"><label class="form-label">Delivery instructions</label><textarea class="form-control" name="delivery_instructions" rows="3"></textarea></div>
        <div class="col-12"><button class="btn btn-dark">Submit enquiry</button></div>
      </form>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3">Order request</h4>
      <ul class="list-group list-group-flush">
        <?php foreach ($_SESSION['cart'] as $item) { ?>
          <li class="list-group-item d-flex justify-content-between"><span><?php echo e($item['product_name']); ?></span><span>x<?php echo (int)$item['quantity']; ?></span></li>
        <?php } ?>
      </ul>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
