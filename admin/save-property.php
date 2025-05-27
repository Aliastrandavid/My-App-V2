<?php
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: simple_login.php');
    exit;
}

// Initialize variables
$error = null;
$success = false;
$propertyId = null;

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get property ID
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $propertyId = $_POST['id'];

        try {
            // Get existing property
            $property = getPropertyById($propertyId);

            if (!$property) {
                throw new Exception("Property not found");
            }

            // Process form data - we need to carefully rebuild the property data
            // Start with the basic information
            $updatedProperty = [
                'id' => $propertyId,
                'reference' => $property['reference'],
                'agency' => $_POST['agency'] ?? $property['agency'],
                'quality' => $_POST['quality'] ?? $property['quality'],
                'agency_co_id' => $_POST['agency_co_id'] ?? $property['agency_co_id'],
                'brand' => $_POST['brand'] ?? $property['brand'],
                'sector' => $_POST['sector'] ?? $property['sector'],
            ];

            // Process user information if available
            if (isset($_POST['user']) && is_array($_POST['user'])) {
                $user = $_POST['user'];

                // Create updated user data, preserving original fields
                $updatedProperty['user'] = $property['user'];

                // Update specific fields from form
                if (isset($user['firstname'])) $updatedProperty['user']['firstname'] = $user['firstname'];
                if (isset($user['lastname'])) $updatedProperty['user']['lastname'] = $user['lastname'];
                if (isset($user['email'])) $updatedProperty['user']['email'] = $user['email'];
                if (isset($user['phone'])) $updatedProperty['user']['phone'] = $user['phone'];
                if (isset($user['mobile'])) $updatedProperty['user']['mobile'] = $user['mobile'];
                if (isset($user['language'])) $updatedProperty['user']['language'] = $user['language'];

                // Handle checkbox for active status
                $updatedProperty['user']['active'] = isset($user['active']) ? true : false;
            }

            // Process advanced fields
            foreach ($_POST as $key => $value) {
                if (!in_array($key, ['id', 'agency', 'quality', 'agency_co_id', 'brand', 'sector', 'user'])) {
                    if (is_array($property[$key]) && !is_array($value)) {
                        // Try to parse JSON for array fields
                        try {
                            $decodedValue = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $updatedProperty[$key] = $decodedValue;
                            } else {
                                // If JSON is invalid, keep the original value
                                $updatedProperty[$key] = $property[$key];
                            }
                        } catch (Exception $e) {
                            // If parsing fails, keep the original value
                            $updatedProperty[$key] = $property[$key];
                        }
                    } else if ($key !== 'id' && $key !== 'reference') {
                        // For non-array values and not protected fields
                        $updatedProperty[$key] = $value;
                    }
                }
            }

            // Update the property
            $success = updateProperty($propertyId, $updatedProperty);

            if (!$success) {
                throw new Exception("Failed to update property");
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = "No property ID provided";
    }
} else {
    $error = "Invalid request method";
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
                                <p>Property #<?php echo htmlspecialchars($propertyId); ?> was successfully updated.</p>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="property-details.php?id=<?php echo htmlspecialchars($propertyId); ?>" class="btn btn-primary">
                                    <i class="fas fa-eye me-2"></i>View Updated Property
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-list me-2"></i>Return to Property Listing
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-danger">
                                <p><strong>Failed to update property:</strong> <?php echo htmlspecialchars($error); ?></p>
                            </div>
                            <div class="d-grid gap-2">
                                <?php if ($propertyId) : ?>
                                    <a href="edit-property.php?id=<?php echo htmlspecialchars($propertyId); ?>" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i>Try Again
                                    </a>
                                <?php endif; ?>
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