<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/static-pages.php';

$slug = 'home';
$lang = defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE;
$page = null;
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
if (!$page) {
    $page = [
        'title_en' => 'Home',
        'title_fr' => 'Accueil',
        'content_en' => '<h1>Welcome to Our Website</h1><p>This is a flat headless CMS built with PHP and JSON storage.</p>',
        'content_fr' => '<h1>Bienvenue sur notre site Web</h1><p>Ceci est un CMS headless à plat construit avec PHP et stockage JSON.</p>',
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_site_url(); ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <div class="container">
        <div class="home-page">
            <div class="hero-section">
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <div class="hero-content">
                    <?php echo $content; ?>
                </div>
            </div>
            <!-- Features section ici si besoin -->
        </div>
        <!-- Latest Posts Section -->
        <?php
        // Récupère les derniers posts du blog
        $latest_posts = get_posts('blog', [
            'status' => 'published',
            'per_page' => 5,
            'sort' => 'date_desc'
        ]);
        ?>
        <?php if (!empty($latest_posts)): ?>
        <div class="row my-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="mb-4">Latest Posts</h2>
                <?php foreach ($latest_posts as $post): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">
                            <?php 
                            $title_field = 'title_' . $lang;
                            $title_post = isset($post[$title_field]) && !empty($post[$title_field]) 
                                ? $post[$title_field] 
                                : $post['title_' . DEFAULT_LANGUAGE];
                            ?>
                            <a href="<?php echo get_site_url() . '/' . $lang . '/blog/' . ($post['slug_' . $lang] ?? $post['slug_' . DEFAULT_LANGUAGE]); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($title_post); ?>
                            </a>
                        </h3>
                        <p class="card-text">
                            <?php 
                            $excerpt_field = 'excerpt_' . $lang;
                            $excerpt = isset($post[$excerpt_field]) && !empty($post[$excerpt_field]) 
                                ? $post[$excerpt_field] 
                                : (isset($post['excerpt_' . DEFAULT_LANGUAGE]) ? $post['excerpt_' . DEFAULT_LANGUAGE] : '');
                            echo htmlspecialchars($excerpt);
                            ?>
                        </p>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted"><?php echo date('F j, Y', strtotime($post['date'])); ?></small>
                            <a href="<?php echo get_site_url() . '/' . $lang . '/blog/' . ($post['slug_' . $lang] ?? $post['slug_' . DEFAULT_LANGUAGE]); ?>" class="btn btn-sm btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>