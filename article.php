// db.php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'rso79_rehan_school');
define('DB_USER', 'root'); 
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// functions.php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// index.php
<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

try {
    // Fetch latest articles
    $stmt = $pdo->query("
        SELECT a.*, c.name as category_name, c.slug as category_slug, u.name as author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $articles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching articles: " . $e->getMessage());
}
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
    <?php include 'header.php'; ?>
    
    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($articles as $article): ?>
                <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <?php if (!empty($article['image'])): ?>
                        <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>"
                             class="w-full h-48 object-cover">
                    <?php endif; ?>
                    <div class="p-4">
                        <a href="category.php?slug=<?php echo htmlspecialchars($article['category_slug']); ?>"
                           class="text-red-600 text-sm font-semibold">
                            <?php echo htmlspecialchars($article['category_name']); ?>
                        </a>
                        <h2 class="text-xl font-bold mt-2 mb-2">
                            <a href="article.php?id=<?php echo $article['id']; ?>" 
                               class="hover:text-red-600">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h2>
                        <div class="text-gray-600 text-sm">
                            By <?php echo htmlspecialchars($article['author_name']); ?> | 
                            <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>

// header.php
<nav class="bg-white shadow-lg">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="text-xl font-bold text-red-600">
                <a href="index.php">News Portal</a>
            </div>
            <div class="space-x-4">
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php" class="text-gray-600 hover:text-gray-800">Login</a>
                    <a href="signup.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Sign Up</a>
                <?php else: ?>
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Dashboard</a>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-800">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

// footer.php
<footer class="bg-white shadow-lg mt-8">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-bold mb-4">About Us</h3>
                <p class="text-gray-600">Your trusted source for the latest news and updates.</p>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="index.php" class="text-gray-600 hover:text-red-600">Home</a></li>
                    <li><a href="categories.php" class="text-gray-600 hover:text-red-600">Categories</a></li>
                    <li><a href="contact.php" class="text-gray-600 hover:text-red-600">Contact</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                <div class="space-x-4">
                    <a href="#" class="text-gray-600 hover:text-red-600">Facebook</a>
                    <a href="#" class="text-gray-600 hover:text-red-600">Twitter</a>
                    <a href="#" class="text-gray-600 hover:text-red-600">Instagram</a>
                </div>
            </div>
        </div>
        <div class="border-t mt-8 pt-8 text-center text-gray-600">
            <p>&copy; <?php echo date('Y'); ?> News Portal. All rights reserved.</p>
        </div>
    </div>
</footer>

// login.php
<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } catch (PDOException $e) {
        $error = 'Login failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - News Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <main class="max-w-md mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Login</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>

                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Login
                </button>
            </form>

            <p class="mt-4 text-center text-gray-600">
                Don't have an account? 
                <a href="signup.php" class="text-red-600 hover:text-red-700">Sign up</a>
            </p>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>

// signup.php
<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
                
                // Auto login after signup
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                header('Location: dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - News Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <main class="max-w-md mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Sign Up</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 mb-2">Name</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>

                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>

                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Sign Up
                </button>
            </form>

            <p class="mt-4 text-center text-gray-600">
                Already have an account? 
                <a href="login.php" class="text-red-600 hover:text-red-700">Login</a>
            </p>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
