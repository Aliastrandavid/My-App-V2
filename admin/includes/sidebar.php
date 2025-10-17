<?php
// Get current page filename for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'User';
$user_role = $_SESSION['role'] ?? '';
?>

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse position-fixed" style="height:100vh;">
    <div class="position-sticky pt-3">
        <!-- Site logo and title -->
        <div class="px-3 py-3 mb-3 text-white">
            <a href="../" class="text-decoration-none text-reset"><h4>Flat CMS</h4></a>
            <h6><small>David TRAN ©</small></h6>
            <br><small>Welcome, <?php echo htmlspecialchars($username); ?></small>
        </div>
        
        <!-- Main navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Properties management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === '"properties.php' ? 'active' : ''; ?>" href="properties.php">
                    <i class="bi bi-house me-2"></i>
                    Properties
                </a>
            </li>
            
            <!-- Content Management -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'posts.php' ? 'active' : ''; ?>" href="posts.php">
                    <i class="bi bi-file-text me-2"></i>
                    Content
                </a>
                <!-- Submenu for Content -->
                <?php
                // Debug: Check if we're in the right place
                error_log("Debug: Checking Content submenu");
                
                // Debug: Check if file exists
                error_log("Debug: Checking if post_types.json exists: " . (file_exists('../storage/post_types.json') ? 'Yes' : 'No'));
                
                // Check if file exists before trying to read it
                if (file_exists('../storage/post_types.json')) {
                    error_log("Debug: Loading post types from file");
                    $postTypes = json_decode(file_get_contents('../storage/post_types.json'), true);
                    error_log("Debug: Post types loaded: " . print_r($postTypes, true));
                } else {
                    // File not found, create a default array with common post types
                    error_log("Debug: Using default post types");
                    $postTypes = [
                        'post_types' => [
                            ['name' => 'Blog', 'slug' => 'blog'],
                            ['name' => 'Static Pages', 'slug' => 'index_static_pages'],
                            ['name' => 'Products', 'slug' => 'product']
                        ]
                    ];
                    error_log("Debug: Default post types: " . print_r($postTypes, true));
                }
                ?>
                <ul class="submenu">
                    <?php
                    // Load post types from storage
                    // Check if file exists before trying to read it
                    if (file_exists('../storage/post_types.json')) {
                        $postTypes = json_decode(file_get_contents('../storage/post_types.json'), true);
                    } else {
                        // File not found, create a default array with common post types
                        $postTypes = [
                            'post_types' => [
                                ['name' => 'Blog', 'slug' => 'blog'],
                                ['name' => 'Static Pages', 'slug' => 'index_static_pages'],
                                ['name' => 'Products', 'slug' => 'product']
                            ]
                        ];
                    }
                    if ($postTypes && isset($postTypes['post_types'])) {
                        foreach ($postTypes['post_types'] as $type) {
                            echo '<li><a href="posts.php?type=' . htmlspecialchars($type['slug']) . '">' . htmlspecialchars($type['name']) . '</a></li>';
                        }
                    }
                    ?>
                </ul>
            </li>
            
            <!-- Post Types -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'post-types.php' ? 'active' : ''; ?>" href="post-types.php">
                    <i class="bi bi-collection me-2"></i>
                    Post Types
                </a>
            </li>
            
            <!-- Media -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'media.php' ? 'active' : ''; ?>" href="media.php">
                    <i class="bi bi-images me-2"></i>
                    Media
                </a>
            </li>
            
            <!-- Taxonomies -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'taxonomies.php' ? 'active' : ''; ?>" href="taxonomies.php">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Taxonomies
                </a>
            </li>
            
            <!-- Admin only features -->
            <?php if ($user_role === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-people me-2"></i>
                    Users
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear me-2"></i>
                    Settings
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- Logout button at bottom -->
        <div class="px-3 mt-4">
            <a href="simple_logout.php" class="btn btn-outline-light w-100">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </a>
        </div>
    </div>
</nav>