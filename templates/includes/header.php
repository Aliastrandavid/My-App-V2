<?php
require_once __DIR__ . '/../../includes/functions.php';
$site_title = get_general_settings()['site_title'] ?? 'Flat Headless CMS';
?>
<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?php echo get_site_url(); ?>"><?php echo htmlspecialchars($site_title); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (function_exists('generate_navigation_menu')) echo generate_navigation_menu(CURRENT_LANG); ?>
            <div class="ms-auto">
                <?php if (function_exists('create_language_switcher')) echo create_language_switcher('dropdown'); ?>
            </div>
        </div>
    </div>
</nav>
