<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="text-xl font-semibold text-gray-800">
                    <a href="index.php">News Portal</a>
                </div>
                <div class="space-x-4">
                    <?php if (!isLoggedIn()): ?>
                        <a href="login.php" class="text-gray-600 hover:text-gray-800">Login</a>
                        <a href="signup.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Sign Up</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Dashboard</a>
                        <a href="logout.php" class="text-gray-600 hover:text-gray-800">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-blue-600 text-white py-20">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-4">Welcome to News Portal</h1>
            <p class="text-xl mb-8">Stay informed with the latest news and updates from around the world.</p>
            <a href="signup.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100">
                Get Started
            </a>
        </div>
    </div>

    <!-- Features Section -->
    <div class="max-w-6xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-4">Latest News</h3>
                <p class="text-gray-600">Stay updated with breaking news and the latest developments from around the globe.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-4">Personalized Feed</h3>
                <p class="text-gray-600">Get news that matters to you with our personalized news feed feature.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-4">Expert Analysis</h3>
                <p class="text-gray-600">Read in-depth analysis from our expert journalists and contributors.</p>
            </div>
        </div>
    </div>

    <!-- Latest Articles -->
    <div class="max-w-6xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold mb-8">Latest Articles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $stmt = $pdo->query("SELECT articles.*, categories.name as category_name 
                                FROM articles 
                                JOIN categories ON articles.category_id = categories.id 
                                WHERE status = 'published' 
                                ORDER BY created_at DESC LIMIT 6");
            while ($article = $stmt->fetch()):
            ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <?php if ($article['image']): ?>
                    <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>"
                         class="w-full h-48 object-cover">
                <?php endif; ?>
                <div class="p-6">
                    <span class="text-blue-500 text-sm font-semibold">
                        <?php echo htmlspecialchars($article['category_name']); ?>
                    </span>
                    <h3 class="text-xl font-semibold mt-2">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </h3>
                    <p class="text-gray-600 mt-2">
                        <?php echo substr(strip_tags($article['content']), 0, 150) . '...'; ?>
                    </p>
                    <a href="article.php?id=<?php echo $article['id']; ?>" 
                       class="inline-block mt-4 text-blue-500 hover:text-blue-600">
                        Read More →
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> News Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
