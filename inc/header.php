<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/breadcrumbs.php';
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle ?? SITE_NAME); ?> | <?php echo e(SITE_NAME); ?></title>
    <meta name="description" content="Modern candle, resin and craft supplies storefront built from the existing database.">
    <meta name="theme-color" content="#111827">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>css/style.css">
</head>
<body>
<div class="page-loader" id="pageLoader">
    <div class="spinner"></div>
</div>
<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center small">
        <div class="d-flex flex-wrap gap-3">
            <span><i class="fa fa-phone me-2"></i><?php echo e(getSiteSetting('contact_phone', '+977 9869224134')); ?></span>
            <span><i class="fa fa-envelope me-2"></i><?php echo e(getSiteSetting('contact_email', 'contact@sastikatrading.com.np')); ?></span>
        </div>
        <div class="d-flex flex-wrap gap-2 social-links">
            <?php foreach (getSocialLinks() as $social) { ?>
                <a href="<?php echo e($social['url']); ?>" target="_blank" rel="noreferrer" class="social-link" aria-label="<?php echo e($social['name']); ?>">
                    <i class="<?php echo e($social['icon']); ?>"></i>
                </a>
            <?php } ?>
        </div>
    </div>
</div>
<header class="site-header">
    <nav class="navbar navbar-expand-lg container py-3">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo getProductImage('logo.jpg'); ?>" alt="Brand logo">
            <span>koshi supplier</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>"><?php echo t('home'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>products.php"><?php echo t('shop'); ?></a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown"><?php echo t('categories'); ?></a>
                    <ul class="dropdown-menu mega-menu p-3">
                        <?php foreach (getTopCategories() as $top) { ?>
                            <li class="dropdown-header fw-bold text-dark"><?php echo e($top['tcat_name']); ?></li>
                            <?php foreach (getMidCategories($top['tcat_id']) as $mid) { ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>category.php?id=<?php echo (int)$mid['mcat_id']; ?>"><?php echo e($mid['mcat_name']); ?></a></li>
                            <?php } ?>
                            <li><hr class="dropdown-divider"></li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about.php"><?php echo t('about'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>blog.php"><?php echo t('blog'); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php"><?php echo t('contact'); ?></a></li>
            </ul>
            <form class="d-flex me-3 position-relative search-shell" role="search" action="<?php echo BASE_URL; ?>search.php" method="get">
                <div class="input-group input-group-sm">
                    <input class="form-control" id="headerSearchInput" type="search" name="q" placeholder="<?php echo t('search_products'); ?>" aria-label="Search" autocomplete="off">
                    <button class="btn btn-dark" type="submit"><i class="fa fa-search"></i></button>
                </div>
                <div id="searchResults" class="position-absolute top-100 start-0 w-100 bg-white rounded-4 shadow mt-2 p-2" style="z-index:1000; display:none;"></div>
            </form>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="btn-group btn-group-sm language-switcher" role="group" aria-label="Language switcher">
                    <a href="<?php echo BASE_URL; ?>?lang=en" class="btn btn-outline-secondary btn-sm <?php echo getCurrentLang() === 'en' ? 'active' : ''; ?>" aria-label="English" title="English"><img src="<?php echo ASSET_URL; ?>images/flags/gb.svg" alt="English" class="lang-flag"></a>
                    <a href="<?php echo BASE_URL; ?>?lang=ne" class="btn btn-outline-secondary btn-sm <?php echo getCurrentLang() === 'ne' ? 'active' : ''; ?>" aria-label="नेपाली" title="नेपाली"><img src="<?php echo ASSET_URL; ?>images/flags/np.svg" alt="नेपाली" class="lang-flag"></a>
                    <a href="<?php echo BASE_URL; ?>?lang=hi" class="btn btn-outline-secondary btn-sm <?php echo getCurrentLang() === 'hi' ? 'active' : ''; ?>" aria-label="हिन्दी" title="हिन्दी"><img src="<?php echo ASSET_URL; ?>images/flags/in.svg" alt="हिन्दी" class="lang-flag"></a>
                </div>
                <a href="<?php echo BASE_URL; ?>compare.php" class="icon-pill position-relative">
                    <i class="fa fa-balance-scale"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo compareCount(); ?></span>
                </a>
                <a href="<?php echo BASE_URL; ?>wishlist.php" class="icon-pill position-relative">
                    <i class="fa fa-heart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo wishCount(); ?></span>
                </a>
                <a href="<?php echo BASE_URL; ?>cart.php" class="icon-pill position-relative">
                    <i class="fa fa-shopping-bag"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo cartCount(); ?></span>
                </a>
                <?php if (isLoggedIn()) { ?>
                    <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-dark btn-sm"><?php echo t('account'); ?></a>
                <?php } else { ?>
                    <a href="<?php echo BASE_URL; ?>account/login.php" class="btn btn-dark btn-sm"><?php echo t('login'); ?></a>
                <?php } ?>
            </div>
        </div>
    </nav>
</header>
<main class="pb-5">
    <div class="container py-4">
        <?php echo renderFlash(); ?>
