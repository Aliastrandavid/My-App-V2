<?php
/**
 * Master page template - Uniform layout for all static pages
 * This template includes header, navigation, footer and dynamically loads content partials
 */

// Extract template context variables
extract($template_context);

// Get site settings
$site_title = get_general_settings()['site_title'] ?? 'Flat Headless CMS';
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
    <!-- Navigation -->
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

    <!-- Page Content -->
    <div class="container my-5">
        <?php
        // Include page-specific content partial
        $partial_file = __DIR__ . '/' . $template_name . '.php';
        if (file_exists($partial_file)) {
            include $partial_file;
        } else {
            // Fallback to generic content display
            ?>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="mb-4"><?php echo htmlspecialchars($title); ?></h1>
                    <div class="page-content">
                        <?php echo $page_content; ?>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
