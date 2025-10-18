<?php
// Get current page filename for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'User';
$user_role = $_SESSION['role'] ?? '';
?>
 8 | <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse position-fixed" style="height:100vh;">
 9 |     <div class="position-sticky pt-3">
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
78 |                     <?php
79 |                     // Load post types from storage
80 |                     if (file_exists('../storage/post_types.json')) {
81 |                         $postTypes = json_decode(file_get_contents('../storage/post_types.json'), true);
82 |
83 |                         // Check if post_types array exists
84 |                         if (is_array($postTypes)) {
85 |                             foreach ($postTypes as $slug => $type) {
86 |                                 echo '<li><a href="posts.php?type=' . htmlspecialchars($slug) . '">' . htmlspecialchars($type['name_en'] ?? $type['name_fr'] ?? $slug) . '</a></li>';
87 |                             }
88 |                         }
89 |                     } else {
90 |                         // File not found, create a default array with common post types
91 |                         $postTypes = [
92 |                             'blog' => [
93 |                                 'name_en' => 'Blog',
94 |                                 'name_fr' => 'Articles de Blog',
95 |                                 'slug' => 'blog'
96 |                             ],
97 |                             'index_static_pages' => [
98 |                                 'name_en' => 'Static Pages',
99 |                                 'name_fr' => 'Pages',
100 |                                 'slug' => 'index_static_pages'
101 |                             ],
102 |                             'product' => [
103 |                                 'name_en' => 'Products',
104 |                                 'name_fr' => 'Produits',
105 |                                 'slug' => 'product'
106 |                             ]
107 |                         ];
108 |
109 |                         foreach ($postTypes as $slug => $type) {
110 |                             echo '<li><a href="posts.php?type=' . htmlspecialchars($slug) . '">' . htmlspecialchars($type['name_en'] ?? $type['name_fr'] ?? $slug) . '</a></li>';
111 |                         }
112 |                     }
113 |                     ?>
114 |                 </ul>
115 |             </li>
116 |
117 |             <!-- Post Types -->
118 |             <li class="nav-item">
119 |                 <a class="nav-link <?php echo $current_page === 'post-types.php' ? 'active' : ''; ?>" href="post-types.php">
120 |                     <i class="bi bi-collection me-2"></i>
121 |                     Post Types
122 |                 </a>
123 |             </li>
124 |
125 |             <!-- Media -->
126 |             <li class="nav-item">
127 |                 <a class="nav-link <?php echo $current_page === 'media.php' ? 'active' : ''; ?>" href="media.php">
128 |                     <i class="bi bi-images me-2"></i>
129 |                     Media
130 |                 </a>
131 |             </li>
132 |
133 |             <!-- Taxonomies -->
134 |             <li class="nav-item">
135 |                 <a class="nav-link <?php echo $current_page === 'taxonomies.php' ? 'active' : ''; ?>" href="taxonomies.php">
136 |                     <i class="bi bi-diagram-3 me-2"></i>
137 |                     Taxonomies
138 |                 </a>
139 |             </li>
140 |
141 |             <!-- Admin only features -->
142 |             <?php if ($user_role === 'admin'): ?>
143 |             <li class="nav-item">
144 |                 <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
145 |                     <i class="bi bi-people me-2"></i>
146 |                     Users
147 |                 </a>
148 |             </li>
149 |
150 |             <li class="nav-item">
151 |                 <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
152 |                     <i class="bi bi-gear me-2"></i>
153 |                     Settings
154 |                 </a>
155 |             </li>
156 |             <?php endif; ?>
157 |         </ul>
158 |
159 |         <!-- Logout button at bottom -->
160 |         <div class="px-3 mt-4">
161 |             <a href="simple_logout.php" class="btn btn-outline-light w-100">
162 |                 <i class="bi bi-box-arrow-right me-2"></i>
163 |                 Logout
164 |             </a>
165 |         </div>
166 |     </div>
167 | </nav>