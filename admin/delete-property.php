<?php
require_once 'includes/functions.php';

// Initialize variables
$error = null;
$success = false;
$propertyId = null;

// Check if we have an ID
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $propertyId = $_GET['id'];

    try {
        // Delete the property
        $success = deleteProperty($propertyId);

        if (!$success) {
            throw new Exception("Failed to delete property");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
} else {
    $error = "No property ID specified";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main content -->
            <?php // include 'includes/header.php'; 
            ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

                <div class="card mt-5">
                    <div class="card-header <?php echo $success ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                        <h2 class="card-title mb-0">
                            <?php if ($success) : ?>
                                <i class="fas fa-check-circle me-2"></i>Success
                            <?php else : ?>
                                <i class="fas fa-exclamation-triangle me-2"></i>Error
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if ($success) : ?>
                            <div class="alert alert-success">
                                <p>Property #<?php echo htmlspecialchars($propertyId); ?> was successfully deleted.</p>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i>Return to Property Listing
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-danger">
                                <p><strong>Failed to delete property:</strong> <?php echo htmlspecialchars($error); ?></p>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-list me-2"></i>Return to Property Listing
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>


                <?php //include 'includes/footer.php'; 
                ?>
            </main>
        </div>
    </div>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/scripts.js"></script>
</body>

</html>