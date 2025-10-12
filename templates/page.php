<?php
/**
 * Static page template
 */

// Get page data if not already set in index.php
if (!isset($static_page)) {
    // Lecture directe du JSON centralisé
    $pages = [];
    $page_file = __DIR__ . '/../storage/index_static_pages.json';
    if (file_exists($page_file)) {
        $json = json_decode(file_get_contents($page_file), true);
        $pages = $json['posts'] ?? [];
    }
    foreach ($pages as $p) {
        if (($p['slug_' . CURRENT_LANG] ?? '') === $slug && ($p['status'] ?? '') === 'published') {
            $static_page = $p;
            break;
        }
    }
}

// 404 si page non trouvée ou non publiée
if (!$static_page || $static_page['status'] !== 'published') {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The page you are looking for does not exist.</p>";
    echo "<p><a href='" . get_site_url() . "'>Go to homepage</a></p>";
    exit;
}

// Récupère les métadonnées
$site_title = get_general_settings()['site_title'] ?? 'Flat Headless CMS';
$meta_title = $static_page['meta_title_' . CURRENT_LANG] ?? $static_page['meta_title_en'];
$meta_description = $static_page['meta_description_' . CURRENT_LANG] ?? $static_page['meta_description_en'];

// Inclusion du header
include __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang="<?php echo CURRENT_LANG; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - <?php echo htmlspecialchars($site_title); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
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
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- <h1 class="fw-bolder mb-4"><?php echo htmlspecialchars($title); ?></h1> -->
                
                <div class="page-content">
                    <?php echo $page_content; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>