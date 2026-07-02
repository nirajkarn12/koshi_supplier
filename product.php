<?php
require_once __DIR__ . '/inc/functions.php';
$productId = (int)($_GET['id'] ?? 0);
if (!$productId) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM tbl_product WHERE p_id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    header('Location: products.php');
    exit;
}

$pdo->prepare('UPDATE tbl_product SET p_total_view = p_total_view + 1 WHERE p_id = ?')->execute([$productId]);
$gallery = getProductGallery($productId);
$category = getCategoryName($product['ecat_id']);
$related = $pdo->prepare('SELECT p_id, p_name, p_featured_photo, p_short_description, p_qty, ecat_id FROM tbl_product WHERE p_is_active = 1 AND ecat_id = ? AND p_id != ? ORDER BY p_id DESC LIMIT 4');
$related->execute([$product['ecat_id'], $productId]);
$relatedProducts = $related->fetchAll();
$pageTitle = $product['p_name'];
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'url' => BASE_URL],
    ['label' => 'Shop', 'url' => BASE_URL . 'products.php'],
    ['label' => $product['p_name'], 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row g-5">
  <div class="col-lg-6">
    <div class="card card-hover p-3">
      <img src="<?php echo getProductImage($product['p_featured_photo']); ?>" alt="<?php echo e($product['p_name']); ?>" class="img-fluid rounded-4 mb-3" style="height:450px; object-fit:cover; width:100%;">
      <div class="row g-3">
        <?php foreach ($gallery as $photo) { ?>
          <div class="col-3"><img src="<?php echo $photo['photo']; ?>" alt="" class="img-fluid rounded-3 gallery-thumb"></div>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="badge bg-dark"><?php echo e($category['ecat_name'] ?? 'Category'); ?></span>
        <?php if (!empty($product['p_is_featured'])) { ?><span class="badge bg-warning text-dark">Featured</span><?php } ?>
      </div>
      <h1 class="fw-bold mb-3"><?php echo e($product['p_name']); ?></h1>
      <p class="text-muted mb-4"><?php echo e($product['p_short_description']); ?></p>
      <div class="mb-4">
        <div class="fw-semibold mb-2">Availability</div>
        <div class="text-success"><?php echo (int)$product['p_qty'] > 0 ? 'In stock' : 'Out of stock'; ?></div>
      </div>
      <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="cart.php?action=add&id=<?php echo (int)$productId; ?>&qty=1" class="btn btn-dark">Add to cart</a>
        <a href="wishlist.php?action=add&id=<?php echo (int)$productId; ?>" class="btn btn-outline-danger"><i class="fa fa-heart me-2"></i>Wishlist</a>
        <a href="compare.php?action=add&id=<?php echo (int)$productId; ?>" class="btn btn-outline-secondary"><i class="fa fa-balance-scale me-2"></i>Compare</a>
      </div>
      <div class="border-top pt-4">
        <p class="mb-2"><strong>Category:</strong> <?php echo e($category['ecat_name'] ?? ''); ?></p>
        <p class="mb-0"><strong>Availability:</strong> <?php echo (int)$product['p_qty'] > 0 ? 'Available' : 'Unavailable'; ?></p>
      </div>
    </div>
  </div>
</div>
<div class="row g-4 mt-3">
  <div class="col-12">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3">Description</h4>
      <div class="text-muted"><?php echo $product['p_description'] ? $product['p_description'] : '<p>No description available.</p>'; ?></div>
      <h4 class="fw-bold mt-4 mb-3">Features</h4>
      <div class="text-muted"><?php echo $product['p_feature'] ? $product['p_feature'] : '<p>No features available.</p>'; ?></div>
    </div>
  </div>
</div>
<?php if ($relatedProducts) { ?>
<div class="mt-5">
  <div class="section-title">Related Products</div>
  <div class="row g-4">
    <?php foreach ($relatedProducts as $relatedProduct) { include __DIR__ . '/pages/product-card.php'; } ?>
  </div>
</div>
<?php } ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
