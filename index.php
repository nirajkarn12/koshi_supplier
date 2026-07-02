<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = 'Home';
include __DIR__ . '/inc/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['newsletter_email'])) {
    $email = trim($_POST['newsletter_email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare('SELECT subs_id FROM tbl_subscriber WHERE subs_email = ? LIMIT 1');
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            $now = date('Y-m-d');
            $stmt = $pdo->prepare('INSERT INTO tbl_subscriber (subs_email, subs_date, subs_date_time, subs_hash, subs_active) VALUES (?, ?, ?, ?, 1)');
            $stmt->execute([$email, $now, date('Y-m-d H:i:s'), bin2hex(random_bytes(8))]);
        }
        $newsletterMessage = t('newsletter_success');
        $newsletterType = 'success';
    } else {
        $newsletterMessage = t('newsletter_invalid_email');
        $newsletterType = 'danger';
    }
}

$featured = $pdo->query('SELECT p.p_id, p.p_name, p.p_short_description, p.p_is_featured, p.p_qty, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1 ORDER BY p.p_is_featured DESC, p.p_id DESC LIMIT 8')->fetchAll();
$latest = $pdo->query('SELECT p.p_id, p.p_name, p.p_short_description, p.p_qty, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1 ORDER BY p.p_id DESC LIMIT 8')->fetchAll();
$popular = $pdo->query('SELECT p.p_id, p.p_name, p.p_short_description, p.p_qty, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1 ORDER BY p.p_total_view DESC, p.p_id DESC LIMIT 8')->fetchAll();
$topCategories = getTopCategories();
$posts = $pdo->query('SELECT post_id, post_title, post_content, photo FROM tbl_post ORDER BY post_id DESC LIMIT 2')->fetchAll();
$settings = $pdo->query('SELECT * FROM tbl_settings LIMIT 1')->fetch();
$heroSlides = $pdo->query('SELECT * FROM tbl_slider ORDER BY id ASC')->fetchAll();
$newsletterEnabled = (int)getSiteSetting('newsletter_on_off', 1);
$newsletterText = getSiteSetting('newsletter_text', t('newsletter_default_text'));
?>
<section class="hero mb-5">
  <div class="hero-slider position-relative">
    <div class="container py-3">
      <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
          <?php if ($heroSlides) { foreach ($heroSlides as $slide) { ?>
            <div class="swiper-slide">
              <div class="row align-items-center g-4 py-4">
                <div class="col-lg-7">
                  <span class="badge bg-white text-dark mb-3"><?php echo !empty($slide['heading']) ? e($slide['heading']) : t('hero_default_badge'); ?></span>
                  <h1 class="display-5 fw-bold mb-3"><?php echo !empty($slide['heading']) ? e($slide['heading']) : t('hero_default_title'); ?></h1>
                  <p class="lead mb-4"><?php echo !empty($slide['content']) ? e($slide['content']) : t('hero_default_text'); ?></p>
                  <div class="d-flex gap-3 flex-wrap">
                    <?php if (!empty($slide['button_text'])) { ?>
                      <a href="<?php echo e($slide['button_url'] ?: 'products.php'); ?>" class="btn btn-light btn-lg"><?php echo e($slide['button_text']); ?></a>
                    <?php } else { ?>
                      <a href="products.php" class="btn btn-light btn-lg"><?php echo t('shop_collection'); ?></a>
                      <a href="contact.php" class="btn btn-outline-light btn-lg"><?php echo t('contact_us_btn'); ?></a>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-lg-5">
                  <div class="hero-media">
                    <img src="<?php echo getProductImage($slide['photo']); ?>" alt="Hero">
                  </div>
                </div>
              </div>
            </div>
          <?php } } else { ?>
            <div class="swiper-slide">
              <div class="row align-items-center g-4 py-4">
                <div class="col-lg-7">
                  <span class="badge bg-white text-dark mb-3"><?php echo t('hero_alt_badge'); ?></span>
                  <h1 class="display-5 fw-bold mb-3"><?php echo t('hero_alt_title'); ?></h1>
                  <p class="lead mb-4"><?php echo t('hero_alt_text'); ?></p>
                  <div class="d-flex gap-3 flex-wrap">
                    <a href="products.php" class="btn btn-light btn-lg"><?php echo t('shop_collection'); ?></a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg"><?php echo t('contact_us_btn'); ?></a>
                  </div>
                </div>
                <div class="col-lg-5">
                  <div class="hero-media">
                    <img src="<?php echo getProductImage('banner.jpg'); ?>" alt="Hero">
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>
</section>
<section class="mb-5">
  <div class="container">
    <div class="section-title"><?php echo t('featured_products'); ?></div>
    <div class="row g-4">
      <?php foreach ($featured as $product) { include __DIR__ . '/pages/product-card.php'; } ?>
    </div>
  </div>
</section>
<section class="mb-5">
  <div class="container">
    <div class="section-title"><?php echo t('popular_products'); ?></div>
    <div class="row g-4">
      <?php foreach ($popular as $product) { include __DIR__ . '/pages/product-card.php'; } ?>
    </div>
  </div>
</section>
<section class="mb-5">
  <div class="container">
    <div class="section-title"><?php echo t('latest_additions'); ?></div>
    <div class="row g-4">
      <?php foreach ($latest as $product) { include __DIR__ . '/pages/product-card.php'; } ?>
    </div>
  </div>
</section>
<section class="mb-5">
  <div class="container">
    <div class="section-title"><?php echo t('browse_by_category'); ?></div>
    <div class="row g-4">
      <?php foreach ($topCategories as $top) { ?>
        <div class="col-md-4">
          <div class="card-hover p-4 h-100">
            <h5 class="fw-bold mb-2"><?php echo e($top['tcat_name']); ?></h5>
            <p class="text-muted small"><?php echo t('discover_category_text'); ?></p>
            <a class="btn btn-outline-dark btn-sm" href="category.php?id=<?php echo (int)$top['tcat_id']; ?>"><?php echo t('explore'); ?></a>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</section>
<section class="mb-5">
  <div class="container">
    <div class="section-title"><?php echo t('visit_us'); ?></div>
    <div class="map-shell">
      <iframe loading="lazy" title="Store location" src="https://www.google.com/maps?q=Kathmandu,Nepal&output=embed"></iframe>
    </div>
  </div>
</section>
<?php if ($newsletterEnabled) { ?>
<section class="mb-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card-hover p-4 h-100">
          <div class="section-title mb-3"><?php echo t('from_the_blog'); ?></div>
          <div class="row g-4">
            <?php foreach ($posts as $post) { ?>
              <div class="col-md-6">
                <div class="card-hover h-100">
                  <img src="<?php echo getProductImage($post['photo']); ?>" alt="" class="img-fluid" style="height:220px; object-fit:cover; width:100%;">
                  <div class="card-body px-3 py-3">
                    <h5 class="fw-bold mb-2"><?php echo e($post['post_title']); ?></h5>
                    <p class="text-muted small mb-3"><?php echo excerpt(strip_tags($post['post_content']), 120); ?></p>
                    <a class="btn btn-dark btn-sm" href="blog.php?id=<?php echo (int)$post['post_id']; ?>"><?php echo t('read_more'); ?></a>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card-hover p-4 h-100">
          <div class="section-title mb-3"><?php echo t('newsletter'); ?></div>
          <p class="text-muted mb-4"><?php echo e($newsletterText); ?></p>
          <?php if (!empty($newsletterMessage)) { ?>
            <div class="alert alert-<?php echo e($newsletterType); ?> rounded-4 mb-3"><?php echo e($newsletterMessage); ?></div>
          <?php } ?>
          <form method="post" class="d-grid gap-3">
            <input type="email" class="form-control" name="newsletter_email" placeholder="<?php echo t('your_email'); ?>" required>
            <button class="btn btn-dark" type="submit"><?php echo t('subscribe'); ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php } else { ?>
<section class="mb-5">
  <div class="container">
    <div class="section-title"><?php echo t('from_the_blog'); ?></div>
    <div class="row g-4">
      <?php foreach ($posts as $post) { ?>
        <div class="col-md-4">
          <div class="card-hover">
            <img src="<?php echo getProductImage($post['photo']); ?>" alt="" class="img-fluid" style="height:220px; object-fit:cover; width:100%;">
            <div class="card-body px-3 py-3">
              <h5 class="fw-bold mb-2"><?php echo e($post['post_title']); ?></h5>
              <p class="text-muted small mb-3"><?php echo excerpt(strip_tags($post['post_content']), 120); ?></p>
              <a class="btn btn-dark btn-sm" href="blog.php?id=<?php echo (int)$post['post_id']; ?>"><?php echo t('read_more'); ?></a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</section>
<?php } ?>
<?php if (!empty($settings['banner_login'])): ?>
<!-- Welcome Popup -->
<div class="modal fade" id="welcomePopup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent shadow-none">

            <button type="button"
                    class="btn-close bg-white rounded-circle position-absolute top-0 end-0 m-2"
                    data-bs-dismiss="modal"
                    aria-label="<?php echo t('close'); ?>"
                    style="z-index:999;"></button>

            <img src="<?php echo getProductImage($settings['banner_login']); ?>"
                 class="img-fluid rounded-4 shadow"
                 alt="<?php echo t('welcome_banner'); ?>">

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Show popup only once per browser session
    if (!sessionStorage.getItem("welcomePopupShown")) {

        var popup = new bootstrap.Modal(document.getElementById('welcomePopup'));
        popup.show();

        sessionStorage.setItem("welcomePopupShown", "true");
    }

});
</script>

<?php endif; ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
