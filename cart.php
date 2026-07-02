<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = 'Cart';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.');
        header('Location: cart.php');
        exit;
    }

    if ($_POST['action'] === 'update') {
        foreach ($_POST['qty'] as $productId => $qty) {
            $id = (int)$productId;
            $quantity = max(1, (int)$qty);
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = $quantity;
                $_SESSION['cart'][$id]['notes'] = trim($_POST['notes'][$id] ?? '');
            }
        }
        setFlash('success', 'Cart updated.');
    }

    if ($_POST['action'] === 'clear') {
        unset($_SESSION['cart']);
        setFlash('success', 'Cart cleared.');
    }

    header('Location: cart.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
        setFlash('success', 'Item removed from cart.');
    }
    header('Location: cart.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $id = (int)($_GET['id'] ?? 0);
    $qty = max(1, (int)($_GET['qty'] ?? 1));
    if ($id) {
        $stmt = $pdo->prepare('SELECT p_id, p_name, p_featured_photo FROM tbl_product WHERE p_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] += $qty;
            } else {
                $_SESSION['cart'][$id] = ['product_id' => $product['p_id'], 'product_name' => $product['p_name'], 'photo' => $product['p_featured_photo'], 'quantity' => $qty, 'notes' => ''];
            }
            setFlash('success', 'Added to cart.');
        }
    }
    header('Location: cart.php');
    exit;
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'url' => BASE_URL],
    ['label' => 'Cart', 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$cartItems = []; if (!empty($_SESSION['cart'])) { foreach ($_SESSION['cart'] as $id => $item) { $cartItems[] = $item; } }
?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Your enquiry cart</h3>
        <form method="post">
          <input type="hidden" name="action" value="clear">
          <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
          <button class="btn btn-outline-secondary btn-sm">Clear</button>
        </form>
      </div>
      <?php if ($cartItems) { ?>
      <form method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr><th>Product</th><th>Qty</th><th>Notes</th><th>Remove</th></tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item) { ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?php echo getProductImage($item['photo']); ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:0.75rem;">
                      <div>
                        <div class="fw-semibold"><?php echo e($item['product_name']); ?></div>
                        <div class="text-muted small">No pricing shown in checkout</div>
                      </div>
                    </div>
                  </td>
                  <td><input type="number" class="form-control" name="qty[<?php echo (int)$item['product_id']; ?>]" value="<?php echo (int)$item['quantity']; ?>" min="1"></td>
                  <td><input type="text" class="form-control" name="notes[<?php echo (int)$item['product_id']; ?>]" value="<?php echo e($item['notes']); ?>" placeholder="Any request?"></td>
                  <td><a href="cart.php?action=remove&id=<?php echo (int)$item['product_id']; ?>" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></a></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between mt-4">
          <a href="products.php" class="btn btn-outline-dark">Continue shopping</a>
          <button class="btn btn-dark">Update cart</button>
        </div>
      </form>
      <?php } else { ?><div class="alert alert-light rounded-4">Your enquiry cart is empty.</div><?php } ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3">Checkout enquiry</h4>
      <p class="text-muted">Provide your contact details and we will follow up with the order request.</p>
      <a href="checkout.php" class="btn btn-dark w-100">Proceed to checkout</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
