<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/static-pages.php';

$slug = 'contact';
$lang = defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE;
$page = null;
$pages = [];
$page_file = __DIR__ . '/../storage/index_static_pages.json';
if (file_exists($page_file)) {
    $json = json_decode(file_get_contents($page_file), true);
    $pages = $json['pages'] ?? [];
}
foreach ($pages as $p) {
    if (($p['slug_' . $lang] ?? '') === $slug && ($p['status'] ?? '') === 'published') {
        $page = $p;
        break;
    }
}
if (!$page) {
    $page = [
        'meta_title_en' => 'Contact',
        'meta_title_fr' => 'Contact',
        'meta_description_en' => '<h1>Contact Us</h1><p>We\'d love to hear from you!</p>',
        'meta_description_fr' => '<h1>Contactez-nous</h1><p>Nous aimerions avoir de vos nouvelles !</p>'
    ];
}
$meta_title = $page['meta_title_' . $lang] ?? $page['meta_title_en'];
$meta_description = $page['meta_description_' . $lang] ?? $page['meta_description_en'];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_site_url(); ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <div class="container">
        <div class="contact-page">
            <div class="contact-content">
                <?php echo $content; ?>
            </div>
            <div class="contact-form">
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" rows="5"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
