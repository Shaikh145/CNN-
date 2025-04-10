<?php
session_start();
require_once 'db.php';

if (!isAdmin()) {
    header("Location: login.php");
    exit;
}

// Handle article deletion
if (isset($_POST['delete_article']) && isset($_POST['article_id'])) {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$_POST['article_id']]);
    $_SESSION['success'] = "Article deleted successfully";
    header("Location: admin.php");
    exit;
}

// Handle article publishing/unpublishing
if (isset($_POST['toggle_status']) && isset($_POST['article_id'])) {
    $stmt = $pdo->prepare("UPDATE articles SET status = CASE WHEN status = 'published' THEN 'draft' ELSE 'published' END WHERE id = ?");
    $stmt->execute([$_POST['article_id']]);
    header("Location: admin.php");
    exit;
}

// Get all articles
$stmt = $pdo->query("SELECT articles.*, categories.name as category_name, users.name as author_name 
                     FROM articles 
                     LEFT JOIN categories ON articles.category_id = categories.id 
                     LEFT JOIN users ON articles.author_id = users.id 
                     ORDER BY created_at DESC");
$articles = $stmt->fetchAll();

// Get categories for the form
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CNN Clone</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/YOUR_TINY_MCE_API_KEY/tinymce/5/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 500,
            plugins: 'link image code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | link image | code'
        });
    </script>
</head>
<body class="bg-gray-100">
    <nav class="bg-red-700 text-white py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">CNN Clone</a>
            <div class="space-x-4">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="hover:text-gray-200">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <button onclick="document.getElementById('newArticleModal').classList.remove('hidden')"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Add New Article
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Articles Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($article['category_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($article['author_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                       <?php echo $article['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo ucfirst($article['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                            <form method="POST" class="inline">
                                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                <button type="submit" name="toggle_status" 
                                        class="text-indigo-600 hover:text-indigo-900">
                                    <?php echo $article['status'] === 'published' ? 'Unpublish' : 'Publish'; ?>
                                </button>
                            </form>
                            <a href="edit_article.php?id=<?php echo $article['id']; ?>" 
                               class="text-blue-600 hover:text-blue-900">Edit</a>
                            <form method="POST" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this article?')">
                                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                <button type="submit" name="delete_article" 
                                        class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Article Modal -->
    <div id="newArticleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Article</h3>
                <button onclick="document.getElementById('newArticleModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-500">×</button>
            </div>
            <form action="process_article.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                        Title
                    </label>
                    <input type="text" id="title" name="title" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="category">
                        Category
                    </label>
                    <select id="category" name="category_id" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
                        Featured Image
                    </label>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                        Content
                    </label>
                    <textarea id="content" name="content" required></textarea>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" 
                            onclick="document.getElementById('newArticleModal
