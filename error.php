<?php
/**
 * Error Page
 * 
 * Displays user-friendly error messages for various HTTP error codes
 */

$errorCode = $_GET['code'] ?? '404';
$errorMessages = [
    '400' => [
        'title' => 'Bad Request',
        'message' => 'The request could not be understood by the server.',
        'suggestion' => 'Please check your input and try again.'
    ],
    '401' => [
        'title' => 'Unauthorized',
        'message' => 'You need to be logged in to access this page.',
        'suggestion' => 'Please log in and try again.'
    ],
    '403' => [
        'title' => 'Access Forbidden',
        'message' => 'You don\'t have permission to access this resource.',
        'suggestion' => 'Please contact the administrator if you believe this is an error.'
    ],
    '404' => [
        'title' => 'Page Not Found',
        'message' => 'The page you are looking for could not be found.',
        'suggestion' => 'Please check the URL or return to the homepage.'
    ],
    '500' => [
        'title' => 'Internal Server Error',
        'message' => 'Something went wrong on our end.',
        'suggestion' => 'Please try again later or contact support if the problem persists.'
    ]
];

$error = $errorMessages[$errorCode] ?? $errorMessages['404'];
http_response_code((int)$errorCode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo htmlspecialchars($errorCode); ?> - Classifieds CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #dc3545;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .error-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Classifieds CMS</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="error-container">
            <div class="text-center">
                <div class="error-code"><?php echo htmlspecialchars($errorCode); ?></div>
                
                <div class="error-icon">⚠️</div>
                
                <h1 class="h2 mb-3"><?php echo htmlspecialchars($error['title']); ?></h1>
                
                <p class="lead mb-4"><?php echo htmlspecialchars($error['message']); ?></p>
                
                <p class="text-muted mb-4"><?php echo htmlspecialchars($error['suggestion']); ?></p>
                
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="index.php" class="btn btn-primary">
                        🏠 Go Home
                    </a>
                    
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        ← Go Back
                    </button>
                    
                    <?php if ($errorCode === '401'): ?>
                        <a href="login.php" class="btn btn-outline-primary">
                            🔐 Login
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="mt-5">
                    <h5>Popular Pages</h5>
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <ul class="list-unstyled">
                                <li><a href="index.php" class="text-decoration-none">Browse Ads</a></li>
                                <li><a href="register.php" class="text-decoration-none">Register</a></li>
                                <li><a href="login.php" class="text-decoration-none">Login</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="text-center">
                <p>&copy; 2024 Classifieds CMS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

