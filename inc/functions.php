<?php
require_once __DIR__ . '/../config/database.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function getCurrentLang() {
    $lang = $_SESSION['lang'] ?? 'en';
    $lang = in_array($lang, ['en', 'ne', 'hi'], true) ? $lang : 'en';
    return $lang;
}

function loadLang($key) {
    $lang = getCurrentLang();
    $file = __DIR__ . '/lang/' . $lang . '.php';
    static $cache = [];
    if (!isset($cache[$lang])) {
        $cache[$lang] = file_exists($file) ? require $file : [];
    }
    return $cache[$lang][$key] ?? $key;
}

function t($key) {
    return e(loadLang($key));
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function renderFlash() {
    if (empty($_SESSION['flash'])) {
        return '';
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type = $flash['type'] ?? 'info';
    $message = e($flash['message'] ?? '');

    return '<div class="alert alert-' . $type . ' shadow-sm rounded-4">' . $message . '</div>';
}

function getSiteSetting($field, $default = '') {
    global $pdo;
    static $settings = null;

    if ($settings === null) {
        $settings = $pdo->query('SELECT * FROM tbl_settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (!$settings) {
            $settings = [];
        }
    }

    $fieldName = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    return array_key_exists($fieldName, $settings) && $settings[$fieldName] !== null ? $settings[$fieldName] : $default;
}

function getProductImage($filename) {
    if (empty($filename)) {
        return ASSET_URL . 'images/placeholder.png';
    }

    if (preg_match('#^https?://#i', $filename)) {
        return $filename;
    }

    $cleanName = ltrim(str_replace('\\', '/', (string) $filename), '/');
    $possiblePaths = [];

    if (strpos($cleanName, 'assets/uploads/') === 0 || strpos($cleanName, 'uploads/') === 0) {
        $possiblePaths[] = __DIR__ . '/../' . $cleanName;
    }

    $possiblePaths[] = __DIR__ . '/../assets/uploads/' . $cleanName;
    $possiblePaths[] = __DIR__ . '/../assets/uploads/product_photos/' . $cleanName;
    $possiblePaths[] = __DIR__ . '/../' . $cleanName;

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $relative = ltrim(str_replace(__DIR__ . '/../', '', $path), '/');
            return BASE_URL . $relative;
        }
    }

    return ASSET_URL . 'images/placeholder.png';
}

function getSocialLinks() {
    global $pdo;
    $stmt = $pdo->query('SELECT social_name, social_url FROM tbl_social WHERE social_url IS NOT NULL AND TRIM(social_url) <> "" ORDER BY social_name ASC');
    $rows = $stmt->fetchAll();

    $icons = [
        'Facebook' => 'fab fa-facebook-f',
        'Twitter' => 'fab fa-twitter',
        'Instagram' => 'fab fa-instagram',
        'LinkedIn' => 'fab fa-linkedin-in',
        'YouTube' => 'fab fa-youtube',
        'WhatsApp' => 'fab fa-whatsapp',
        'Pinterest' => 'fab fa-pinterest-p',
        'Google Plus' => 'fab fa-google-plus-g',
        'Snapchat' => 'fab fa-snapchat',
        'Quora' => 'fab fa-quora',
        'Reddit' => 'fab fa-reddit-alien',
    ];

    $links = [];
    foreach ($rows as $row) {
        $name = $row['social_name'] ?? '';
        $url = trim((string) ($row['social_url'] ?? ''));
        if ($url === '') {
            continue;
        }
        $links[] = [
            'name' => $name,
            'url' => $url,
            'icon' => $icons[$name] ?? 'fab fa-link',
        ];
    }

    return $links;
}

function getProductGallery($productId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT photo FROM tbl_product_photo WHERE p_id = ? ORDER BY pp_id ASC');
    $stmt->execute([$productId]);
    $photos = $stmt->fetchAll();

    if (!$photos) {
        return [[ 'photo' => getProductImage($productId . '.jpg') ]];
    }

    $gallery = [];
    foreach ($photos as $photo) {
        $gallery[] = [
            'photo' => getProductImage($photo['photo']),
        ];
    }
    return $gallery;
}

function getCategoryName($ecatId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT e.ecat_name, m.mcat_name, t.tcat_name FROM tbl_end_category e LEFT JOIN tbl_mid_category m ON m.mcat_id = e.mcat_id LEFT JOIN tbl_top_category t ON t.tcat_id = m.tcat_id WHERE e.ecat_id = ?');
    $stmt->execute([$ecatId]);
    return $stmt->fetch();
}

function getTopCategories() {
    global $pdo;
    $stmt = $pdo->prepare('SELECT tcat_id, tcat_name FROM tbl_top_category WHERE show_on_menu = 1 ORDER BY tcat_id ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function getMidCategories($tcatId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT mcat_id, mcat_name FROM tbl_mid_category WHERE tcat_id = ? ORDER BY mcat_id ASC');
    $stmt->execute([$tcatId]);
    return $stmt->fetchAll();
}

function getEndCategories($mcatId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT ecat_id, ecat_name FROM tbl_end_category WHERE mcat_id = ? ORDER BY ecat_id ASC');
    $stmt->execute([$mcatId]);
    return $stmt->fetchAll();
}

function cartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

function wishCount() {
    return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
}

function compareCount() {
    return isset($_SESSION['compare']) ? count($_SESSION['compare']) : 0;
}

function isLoggedIn() {
    return !empty($_SESSION['customer_id']);
}

function currentCustomer() {
    global $pdo;
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM tbl_customer WHERE cust_id = ? LIMIT 1');
    $stmt->execute([$_SESSION['customer_id']]);
    return $stmt->fetch();
}

function verifyPassword($input, $stored) {
    if (password_get_info($stored)['algo'] ?? null) {
        return password_verify($input, $stored);
    }

    if (strlen($stored) === 32) {
        return md5($input) === $stored;
    }

    return $input === $stored;
}

function buildProductUrl($productId) {
    return BASE_URL . 'product.php?id=' . $productId;
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ne', 'hi'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

function excerpt($text, $limit = 120) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . '...';
}

function sortOptions($selected = '') {
    $options = [
        'newest' => t('sort_newest'),
        'featured' => t('sort_featured'),
        'popular' => t('sort_popular'),
        'alphabetical' => t('sort_alphabetical'),
    ];

    $html = '';
    foreach ($options as $value => $label) {
        $active = $selected === $value ? 'selected' : '';
        $html .= '<option value="' . $value . '" ' . $active . '>' . $label . '</option>';
    }
    return $html;
}
