<!-- Contenu spécifique de la page blog -->
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
                        <h5 class="card-title"><?php echo htmlspecialchars($post['title_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE)] ?? ''); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr(strip_tags($post['content_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE)] ?? ''), 0, 150)) . '...'; ?></p>
                        <a href="<?php echo get_site_url() . '/' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE) . '/blog/' . ($post['slug_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE)] ?? ''); ?>" class="btn btn-primary">Read More</a>
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
