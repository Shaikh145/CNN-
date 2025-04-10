<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// Function to log errors
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'error.log');
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch categories for the dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    logError("Database Error: " . $e->getMessage());
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['title', 'content', 'category_id', 'status'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst($field) . " is required.";
            }
        }

        if (empty($errors)) {
            // Sanitize inputs using the function from db.php
            $title = sanitizeInput($_POST['title']);
            $content = $_POST['content'];
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $status = sanitizeInput($_POST['status']);
            
            // Create URL-friendly slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            // Initialize image path
            $image_path = null;

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                
                // Create uploads directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0777, true)) {
                        throw new Exception("Failed to create uploads directory.");
                    }
                }

                // Generate unique filename
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $file_name = uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;

                // Check if it's a valid image
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_extension, $allowed_types)) {
                    throw new Exception("Invalid image file type. Allowed types: " . implode(', ', $allowed_types));
                }

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    throw new Exception("Failed to upload the image.");
                }

                $image_path = $target_path;
            }

            // Insert data into the database
            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, image, category_id, author_id, status, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            
            if (!$stmt->execute([
                $title,
                $slug,
                $content,
                $image_path,
                $category_id,
                $_SESSION['user_id'],
                $status
            ])) {
                throw new Exception("Database error: Failed to insert article.");
            }

            $_SESSION['success'] = "Article added successfully!";
            header('Location: admin.php');
            exit();
        }
    } catch (Exception $e) {
        logError($e->getMessage());
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Article - CNN Clone</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Add New Article</h2>

                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p class="block sm:inline"><?php echo sanitizeInput($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    <!-- Title Field -->
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title *</label>
                        <input type="text" id="title" name="title" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                               value="<?php echo isset($_POST['title']) ? sanitizeInput($_POST['title']) : ''; ?>">
                    </div>

                    <!-- Category Field -->
                    <div class="mb-4">
                        <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category *</label>
                        <select id="category_id" name="category_id" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitizeInput($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Content Field -->
                    <div class="mb-4">
                        <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Content *</label>
                        <textarea id="content" name="content" rows="10" required
                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        ><?php echo isset($_POST['content']) ? sanitizeInput($_POST['content']) : ''; ?></textarea>
                    </div>

                    <!-- Image Upload Field -->
                    <div class="mb-4">
                        <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Featured Image</label>
                        <input type="file" id="image" name="image" accept="image/*"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <p class="text-sm text-gray-600 mt-1">Accepted formats: JPG, JPEG, PNG, GIF</p>
                    </div>

                    <!-- Status Field -->
                    <div class="mb-6">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status *</label>
                        <select id="status" name="status" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <div class="flex items-center justify-between">
                        <button type="submit" 
                                class="bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Add Article
                        </button>
                        <a href="index.php" 
                           class="inline-block align-baseline font-bold text-red-700 hover:text-red-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
