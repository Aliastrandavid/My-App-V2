<?php
/**
 * Homepage template
 */

$static_page = get_static_page('home');

if (!$static_page || $static_page['status'] !== 'published') {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - Page Not Found</h1>";
    exit;
}

$site_title = get_general_settings()['site_title'] ?? 'Flat Headless CMS';
$meta_title = $static_page['meta_title_' . CURRENT_LANG] ?? $static_page['meta_title_en'] ?? $static_page['title_' . CURRENT_LANG] ?? $static_page['title_en'];
$meta_description = $static_page['meta_description_' . CURRENT_LANG] ?? $static_page['meta_description_en'] ?? '';
$page_content = $static_page['content_' . CURRENT_LANG] ?? $static_page['content_en'] ?? '';

if (empty($page_content) && isset($static_page['meta_description_' . CURRENT_LANG])) {
    $page_content = $static_page['meta_description_' . CURRENT_LANG];
}
?>
<!DOCTYPE html>
<html lang="<?php echo CURRENT_LANG; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta_title); ?> - <?php echo htmlspecialchars($site_title); ?></title>
    <?php if (!empty($meta_description)): ?>
    <meta name="description" content="<?php echo htmlspecialchars(strip_tags($meta_description)); ?>">
    <?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_site_url(); ?>/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo get_site_url(); ?>"><?php echo htmlspecialchars($site_title); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php echo generate_navigation_menu(CURRENT_LANG); ?>
                <div class="ms-auto">
                    <?php echo create_language_switcher('dropdown'); ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <?php echo $page_content; ?>
            </div>
        </div>
    </div>

    <?php
    $latest_posts = get_posts('blog', [
        'status' => 'published',
        'per_page' => 5,
        'sort' => 'date_desc'
    ]);
    ?>
    <?php if (!empty($latest_posts)): ?>
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <h2 class="mb-4">Latest Posts</h2>
                <?php foreach ($latest_posts as $post): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">
                            <?php 
                            $title_field = 'title_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE);
                            $title_post = isset($post[$title_field]) && !empty($post[$title_field]) 
                                ? $post[$title_field] 
                                : $post['title_' . DEFAULT_LANGUAGE];
                            ?>
                            <a href="<?php echo get_site_url() . '/' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE) . '/blog/' . ($post['slug_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE)] ?? $post['slug_' . DEFAULT_LANGUAGE]); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($title_post); ?>
                            </a>
                        </h3>
                        <p class="card-text">
                            <?php 
                            $excerpt_field = 'excerpt_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE);
                            $excerpt = isset($post[$excerpt_field]) && !empty($post[$excerpt_field]) 
                                ? $post[$excerpt_field] 
                                : (isset($post['excerpt_' . DEFAULT_LANGUAGE]) ? $post['excerpt_' . DEFAULT_LANGUAGE] : '');
                            echo htmlspecialchars($excerpt);
                            ?>
                        </p>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted"><?php echo date('F j, Y', strtotime($post['date'])); ?></small>
                            <a href="<?php echo get_site_url() . '/' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE) . '/blog/' . ($post['slug_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE)] ?? $post['slug_' . DEFAULT_LANGUAGE]); ?>" class="btn btn-sm btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
