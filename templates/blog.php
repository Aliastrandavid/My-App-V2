<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/post-types.php';

$lang = defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE;
$site_title = get_general_settings()['site_title'] ?? 'My Site';
$posts = get_posts('blog', ['status' => 'published'], -1);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - <?php echo htmlspecialchars($site_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_site_url(); ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <div class="container my-5">
        <h1>Blog Posts</h1>
        <div class="row">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($post['featured_image'])): ?>
                                <img src="<?php echo get_site_url(); ?>/uploads/<?php echo $post['featured_image']; ?>" class="card-img-top" alt="">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($post['title_' . $lang] ?? ''); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr(strip_tags($post['content_' . $lang] ?? ''), 0, 150)) . '...'; ?></p>
                                <a href="<?php echo get_site_url() . '/' . $lang . '/blog/' . ($post['slug_' . $lang] ?? ''); ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col">
                    <p>No posts found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
