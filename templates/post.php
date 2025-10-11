<!-- Contenu spécifique d'un post -->
<article>
    <header class="mb-4">
        <h1 class="fw-bolder mb-3"><?php echo htmlspecialchars($post_title ?? ''); ?></h1>
        <div class="text-muted mb-3">
            <?php if (!empty($post_date ?? '')): ?>
            <span class="me-3"><i class="bi bi-calendar"></i> <?php echo $post_date ?? ''; ?></span>
            <?php endif; ?>
            <?php if (!empty($author['name'] ?? '')): ?>
            <span class="me-3"><i class="bi bi-person"></i> <?php echo htmlspecialchars($author['name']); ?></span>
            <?php endif; ?>
            <?php if (!empty($categories ?? [])): ?>
            <span class="me-3">
                <i class="bi bi-folder"></i> 
                <?php 
                $category_links = [];
                foreach ($categories as $cat_id) {
                    $term = get_term('category', $cat_id);
                    if ($term) {
                        $term_name_field = 'name_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE);
                        $term_name = isset($term[$term_name_field]) && !empty($term[$term_name_field]) 
                            ? $term[$term_name_field] 
                            : $term['name_' . DEFAULT_LANGUAGE];
                        $term_url = get_site_url() . '/category/' . $term['slug'];
                        $category_links[] = '<a href="' . $term_url . '">' . htmlspecialchars($term_name) . '</a>';
                    }
                }
                echo implode(', ', $category_links);
                ?>
            </span>
            <?php endif; ?>
        </div>
    </header>
    <?php if (!empty($featured_image ?? '')): ?>
    <figure class="mb-4">
        <img class="img-fluid rounded" src="<?php echo get_site_url() . '/uploads/' . $featured_image; ?>" alt="<?php echo htmlspecialchars($post_title ?? ''); ?>">
    </figure>
    <?php endif; ?>
    <div class="mb-5">
        <?php echo $post_content ?? ''; ?>
    </div>
    <?php if (!empty($tags ?? [])): ?>
    <div class="mb-4">
        <i class="bi bi-tags"></i> 
        <?php 
        $tag_links = [];
        foreach ($tags as $tag_id) {
            $term = get_term('tag', $tag_id);
            if ($term) {
                $term_name_field = 'name_' . (defined('CURRENT_LANG') ? CURRENT_LANG : DEFAULT_LANGUAGE);
                $term_name = isset($term[$term_name_field]) && !empty($term[$term_name_field]) 
                    ? $term[$term_name_field] 
                    : $term['name_' . DEFAULT_LANGUAGE];
                $term_url = get_site_url() . '/tag/' . $term['slug'];
                $tag_links[] = '<a href="' . $term_url . '" class="badge bg-secondary text-decoration-none link-light">' . htmlspecialchars($term_name) . '</a>';
            }
        }
        echo implode(' ', $tag_links);
        ?>
    </div>
    <?php endif; ?>
</article>