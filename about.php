<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('about');
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('about'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$page = $pdo->query('SELECT * FROM tbl_page LIMIT 1')->fetch();
?>
<div class="card card-hover p-4">
  <h2 class="fw-bold mb-3"><?php echo t('about_koshi_supplier'); ?></h2>
  <div class="text-muted"><?php echo $page['about_content'] ?? ''; ?></div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
