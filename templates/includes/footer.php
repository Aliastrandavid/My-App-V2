<?php
require_once __DIR__ . '/../../includes/functions.php';
$site_title = get_general_settings()['site_title'] ?? 'Flat Headless CMS';
?>
<!-- Footer -->
<footer class="bg-light py-4 mt-5">
    <div class="container">
        <p class="text-center text-muted mb-0">
            <a href="<?php echo get_site_url(); ?>/admin/dashboard.php">Admin</a>
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_title); ?>
        </p>
    </div>
</footer>
