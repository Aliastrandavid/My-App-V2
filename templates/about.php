<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/static-pages.php';

// Détecte le slug et la langue
$slug = 'about';
$lang = defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE;
$page = null;

// Recherche la page dans page.json
$pages = [];
$page_file = __DIR__ . '/../storage/page.json';
if (file_exists($page_file)) {
    $json = json_decode(file_get_contents($page_file), true);
    $pages = $json['posts'] ?? [];
}
foreach ($pages as $p) {
    if (($p['slug_' . $lang] ?? '') === $slug && ($p['status'] ?? '') === 'published') {
        $page = $p;
        break;
    }
}
// Fallback si non trouvé
if (!$page) {
    $page = [
        'title_en' => 'About Us',
        'title_fr' => 'À propos de nous',
        'content_en' => '<h1>About Our Company</h1><p>Welcome to our company page.</p>',
        'content_fr' => '<h1>À propos de notre entreprise</h1><p>Bienvenue sur la page de notre entreprise.</p>',
        'meta_title_en' => '',
        'meta_title_fr' => '',
        'meta_description_en' => '',
        'meta_description_fr' => ''
    ];
}
$title = $page['title_' . $lang] ?? $page['title_en'];
$content = $page['content_' . $lang] ?? $page['content_en'];
$meta_title = $page['meta_title_' . $lang] ?? $title;
$meta_description = $page['meta_description_' . $lang] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
</head>
<body>
    <div class="container">
        <div class="about-page">
            <div class="about-content">
                <?php echo $content; ?>
            </div>
            <div class="about-sidebar">
                <h3>Company Info</h3>
                <p>Founded in 2023</p>
                <p>Experts in headless CMS solutions</p>
                <p>Based in Digital Land</p>
            </div>
        </div>
    </div>
</body>
</html>
