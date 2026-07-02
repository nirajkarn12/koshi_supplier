<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('blog');
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('blog'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

$postId = (int)($_GET['id'] ?? 0);
if ($postId) {
    $stmt = $pdo->prepare('SELECT * FROM tbl_post WHERE post_id = ? LIMIT 1');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    if (!$post) {
        header('Location: blog.php');
        exit;
    }
    ?>
    <div class="card card-hover p-4">
      <h2 class="fw-bold mb-3"><?php echo e($post['post_title']); ?></h2>
      <div class="text-muted"><?php echo $post['post_content']; ?></div>
    </div>
    <?php
} else {
    $posts = $pdo->query('SELECT * FROM tbl_post ORDER BY post_id DESC')->fetchAll();
    ?>
    <div class="row g-4">
      <?php foreach ($posts as $post) { ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-hover">
            <img src="<?php echo getProductImage($post['photo']); ?>" alt="" class="img-fluid" style="height:220px; object-fit:cover; width:100%;">
            <div class="card-body">
              <h5 class="fw-bold"><?php echo e($post['post_title']); ?></h5>
              <p class="text-muted small"><?php echo excerpt(strip_tags($post['post_content']), 140); ?></p>
              <a href="blog.php?id=<?php echo (int)$post['post_id']; ?>" class="btn btn-dark btn-sm"><?php echo t('read_more'); ?></a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    <?php
}
include __DIR__ . '/inc/footer.php';
