<?php
/**
 * Main entry point for the flat headless CMS
 * 
 * This file handles routing for the frontend and API requests
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/languages.php';
require_once 'includes/static-pages.php';
require_once 'includes/post-types.php';
require_once 'includes/taxonomy.php';
require_once 'includes/users.php';
require_once 'includes/media.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the request URI and parse it
$request_uri = $_SERVER['REQUEST_URI'];

$request_path = str_replace("daviddev/", "", str_replace("mrodev/", "", parse_url($request_uri, PHP_URL_PATH)));
$path_parts = explode('/', trim($request_path, '/'));

// Check if this is an API request
if (isset($path_parts[0]) && $path_parts[0] === 'api') {
    // Route API requests
    include 'api/index.php';
    exit;
}

// Check if this is an admin request
if (isset($path_parts[0]) && $path_parts[0] === 'admin') {
    // Include admin file based on the path
    $admin_path = implode('/', array_slice($path_parts, 1));
    
    if (empty($admin_path)) {
        // Base admin URL - include index
        include 'admin/index.php';
    } else {
        // Security: sanitize path to prevent directory traversal
        // Remove any ../ or .\ sequences
        $admin_path = str_replace(['../', '..\\', '../', '..'], '', $admin_path);
        
        // Only allow alphanumeric characters, hyphens, underscores, and single forward slashes
        if (!preg_match('/^[a-zA-Z0-9\/_-]+$/', $admin_path)) {
            header("HTTP/1.0 403 Forbidden");
            echo "<h1>403 - Forbidden</h1>";
            echo "<p>Invalid admin path.</p>";
            exit;
        }
        
        // Check if the file exists
        $admin_file = 'admin/' . $admin_path . '.php';
        
        // Additional security: verify the resolved path is still within admin directory
        $real_admin_file = realpath($admin_file);
        $real_admin_dir = realpath('admin');
        
        if ($real_admin_file && $real_admin_dir && strpos($real_admin_file, $real_admin_dir) === 0 && file_exists($admin_file)) {
            include $admin_file;
        } else {
            // 404 page
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 - Admin Page Not Found</h1>";
            echo "<p>The admin page you are looking for does not exist.</p>";
            echo "<p><a href='/admin'>Go to admin dashboard</a></p>";
        }
    }
    exit;
}

// Detect language from URL
$current_lang = detect_language_from_url($path_parts);
define('CURRENT_LANG', $current_lang);

// Remove language segment from URL if present
if (in_array($path_parts[0], get_available_languages())) {
    array_shift($path_parts);
}

// Default page is home
$page = 'home';
$slug = '';
$is_post = false;
$template_name = 'home';

// Determine what to load
if (!empty($path_parts[0])) {
    if ($path_parts[0] === 'blog' && isset($path_parts[1])) {
        // Blog post
        $page = 'post';
        $slug = $path_parts[1];
        $is_post = true;
    } else {
        // Static page
        $slug = $path_parts[0];
        $template_name = $slug;  // Preserve slug for template selection
        $static_page = get_static_page_by_slug($slug, CURRENT_LANG);
        
        if ($static_page) {
            $page = $static_page['id'];
        }
    }
}

// Load appropriate template
if ($is_post) {
    // Single post - use dedicated post template
    include 'templates/post.php';
} else {
    // Static pages - use page.php as master layout with dynamic content
    $static_page = !empty($slug) ? get_static_page_by_slug($slug, CURRENT_LANG) : get_static_page($page);
    
    if (!$static_page || $static_page['status'] !== 'published') {
        // 404 page
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The page you are looking for does not exist.</p>";
        echo "<p><a href='/'>Go to homepage</a></p>";
        exit;
    }
    
    // Define current page ID globally for language switcher
    define('CURRENT_PAGE_ID', $static_page['id']);
    
    // Build template context
    $template_context = [
        'page_id' => $static_page['id'],
        'title' => $static_page['title_' . CURRENT_LANG] ?? $static_page['title_en'],
        'meta_title' => $static_page['meta_title_' . CURRENT_LANG] ?? $static_page['meta_title_en'] ?? ($static_page['title_' . CURRENT_LANG] ?? $static_page['title_en']),
        'meta_description' => $static_page['meta_description_' . CURRENT_LANG] ?? $static_page['meta_description_en'] ?? '',
        'page_content' => $static_page['content_' . CURRENT_LANG] ?? $static_page['content_en'] ?? '',
        'slug' => $slug
    ];
    
    // Add page-specific context based on template name (slug-based)
    if ($template_name === 'home') {
        $template_context['latest_posts'] = get_posts('blog', [
            'status' => 'published',
            'per_page' => 5,
            'sort' => 'date_desc'
        ]);
    } elseif ($template_name === 'blog') {
        $template_context['posts'] = get_posts('blog', [
            'status' => 'published',
            'sort' => 'date_desc'
        ]);
    }
    
    // If page_content is empty but meta_description exists, use it
    if (empty($template_context['page_content']) && !empty($template_context['meta_description'])) {
        $template_context['page_content'] = $template_context['meta_description'];
    }
    
    // Load master template (page.php) which will include the specific content partial
    include 'templates/page.php';
}
