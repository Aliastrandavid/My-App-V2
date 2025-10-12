<?php
/**
 * Home page content partial
 * Variables available: $page_content, $latest_posts
 */
?>
<div class="row">
    <div class="col-lg-10 mx-auto">
        <?php echo $page_content; ?>
    </div>
</div>

<?php if (!empty($latest_posts)): ?>
<div class="row mt-5">
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
<?php endif; ?>
