<?php
session_start();
require_once 'db.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Fetch user's saved articles
$stmt = $pdo->prepare("
    SELECT articles.*, categories.name as category_name 
    FROM articles 
    JOIN categories ON articles.category_id = categories.id 
    WHERE articles.status = 'published' 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute();
$articles = $stmt->fetchAll();

// Fetch trending articles
$stmt = $pdo->prepare("
    SELECT articles.*, categories.name as category_name 
    FROM articles 
    JOIN categories ON articles.category_id = categories.id 
    WHERE articles.status = 'published' 
    ORDER BY views DESC 
    LIMIT 5
");
$stmt->execute();
$trending = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CNN Clone</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-red-700 text-white">
        <div class="container mx-auto px-4">
            <!-- Upper Nav -->
            <div class="flex justify-between items-center py-4">
                <a href="index.php" class="text-2xl font-bold">CNN Clone</a>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search news..." 
                               class="rounded-full py-1 px-4 text-gray-900 text-sm focus:outline-none">
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="logout.php" class="hover:text-gray-200">Logout</a>
                    </div>
                </div>
            </div>
            <!-- Lower Nav -->
            <div class="flex space-x-6 py-2 text-sm">
                <a href="#" class="hover:text-gray-300">World</a>
                <a href="#" class="hover:text-gray-300">Politics</a>
                <a href="#" class="hover:text-gray-300">Business</a>
                <a href="#" class="hover:text-gray-300">Health</a>
                <a href="#" class="hover:text-gray-300">Entertainment</a>
                <a href="#" class="hover:text-gray-300">Style</a>
                <a href="#" class="hover:text-gray-300">Travel</a>
                <a href="#" class="hover:text-gray-300">Sports</a>
                <a href="#" class="hover:text-gray-300">Videos</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-12 gap-8">
            <!-- Main News Column -->
            <div class="col-span-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Latest News</h2>
                    <a href="add-article.php" class="bg-red-700 text-white px-4 py-2 rounded-lg hover:bg-red-800 flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Add Article
                    </a>
                </div>
                <div class="space-y-6">
                    <?php foreach ($articles as $article): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="flex">
                            <?php if ($article['image']): ?>
                            <div class="w-1/3">
                                <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                                     class="w-full h-48 object-cover">
                            </div>
                            <?php endif; ?>
                            <div class="<?php echo $article['image'] ? 'w-2/3' : 'w-full'; ?> p-6">
                                <span class="text-red-600 text-sm font-semibold">
                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                </span>
                                <h3 class="text-xl font-bold mt-2">
                                    <a href="article.php?id=<?php echo $article['id']; ?>" 
                                       class="hover:text-red-600">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 mt-2">
                                    <?php echo substr(strip_tags($article['content']), 0, 150) . '...'; ?>
                                </p>
                                <div class="mt-4 text-sm text-gray-500">
                                    <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
                                    • <?php echo $article['views']; ?> views
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-span-4">
                <!-- Trending Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4">Trending Now</h3>
                    <div class="space-y-4">
                        <?php foreach ($trending as $index => $trend): ?>
                        <div class="flex items-start">
                            <span class="text-2xl font-bold text-gray-300 mr-4">
                                <?php echo $index + 1; ?>
                            </span>
                            <div>
                                <a href="article.php?id=<?php echo $trend['id']; ?>" 
                                   class="font-semibold hover:text-red-600">
                                    <?php echo htmlspecialchars($trend['title']); ?>
                                </a>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo htmlspecialchars($trend['category_name']); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Weather Widget -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4">Weather</h3>
                    <div class="text-center">
                        <i class="fas fa-sun text-4xl text-yellow-500"></i>
                        <div class="text-3xl font-bold mt-2">72°F</div>
                        <div class="text-gray-600">New York, NY</div>
                    </div>
                </div>

                <!-- Market Watch -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold mb-4">Market Watch</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>DOW</span>
                            <span class="text-green-600">+1.2%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>S&P 500</span>
                            <span class="text-red-600">-0.8%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>NASDAQ</span>
                            <span class="text-green-600">+0.5%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-4 gap-8">
                <div>
                    <h4 class="text-lg font-bold mb-4">World</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-gray-300">Africa</a></li>
                        <li><a href="#" class="hover:text-gray-300">Americas</a></li>
                        <li><a href="#" class="hover:text-gray-300">Asia</a></li>
                        <li><a href="#" class="hover:text-gray-300">Europe</a></li>
                        <li><a href="#" class="hover:text-gray-300">Middle East</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Politics</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-gray-300">Executive</a></li>
                        <li><a href="#" class="hover:text-gray-300">Congress</a></li>
                        <li><a href="#" class="hover:text-gray-300">Supreme Court</a></li>
                        <li><a href="#" class="hover:text-gray-300">Elections</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Business</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-gray-300">Markets</a></li>
                        <li><a href="#" class="hover:text-gray-300">Tech</a></li>
                        <li><a href="#" class="hover:text-gray-300">Media</a></li>
                        <li><a href="#" class="hover:text-gray-300">Success</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Follow CNN</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-2xl hover:text-gray-300"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-2xl hover:text-gray-300"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-2xl hover:text-gray-300"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-2xl hover:text-gray-300"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm">
                <p>&copy; <?php echo date('Y'); ?> CNN Clone. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
