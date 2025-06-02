<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: simple_login.php');
    exit;
}

// Include necessary files
require_once '../includes/functions.php';

$username = $_SESSION['username'] ?? 'User';

// Check for file upload
$upload_message = '';
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $upload_dir = '../uploads/';
    $media_json = dirname(__DIR__) . '/storage/media_library.json';
    $media_library = file_exists($media_json) ? json_decode(file_get_contents($media_json), true) : [];
    $active_languages = [];
    $lang_config_file = dirname(__DIR__) . '/storage/lang_config.json';
    if (file_exists($lang_config_file)) {
        $lang_config = json_decode(file_get_contents($lang_config_file), true);
        $active_languages = $lang_config['active_languages'] ?? ['en','fr'];
    }
    $format = $_POST['media_format'] ?? 'keep';
    $files = $_FILES['media_file'];
    $count = is_array($files['name']) ? count($files['name']) : 1;
    for ($i = 0; $i < $count; $i++) {
        $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $base = preg_replace('/\.[^.]+$/', '', $name);
        $target_ext = ($format !== 'keep') ? $format : $ext;
        $target_name = $base . '.' . $target_ext;
        $target_path = $upload_dir . $target_name;
        // Si le fichier existe déjà, il sera écrasé (comportement voulu si overwrite)
        move_uploaded_file($tmp, $target_path);
        // Générer les versions _m, _d, _t
        require_once '../includes/functions.php';
        $sizes = ['m' => 480, 'd' => 1200, 't' => 150];
        foreach ($sizes as $suffix => $width) {
            $new_filename = get_versioned_filename($target_name, $suffix);
            $new_destination = $upload_dir . $new_filename;
            resize_image($target_path, $new_destination, $width, $target_ext);
        }
        // Mettre à jour le JSON (ajout ou update)
        $found = false;
        foreach ($media_library as &$item) {
            if ($item['filename'] === $target_name) {
                $item['name'] = $base;
                $item['filename'] = $target_name;
                $item['url'] = '/uploads/' . $target_name;
                $item['type'] = $target_ext;
                $item['size'] = filesize($target_path);
                $item['date'] = date('Y-m-d H:i:s');
                // Champs multilingues vides si non présents
                if (!isset($item['alt']) || !is_array($item['alt'])) $item['alt'] = [];
                if (!isset($item['description']) || !is_array($item['description'])) $item['description'] = [];
                foreach ($active_languages as $lang) {
                    if (!isset($item['alt'][$lang])) $item['alt'][$lang] = '';
                    if (!isset($item['description'][$lang])) $item['description'][$lang] = '';
                }
                $found = true;
                break;
            }
        }
        if (!$found) {
            $new_item = [
                'id' => uniqid(),
                'name' => $base,
                'filename' => $target_name,
                'url' => '/uploads/' . $target_name,
                'alt' => array_fill_keys($active_languages, ''),
                'description' => array_fill_keys($active_languages, ''),
                'type' => $target_ext,
                'size' => filesize($target_path),
                'date' => date('Y-m-d H:i:s')
            ];
            $media_library[] = $new_item;
        }
    }
    file_put_contents($media_json, json_encode($media_library, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    header('Location: media.php');
    exit;
}

// --- Gestion AJAX de la mise à jour des métadonnées média (multilingue) ---
if (isset($_GET['action']) && $_GET['action'] === 'update_meta' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $media_json = dirname(__DIR__) . '/storage/media_library.json';
    $media_library = file_exists($media_json) ? json_decode(file_get_contents($media_json), true) : [];
    $found = false;
    foreach ($media_library as &$item) {
        if ($item['id'] === $input['id']) {
            $item['name'] = $input['name'];
            // Correction : forcer structure multilingue
            if (!isset($item['alt']) || !is_array($item['alt'])) $item['alt'] = [];
            if (!isset($item['description']) || !is_array($item['description'])) $item['description'] = [];
            if (isset($input['alt']) && is_array($input['alt'])) {
                $item['alt'] = $input['alt'];
            }
            if (isset($input['description']) && is_array($input['description'])) {
                $item['description'] = $input['description'];
            }
            $found = true;
            break;
        }
    }
    if ($found && file_put_contents($media_json, json_encode($media_library, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// --- Traitement AJAX pour sauvegarder une image croppée (CropperJS) ---
if (isset($_GET['action']) && $_GET['action'] === 'save_cropped' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = '../uploads/';
    if (!isset($_FILES['cropped_image']) || !isset($_POST['original_filename']) || !isset($_POST['crop_format'])) {
        echo json_encode(['success' => false, 'error' => 'missing data']);
        exit;
    }
    $original_filename = basename($_POST['original_filename']);
    $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $base_name = preg_replace('/(_[mdt])?\.[^.]+$/', '', $original_filename);
    $target_ext = strtolower($_POST['crop_format']);
    $cropped_path = $upload_dir . $base_name . '.' . $target_ext;
    $cropped_tmp = $_FILES['cropped_image']['tmp_name'];
    $img = imagecreatefrompng($cropped_tmp);
    $success = false;
    if ($img) {
        switch ($target_ext) {
            case 'jpg':
            case 'jpeg': $success = imagejpeg($img, $cropped_path, 90); break;
            case 'webp': $success = imagewebp($img, $cropped_path, 90); break;
            case 'gif': $success = imagegif($img, $cropped_path); break;
            case 'png': default: $success = imagepng($img, $cropped_path, 6); break;
        }
        imagedestroy($img);
    }
    if ($success) {
        require_once '../includes/functions.php';
        $sizes = [
            'm' => 480,
            'd' => 1200,
            't' => 150
        ];
        foreach ($sizes as $suffix => $width) {
            $new_filename = get_versioned_filename($base_name . '.' . $target_ext, $suffix);
            $new_destination = $upload_dir . $new_filename;
            resize_image($cropped_path, $new_destination, $width, $target_ext);
        }
        // Mettre à jour la taille et la date dans media_library.json
        $media_json = dirname(__DIR__) . '/storage/media_library.json';
        $media_library = file_exists($media_json) ? json_decode(file_get_contents($media_json), true) : [];
        foreach ($media_library as &$item) {
            if ($item['filename'] === $base_name . '.' . $ext) {
                $item['size'] = filesize($cropped_path);
                $item['date'] = date('Y-m-d H:i:s');
                $item['filename'] = $base_name . '.' . $target_ext;
                $item['url'] = '/uploads/' . $base_name . '.' . $target_ext;
                $item['type'] = $target_ext;
            }
        }
        file_put_contents($media_json, json_encode($media_library, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'move failed']);
    }
    exit;
}

// Gestion AJAX côté PHP pour vérifier les doublons à l'upload
if (isset($_GET['action']) && $_GET['action'] === 'check_duplicates' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = '../uploads/';
    $file_names = $_POST['file_names'] ?? [];
    $format = $_POST['format'] ?? 'keep';
    $duplicates = [];
    foreach ($file_names as $name) {
        $original_name = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $target_ext = ($format !== 'keep') ? $format : $ext;
        $filename = $original_name . '.' . $target_ext;
        if (file_exists($upload_dir . $filename)) {
            $duplicates[] = $filename;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['duplicates' => $duplicates]);
    exit;
}

// Get all media files
$upload_dir = '../uploads/';
$media_files = [];

// Charger media_library.json pour associer chaque fichier à ses métadonnées
$media_json = dirname(__DIR__) . '/storage/media_library.json';
$media_library = file_exists($media_json) ? json_decode(file_get_contents($media_json), true) : [];

// Synchroniser media_library.json avec tous les fichiers images présents dans /uploads
$existing_filenames = array_map(function($entry) { return $entry['filename']; }, $media_library);
$upload_files = scandir($upload_dir);
foreach ($upload_files as $file) {
    if ($file !== '.' && $file !== '..' && !is_dir($upload_dir . $file)) {
        $is_image = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
        $is_thumb = preg_match('/_t\.(jpg|jpeg|png|gif|webp)$/i', $file); // détecter les vignettes
        if ($is_image && !$is_thumb && !in_array($file, $existing_filenames)) {
            $file_path = $upload_dir . $file;
            $media_item = [
                'id' => uniqid(),
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'filename' => $file,
                'url' => '/uploads/' . $file,
                'alt' => '',
                'description' => '',
                'type' => pathinfo($file, PATHINFO_EXTENSION),
                'size' => filesize($file_path),
                'date' => date('Y-m-d H:i:s', filemtime($file_path))
            ];
            $media_library[] = $media_item;
        }
    }
}
// Réécrire le JSON si modifié
file_put_contents($media_json, json_encode($media_library, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Correction : n'afficher que les originaux (pas de _m, _d, _t dans filename)
function is_original_image($filename) {
    return !preg_match('/_[mdt]\.[a-z0-9]+$/i', $filename);
}
// Correction : préfixer les chemins de vignettes et originaux par ".." pour l'accès serveur
function get_thumb_path($base, $upload_dir) {
    $exts = ['jpg', 'jpeg', 'png', 'webp'];
    foreach ($exts as $ext) {
        $thumb = $base . '_t.' . $ext;
        $thumb_path = $upload_dir . $thumb;
        if (file_exists($thumb_path)) {
            return ['../uploads/' . $thumb, $thumb_path];
        }
    }
    return [null, null];
}
$media_files = [];
foreach ($media_library as $entry) {
    if (!is_original_image($entry['filename'])) continue;
    $base = preg_replace('/(\.[^.]+)$/', '', $entry['filename']);
    list($thumb_url, $thumb_path) = get_thumb_path($base, $upload_dir);
    if ($thumb_url && $thumb_path) {
        $media_files[] = [
            'name' => basename($thumb_url),
            'path' => $thumb_url,
            'size' => filesize($thumb_path),
            'type' => 'image/' . pathinfo($thumb_url, PATHINFO_EXTENSION),
            'date' => filemtime($thumb_path),
            'original' => $entry['filename'],
            'meta' => $entry
        ];
    } else {
        $orig_path = $upload_dir . $entry['filename'];
        if (file_exists($orig_path)) {
            $file_url = '../uploads/' . $entry['filename'];
            $media_files[] = [
                'name' => $entry['filename'],
                'path' => $file_url,
                'size' => filesize($orig_path),
                'type' => 'image/' . pathinfo($entry['filename'], PATHINFO_EXTENSION),
                'date' => filemtime($orig_path),
                'original' => $entry['filename'],
                'meta' => $entry
            ];
        }
    }
}

// Fonction pour retrouver l'entrée media_library.json correspondant à un fichier
function find_media_entry($media_library, $filename) {
    foreach ($media_library as $entry) {
        if ($entry['filename'] === $filename) {
            return $entry;
        }
    }
    return null;
}

// Correction cropper : retrouver le vrai nom original même si la vignette (_t) a une autre extension
function get_original_filename($filename) {
    // Retire _t, _m, _d et change l'extension en celle du JSON si besoin
    $base = preg_replace('/(_[tmd])?\.[^.]+$/', '', $filename);
    foreach ($GLOBALS['media_library'] as $entry) {
        $entry_base = preg_replace('/(\.[^.]+)$/', '', $entry['filename']);
        if ($base === $entry_base) {
            return $entry['filename'];
        }
    }
    // fallback : retourne le nom sans suffixe avec extension d'origine
    return $base . '.' . pathinfo($filename, PATHINFO_EXTENSION);
}

// --- Filtrage côté PHP (avant affichage)
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
if ($search !== '' || $type !== '') {
    $media_files = array_values(array_filter($media_files, function($file) use ($media_library, $search, $type) {
        $entry = find_media_entry($media_library, $file['original']);
        if ($search !== '') {
            $haystack = '';
            if ($entry) {
                $haystack .= strtolower($entry['name']) . ' ';
                $haystack .= strtolower($entry['filename']) . ' ';
                if (isset($entry['alt']['fr'])) $haystack .= strtolower($entry['alt']['fr']) . ' ';
                if (isset($entry['alt']['en'])) $haystack .= strtolower($entry['alt']['en']) . ' ';
                if (isset($entry['description']['fr'])) $haystack .= strtolower($entry['description']['fr']) . ' ';
                if (isset($entry['description']['en'])) $haystack .= strtolower($entry['description']['en']) . ' ';
            }
            $haystack .= strtolower($file['name']);
            if (strpos($haystack, $search) === false) return false;
        }
        if ($type !== '') {
            $original_ext = strtolower(pathinfo($file['original'], PATHINFO_EXTENSION));
            if ($original_ext !== $type) return false;
        }
        return true;
    }));
}

// --- Pagination médias ---
$per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 24;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_media = count($media_files);
$total_pages = max(1, ceil($total_media / $per_page));
$media_files = array_slice($media_files, ($page-1)*$per_page, $per_page);

// Charger les langues actives
$lang_config_file = dirname(__DIR__) . '/storage/lang_config.json';
$lang_config = file_exists($lang_config_file) ? json_decode(file_get_contents($lang_config_file), true) : [];
$active_languages = $lang_config['active_languages'] ?? ['en','fr'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
    <!-- CropperJS -->
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Media Library</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="bi bi-upload"></i> Upload New Media
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($upload_message)): ?>
                    <div class="alert alert-<?php echo $upload_success ? 'success' : 'danger'; ?>" role="alert">
                        <?php echo htmlspecialchars($upload_message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Barre de recherche et filtre type -->
                <form class="row g-2 mb-3" method="get" id="media-search-form">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, alt, description..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">Tous types</option>
                            <option value="jpg" <?php if(isset($_GET['type']) && $_GET['type']==='jpg') echo 'selected'; ?>>JPG</option>
                            <option value="jpeg" <?php if(isset($_GET['type']) && $_GET['type']==='jpeg') echo 'selected'; ?>>JPEG</option>
                            <option value="png" <?php if(isset($_GET['type']) && $_GET['type']==='png') echo 'selected'; ?>>PNG</option>
                            <option value="webp" <?php if(isset($_GET['type']) && $_GET['type']==='webp') echo 'selected'; ?>>WEBP</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Rechercher</button>
                    </div>
                    <div class="col-md-2">
                        <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
                            <a href="media.php" class="btn btn-outline-secondary w-100">Annuler les filtres</a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Bouton suppression multiple -->
                <form id="multi-delete-form" method="post" action="media.php" onsubmit="return confirm('Supprimer les médias sélectionnés ?');">
                    <div class="mb-2">
                        <button type="submit" class="btn btn-danger btn-sm" name="multi_delete" id="multi-delete-btn" disabled>
                            <i class="bi bi-trash"></i> Supprimer la sélection
                        </button>
                    </div>
                    <div class="row">
                        <?php if (empty($media_files)): ?>
                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    No media files found. Upload files using the button above.
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($media_files as $file): ?>
                                <?php 
                                    // Correction : utiliser le nom de l'original pour retrouver l'entrée JSON
                                    $media_entry = find_media_entry($media_library, $file['original']); 
                                    $media_data = $media_entry ? htmlspecialchars(json_encode($media_entry), ENT_QUOTES, 'UTF-8') : '{}';
                                ?>
                                <div class="col-md-2 mb-4">
                                    <div class="card h-100 position-relative">
                                        <div class="media-preview">
                                            <input type="checkbox" class="form-check-input position-absolute top-0 start-0 m-2 multi-delete-checkbox" name="delete_ids[]" value="<?php echo htmlspecialchars($media_entry ? $media_entry['id'] : ''); ?>" style="z-index:2;">
                                            <img src="<?php echo htmlspecialchars($file['path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($media_entry && isset($media_entry['alt'][$active_languages[0]]) ? $media_entry['alt'][$active_languages[0]] : $file['name']); ?>">
                                            <!-- Bouton édition (icône crayon) -->
                                            <?php if ($media_entry): ?>
                                            <button type="button" class="btn btn-sm btn-light edit-media-btn position-absolute top-0 end-0 m-2" 
                                                    title="Éditer les métadonnées" data-media='<?php echo $media_data; ?>'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light edit-image-btn position-absolute top-0 start-50 translate-middle-x m-2"
                                                    title="Éditer l'image" 
                                                    data-image="<?php echo htmlspecialchars($media_entry['filename']); ?>"
                                                    data-original="<?php echo htmlspecialchars(get_original_filename($media_entry['filename'])); ?>">
                                                <i class="bi bi-scissors"></i>
                                            </button>
                                            <a href="<?php echo htmlspecialchars($media_entry['url']); ?>" download class="btn btn-sm btn-light position-absolute bottom-0 end-0 m-2" title="Télécharger">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($file['name']); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    Type: <?php echo htmlspecialchars($file['type']); ?><br>
                                                    Size: <?php echo format_file_size($file['size']); ?><br>
                                                    Date: <?php echo date('M d, Y', $file['date']); ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="card-footer">
                                            <!-- ...boutons individuels... -->
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&per_page=<?php echo $per_page; ?>" tabindex="-1" aria-disabled="true">Précédent</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&per_page=<?php echo $per_page; ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </main>
        </div>
    </div>
    
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="media.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload Media</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="media_file" class="form-label">Select File</label>
                            <input class="form-control" type="file" id="media_file" name="media_file[]" multiple required>
                        </div>
                        <div class="mb-3">
                            <label for="media_format" class="form-label">Format d'image</label>
                            <select class="form-select" id="media_format" name="media_format" required>
                                <option value="keep">Conserver le format d'origine</option>
                                <option value="jpg">JPG</option>
                                <option value="png">PNG</option>
                                <option value="webp">WEBP</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'édition des métadonnées média (multilingue) -->
    <div class="modal fade" id="editMediaModal" tabindex="-1" aria-labelledby="editMediaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="media-meta-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMediaModalLabel">Éditer les métadonnées</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="media-meta-id" name="id">
                        <div class="mb-3">
                            <label for="media-meta-name" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="media-meta-name" name="name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Texte alternatif (alt)</label>
                            <?php foreach ($active_languages as $lang): ?>
                                <input type="text" class="form-control mb-1" id="media-meta-alt-<?php echo $lang; ?>" name="alt_<?php echo $lang; ?>" placeholder="<?php echo strtoupper($lang); ?>">
                            <?php endforeach; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <?php foreach ($active_languages as $lang): ?>
                                <textarea class="form-control mb-1" id="media-meta-description-<?php echo $lang; ?>" name="description_<?php echo $lang; ?>" rows="2" placeholder="<?php echo strtoupper($lang); ?>"></textarea>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'édition d'image (CropperJS) -->
    <div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editImageModalLabel">Éditer l'image : <span id="crop-filename"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img id="cropper-image" src="" style="max-width:100%; max-height:60vh;" alt="Image à éditer">
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <label for="crop-width" class="form-label">Largeur (px)</label>
                            <input type="number" class="form-control" id="crop-width" min="1" value="">
                        </div>
                        <div class="col-auto d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm mb-2 mx-1" id="keep-ratio-btn" title="Garder le ratio" style="height:32px;width:32px;padding:0;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>
                        <div class="col">
                            <label for="crop-height" class="form-label">Hauteur (px)</label>
                            <input type="number" class="form-control" id="crop-height" min="1" value="">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="crop-format" class="form-label">Format de sortie</label>
                        <select class="form-select" id="crop-format" name="crop_format" required>
                            <option value="keep">Garder le format original</option>
                            <option value="png">PNG</option>
                            <option value="jpg">JPG</option>
                            <option value="webp">WEBP</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="save-cropped-image">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les fichiers en double -->
    <div class="modal fade" id="duplicateFileModal" tabindex="-1" aria-labelledby="duplicateFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="duplicateFileModalLabel">Doublons détectés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="duplicateFileModalContent">
                    <!-- Contenu dynamique ajouté par JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal HTML pour résolution des doublons à l'upload -->
    <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="resolve-duplicates-form">
            <div class="modal-header">
              <h5 class="modal-title" id="duplicateModalLabel">Doublons détectés</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="duplicate-modal-body">
              <!-- Contenu dynamique JS -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary">Valider et uploader</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script>
        function confirmDelete(filename) {
            if (confirm('Are you sure you want to delete ' + filename + '?')) {
                // TODO: Implement delete functionality
                alert('Delete functionality will be implemented soon.');
            }
        }

        // Ouvre le modal d'édition avec les infos du média
        function openEditMediaModal(media) {
            document.getElementById('media-meta-id').value = media.id;
            document.getElementById('media-meta-name').value = media.name || '';
            <?php foreach ($active_languages as $lang): ?>
            document.getElementById('media-meta-alt-<?php echo $lang; ?>').value = (media.alt && media.alt['<?php echo $lang; ?>']) ? media.alt['<?php echo $lang; ?>'] : '';
            document.getElementById('media-meta-description-<?php echo $lang; ?>').value = (media.description && media.description['<?php echo $lang; ?>']) ? media.description['<?php echo $lang; ?>'] : '';
            <?php endforeach; ?>
            var modal = new bootstrap.Modal(document.getElementById('editMediaModal'));
            modal.show();
        }

        // Correction JS édition meta : forcer structure multilingue à l'envoi
        const mediaMetaForm = document.getElementById('media-meta-form');
        if (mediaMetaForm) {
            mediaMetaForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const data = {
                    id: document.getElementById('media-meta-id').value,
                    name: document.getElementById('media-meta-name').value,
                    alt: {},
                    description: {}
                };
                <?php foreach ($active_languages as $lang): ?>
                data.alt['<?php echo $lang; ?>'] = document.getElementById('media-meta-alt-<?php echo $lang; ?>').value;
                data.description['<?php echo $lang; ?>'] = document.getElementById('media-meta-description-<?php echo $lang; ?>').value;
                <?php endforeach; ?>
                fetch('media.php?action=update_meta', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Erreur lors de la sauvegarde');
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-media-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const media = JSON.parse(this.dataset.media);
                    openEditMediaModal(media);
                });
            });

            let cropper;
            let keepRatio = false;
            let lastRatio = 1;

            function updateCropInputsFromCropper() {
                const data = cropper.getData();
                document.getElementById('crop-width').value = Math.round(data.width);
                document.getElementById('crop-height').value = Math.round(data.height);
                lastRatio = data.width / data.height;
            }

            document.getElementById('keep-ratio-btn').onclick = function() {
                keepRatio = !keepRatio;
                this.classList.toggle('active', keepRatio);
            };

            document.getElementById('crop-width').oninput = function() {
                let w = parseInt(this.value, 10);
                if (keepRatio) {
                    let h = Math.round(w / lastRatio);
                    document.getElementById('crop-height').value = h;
                    cropper.setCropBoxData({ width: w, height: h });
                } else {
                    cropper.setCropBoxData({ width: w });
                }
            };
            document.getElementById('crop-height').oninput = function() {
                let h = parseInt(this.value, 10);
                if (keepRatio) {
                    let w = Math.round(h * lastRatio);
                    document.getElementById('crop-width').value = w;
                    cropper.setCropBoxData({ width: w, height: h });
                } else {
                    cropper.setCropBoxData({ height: h });
                }
            };

            document.querySelectorAll('.edit-image-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filename = this.dataset.image;
                    const original = this.dataset.original || filename;
                    document.getElementById('crop-filename').textContent = original;
                    // Charger toujours l'originale avec le bon chemin relatif
                    let imgSrc = '../uploads/' + original;
                    const testImg = new Image();
                    testImg.onload = function() {
                        document.getElementById('cropper-image').src = imgSrc;
                        showCropperModal(original);
                    };
                    testImg.onerror = function() {
                        document.getElementById('cropper-image').src = '';
                        alert('Impossible de charger l\'image originale.');
                    };
                    testImg.src = imgSrc;
                });
            });

            // --- RÉTABLIR LE CROPPER (version précédente, zone de sélection par défaut) ---
            function showCropperModal(filename) {
                var modal = new bootstrap.Modal(document.getElementById('editImageModal'));
                modal.show();
                setTimeout(() => {
                    if (window.cropper) window.cropper.destroy();
                    const image = document.getElementById('cropper-image');
                    window.cropper = new Cropper(image, {
                        viewMode: 1,
                        autoCrop: true,
                        autoCropArea: 0.8,
                        responsive: true,
                        movable: true,
                        zoomable: true,
                        scalable: true,
                        aspectRatio: NaN,
                        ready() {
                            // Zone de crop par défaut (80% de l'image, centrée)
                            const data = window.cropper.getImageData();
                            const cropW = data.naturalWidth * 0.8;
                            const cropH = data.naturalHeight * 0.8;
                            window.cropper.setCropBoxData({
                                left: (data.naturalWidth-cropW)/2,
                                top: (data.naturalHeight-cropH)/2,
                                width: cropW,
                                height: cropH
                            });
                        }
                    });
                }, 400);
            }

            document.getElementById('save-cropped-image').addEventListener('click', function() {
                if (!cropper) return;
                const width = parseInt(document.getElementById('crop-width').value, 10);
                const height = parseInt(document.getElementById('crop-height').value, 10);
                const format = document.getElementById('crop-format').value;
                cropper.getCroppedCanvas({ width, height }).toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('cropped_image', blob, 'cropped.' + format);
                    // Utilise le vrai nom original stocké dans le span crop-filename
                    formData.append('original_filename', document.getElementById('crop-filename').textContent);
                    formData.append('crop_format', format);
                    fetch('media.php?action=save_cropped', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            location.reload();
                        } else {
                            alert('Erreur lors de l’enregistrement de l’image.');
                        }
                    });
                }, 'image/' + (format === 'jpg' ? 'jpeg' : format));
            });

            // Gestion activation bouton suppression multiple
            const checkboxes = document.querySelectorAll('.multi-delete-checkbox');
            const multiDeleteBtn = document.getElementById('multi-delete-btn');
            if (checkboxes.length && multiDeleteBtn) {
                checkboxes.forEach(cb => cb.addEventListener('change', function() {
                    multiDeleteBtn.disabled = !Array.from(checkboxes).some(c => c.checked);
                }));
            }
        });

        // Ajout d'un script pour gestion avancée des doublons à l'upload
        document.addEventListener('DOMContentLoaded', function() {
            const uploadForm = document.querySelector('#uploadModal form');
            if (uploadForm) {
                uploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const files = document.getElementById('media_file').files;
                    if (!files.length) return;
                    const format = document.getElementById('media_format').value;
                    const formData = new FormData();
                    for (let i = 0; i < files.length; i++) {
                        formData.append('file_names[]', files[i].name);
                    }
                    formData.append('format', format);
                    fetch('media.php?action=check_duplicates', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.duplicates && res.duplicates.length) {
                            showDuplicateModal(res.duplicates, files, format);
                        } else {
                            uploadForm.submit();
                        }
                    });
                });
            }

            // Fonction JS pour afficher le modal de résolution des doublons
            function showDuplicateModal(duplicates, files, format) {
                const modalBody = document.getElementById('duplicate-modal-body');
                modalBody.innerHTML = '';
                duplicates.forEach(dup => {
                    const row = document.createElement('div');
                    row.className = 'mb-3';
                    row.innerHTML = `
                        <div class="alert alert-danger p-2 mb-1">Fichier existant : <strong>${dup}</strong></div>
                        <label>Renommer&nbsp;:</label>
                        <input type="text" class="form-control mb-1" name="rename_${dup}" value="${dup.replace(/(\.[^.]+)$/, '')}_new${dup.match(/(\.[^.]+)$/)[0]}">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="overwrite_${dup}" id="overwrite_${dup}">
                            <label class="form-check-label text-danger" for="overwrite_${dup}">Écraser le fichier existant</label>
                        </div>
                    `;
                    modalBody.appendChild(row);
                });
                var modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
                modal.show();
                document.getElementById('resolve-duplicates-form').onsubmit = function(e) {
                    e.preventDefault();
                    // Appliquer les renommages/écrasements sur les fichiers à uploader
                    const form = e.target;
                    const renameMap = {};
                    const overwriteSet = new Set();
                    duplicates.forEach(dup => {
                        const renameVal = form.querySelector(`[name='rename_${dup}']`).value.trim();
                        const overwrite = form.querySelector(`[name='overwrite_${dup}']`).checked;
                        if (overwrite) {
                            overwriteSet.add(dup);
                        } else if (renameVal && renameVal !== dup.replace(/(\.[^.]+)$/, '')) {
                            // Ajoute l'extension d'origine
                            const ext = dup.match(/(\.[^.]+)$/)[0];
                            renameMap[dup] = renameVal + ext;
                        }
                    });
                    // Préparer un nouvel objet FormData pour l'upload effectif
                    const uploadForm = document.querySelector('#uploadModal form');
                    const filesInput = document.getElementById('media_file');
                    const format = document.getElementById('media_format').value;
                    const newFormData = new FormData();
                    for (let i = 0; i < filesInput.files.length; i++) {
                        let file = filesInput.files[i];
                        let targetName = file.name;
                        // Si ce fichier est dans les doublons, appliquer renommage ou écrasement
                        if (duplicates.includes(targetName)) {
                            if (overwriteSet.has(targetName)) {
                                // On garde le nom d'origine, il sera écrasé côté PHP
                            } else if (renameMap[targetName]) {
                                // On renomme le fichier côté client
                                file = new File([file], renameMap[targetName], {type: file.type});
                                targetName = renameMap[targetName];
                            } else {
                                // Ni renommé ni écrasé, on skippe ce fichier
                                continue;
                            }
                        }
                        newFormData.append('media_file[]', file, targetName);
                    }
                    newFormData.append('media_format', format);
                    // Soumettre l'upload en AJAX
                    fetch('media.php', {
                        method: 'POST',
                        body: newFormData
                    })
                    .then(res => res.text())
                    .then(() => { location.reload(); });
                    modal.hide();
                };
            }
        });
    </script>

    <!-- Modal pour les fichiers en double -->
    <div class="modal fade" id="duplicateFileModal" tabindex="-1" aria-labelledby="duplicateFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="duplicateFileModalLabel">Doublons détectés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="duplicateFileModalContent">
                    <!-- Contenu dynamique ajouté par JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal HTML pour résolution des doublons à l'upload -->
    <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="resolve-duplicates-form">
            <div class="modal-header">
              <h5 class="modal-title" id="duplicateModalLabel">Doublons détectés</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="duplicate-modal-body">
              <!-- Contenu dynamique JS -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary">Valider et uploader</button>
            </div>
          </form>
        </div>
      </div>
    </div>

<?php
// Suppression multiple côté PHP
if (isset($_POST['multi_delete']) && !empty($_POST['delete_ids'])) {
    $delete_ids = $_POST['delete_ids'];
    $media_json = dirname(__DIR__) . '/storage/media_library.json';
    $media_library = file_exists($media_json) ? json_decode(file_get_contents($media_json), true) : [];
    $upload_dir = '../uploads/';
    $to_keep = [];
    foreach ($media_library as $item) {
        if (!in_array($item['id'], $delete_ids)) {
            $to_keep[] = $item;
        } else {
            // Supprimer les fichiers associés (_m, _d, _t et original)
            $base = preg_replace('/(\.[^.]+)$/', '', $item['filename']);
            $ext = pathinfo($item['filename'], PATHINFO_EXTENSION);
            $files = [
                $item['filename'],
                $base . '_m.' . $ext,
                $base . '_d.' . $ext,
                $base . '_t.' . $ext
            ];
            foreach ($files as $f) {
                $fp = $upload_dir . $f;
                if (file_exists($fp)) @unlink($fp);
            }
        }
    }
    file_put_contents($media_json, json_encode($to_keep, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    header('Location: media.php');
    exit;
}