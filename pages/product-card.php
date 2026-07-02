<?php
$productId = $product['p_id'];
$category = getCategoryName($product['ecat_id']);
$categoryName = $category['ecat_name'] ?? '';
$featuredBadge = !empty($product['p_is_featured']) ? '<span class="badge soft-pill">' . e(t('featured')) . '</span>' : '';
$availability = (int)$product['p_qty'] > 0 ? '<span class="stock-pill in-stock">' . e(t('in_stock')) . '</span>' : '<span class="stock-pill out-stock">' . e(t('out_of_stock')) . '</span>';
?>
<div class="col-lg-3 col-md-4 col-sm-6 reveal">
  <div class="card card-hover product-card h-100">
    <div class="product-card-media">
      <img src="<?php echo getProductImage($product['p_featured_photo']); ?>" alt="<?php echo e($product['p_name']); ?>">
      <div class="product-card-overlay">
        <a href="product.php?id=<?php echo (int)$productId; ?>" class="action-pill view-pill">View</a>
      </div>
    </div>
    <div class="card-body d-flex flex-column">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge soft-pill"><?php echo e($categoryName); ?></span>
        <?php echo $featuredBadge; ?>
      </div>
      <h5 class="fw-semibold mb-2"><?php echo e($product['p_name']); ?></h5>
      <p class="text-muted small mb-3"><?php echo e(excerpt($product['p_short_description'], 95)); ?></p>
      <div class="d-flex justify-content-between align-items-center small text-muted mb-3 px-1">
        <span><?php echo $availability; ?></span>
        <span><?php echo e(t('qty')); ?>: <?php echo (int)$product['p_qty']; ?></span>
      </div>
      <div class="mt-auto d-flex gap-2 flex-wrap align-items-center product-actions">
        <a href="javascript:void(0);" class="btn btn-dark btn-sm add-to-cart" data-product-id="<?php echo (int)$productId; ?>"><?php echo t('add_to_cart'); ?></a>
        <a href="wishlist.php?action=add&id=<?php echo (int)$productId; ?>" class="btn btn-outline-danger btn-sm action-icon" title="Wishlist"><i class="fa fa-heart"></i></a>
        <a href="compare.php?action=add&id=<?php echo (int)$productId; ?>" class="btn btn-outline-secondary btn-sm action-icon" title="Compare"><i class="fa fa-balance-scale"></i></a>
      </div>
    </div>
  </div>
</div>
