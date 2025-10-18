<?php
// Get current page filename for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'User';
$user_role = $_SESSION['role'] ?? '';
?>
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse position-fixed" style="height:100vh;">
    <div class="position-sticky pt-3">
10 |         <!-- Site logo and title -->
11 |         <div class="px-3 py-3 mb-3 text-white">
12 |             <a href="../" class="text-decoration-none text-reset"><h4>Flat CMS</h4></a>
13 |             <h6><small>David TRAN ©</small></h6>
14 |             <br><small>Welcome, <?php echo htmlspecialchars($username); ?></small>
15 |         </div>
16 |
17 |         <!-- Main navigation -->
18 |         <ul class="nav flex-column">
19 |             <li class="nav-item">
20 |                 <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
21 |                     <i class="bi bi-speedometer2 me-2"></i>
22 |                     Dashboard
23 |                 </a>
24 |             </li>
25 |
26 |             <!-- Properties management -->
27 |             <li class="nav-item">
28 |                 <a class="nav-link <?php echo $current_page === '"properties.php' ? 'active' : ''; ?>" href="properties.php">
29 |                     <i class="bi bi-house me-2"></i>
30 |                     Properties
31 |                 </a>
32 |             </li>
33 |
34 |             <!-- Content Management -->
35 |             <li class="nav-item">
36 |                 <a class="nav-link <?php echo $current_page === 'posts.php' ? 'active' : ''; ?>" href="posts.php">
37 |                     <i class="bi bi-file-text me-2"></i>
38 |                     Content
39 |                 </a>
40 |                 <!-- Submenu for Content -->
41 |                 <?php
42 |                 // Load post types from storage
43 |                 if (file_exists('../storage/post_types.json')) {
44 |                     $postTypes = json_decode(file_get_contents('../storage/post_types.json'), true);
45 |
46 |                     // Check if post_types array exists
47 |                     if (is_array($postTypes)) {
48 |                         foreach ($postTypes as $slug => $type) {
49 |                             echo '<li><a href="posts.php?type=' . htmlspecialchars($slug) . '">' . htmlspecialchars($type['name_en'] ?? $type['name_fr'] ?? $slug) . '</a></li>';
50 |                         }
51 |                     }
52 |                 } else {
53 |                     // File not found, create a default array with common post types
54 |                     $postTypes = [
55 |                         'blog' => [
56 |                             'name_en' => 'Blog',
57 |                             'name_fr' => 'Articles de Blog',
58 |                             'slug' => 'blog'
59 |                         ],
60 |                         'index_static_pages' => [
61 |                             'name_en' => 'Static Pages',
62 |                             'name_fr' => 'Pages',
63 |                             'slug' => 'index_static_pages'
64 |                         ],
65 |                         'product' => [
66 |                             'name_en' => 'Products',
67 |                             'name_fr' => 'Produits',
68 |                             'slug' => 'product'
69 |                         ]
70 |                     ];
71 |
72 |                     foreach ($postTypes as $slug => $type) {
73 |                         echo '<li><a href="posts.php?type=' . htmlspecialchars($slug) . '">' . htmlspecialchars($type['name_en'] ?? $type['name_fr'] ?? $slug) . '</a></li>';
74 |                     }
75 |                 }
76 |                 ?>
77 |                 <ul class="submenu">
78 |                 </ul>
79 |             </li>
80 |
81 |             <!-- Post Types -->
82 |             <li class="nav-item">
83 |                 <a class="nav-link <?php echo $current_page === 'post-types.php' ? 'active' : ''; ?>" href="post-types.php">
84 |                     <i class="bi bi-collection me-2"></i>
85 |                     Post Types
86 |                 </a>
87 |             </li>
88 |
89 |             <!-- Media -->
90 |             <li class="nav-item">
91 |                 <a class="nav-link <?php echo $current_page === 'media.php' ? 'active' : ''; ?>" href="media.php">
92 |                     <i class="bi bi-images me-2"></i>
93 |                     Media
94 |                 </a>
95 |             </li>
96 |
97 |             <!-- Taxonomies -->
98 |             <li class="nav-item">
99 |                 <a class="nav-link <?php echo $current_page === 'taxonomies.php' ? 'active' : ''; ?>" href="taxonomies.php">
100 |                     <i class="bi bi-diagram-3 me-2"></i>
101 |                     Taxonomies
102 |                 </a>
103 |             </li>
104 |
105 |             <!-- Admin only features -->
106 |             <?php if ($user_role === 'admin'): ?>
107 |             <li class="nav-item">
108 |                 <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
109 |                     <i class="bi bi-people me-2"></i>
110 |                     Users
111 |                 </a>
112 |             </li>
113 |
114 |             <li class="nav-item">
115 |                 <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
116 |                     <i class="bi bi-gear me-2"></i>
117 |                     Settings
118 |                 </a>
119 |             </li>
120 |             <?php endif; ?>
121 |         </ul>
122 |
123 |         <!-- Logout button at bottom -->
124 |         <div class="px-3 mt-4">
125 |             <a href="simple_logout.php" class="btn btn-outline-light w-100">
126 |                 <i class="bi bi-box-arrow-right me-2"></i>
127 |                 Logout
128 |             </a>
129 |         </div>
130 |     </div>
131 | </nav>