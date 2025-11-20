<?php
/**
 * Homepage - Browse and Search Classified Ads
 */

require_once 'includes/auth.php';
require_once 'includes/search.php';
require_once 'includes/ads.php';

// Get search filters from URL
$filters = [
    'keyword' => trim($_GET['keyword'] ?? ''),
    'category_id' => (int)($_GET['category_id'] ?? 0),
    'location' => trim($_GET['location'] ?? ''),
    'min_price' => trim($_GET['min_price'] ?? ''),
    'max_price' => trim($_GET['max_price'] ?? ''),
    'sort' => $_GET['sort'] ?? 'newest',
    'page' => (int)($_GET['page'] ?? 1)
];

// Remove empty filters
$filters = array_filter($filters, function($value) {
    return $value !== '' && $value !== 0;
});

// Get ads and pagination info
$isSearching = !empty($filters['keyword']) || !empty($filters['category_id']) || !empty($filters['location']) || !empty($filters['min_price']) || !empty($filters['max_price']);

if ($isSearching) {
    $ads = searchAds($filters);
    $totalAds = getAdsCount($filters);
} else {
    $ads = getFeaturedAds(12);
    $totalAds = count($ads);
}

$categories = getCategories();
$currentUser = getCurrentUser();

// Pagination
$perPage = 12;
$currentPage = $filters['page'] ?? 1;
$totalPages = ceil($totalAds / $perPage);

// Handle logout message
$message = '';
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $message = 'You have been logged out successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classifieds CMS - Buy, Sell, Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Classifieds CMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link active" href="index.php">Home</a>
                    <?php if ($currentUser): ?>
                        <a class="nav-link" href="post_ad.php">Post Ad</a>
                        <a class="nav-link" href="my_ads.php">My Ads</a>
                    <?php endif; ?>
                </div>
                <div class="navbar-nav">
                    <?php if ($currentUser): ?>
                        <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</span>
                        <a class="nav-link" href="logout.php">Logout</a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">Login</a>
                        <a class="nav-link" href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($message): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4">Find What You're Looking For</h1>
                    <p class="lead">Browse thousands of classified ads or post your own for free</p>
                    
                    <!-- Search Form -->
                    <div class="search-container">
                        <form method="GET" class="search-form">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="keyword" 
                                       placeholder="Search keywords..." 
                                       value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category_id">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (($_GET['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="location" 
                                       placeholder="Location..." 
                                       value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                        
                        <!-- Advanced Search -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="min_price" 
                                       placeholder="Min Price" step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="max_price" 
                                       placeholder="Max Price" step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="sort">
                                    <option value="newest" <?php echo (($_GET['sort'] ?? 'newest') === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="oldest" <?php echo (($_GET['sort'] ?? '') === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                                    <option value="price_low" <?php echo (($_GET['sort'] ?? '') === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo (($_GET['sort'] ?? '') === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="popular" <?php echo (($_GET['sort'] ?? '') === 'popular') ? 'selected' : ''; ?>>Most Popular</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <?php if ($isSearching): ?>
                                    <a href="index.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <!-- Results Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="mb-2">
                    <?php if ($isSearching): ?>
                        Search Results (<?php echo $totalAds; ?> found)
                    <?php else: ?>
                        Recent Classified Ads
                    <?php endif; ?>
                </h2>
                <p class="text-muted mb-0">
                    <?php if ($isSearching): ?>
                        Showing results for your search criteria
                    <?php else: ?>
                        Discover amazing deals from local sellers
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if ($currentUser): ?>
                <a href="post_ad.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Post New Ad
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Join & Post Ads
                </a>
            <?php endif; ?>
        </div>

        <!-- Ads Grid -->
        <?php if (empty($ads)): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-search display-1 text-muted"></i>
                </div>
                <h3 class="mb-3">No ads found</h3>
                <p class="text-muted mb-4">
                    <?php if ($isSearching): ?>
                        Try adjusting your search criteria or <a href="index.php" class="text-decoration-none">browse all ads</a>.
                    <?php else: ?>
                        Be the first to post a classified ad and start selling!
                    <?php endif; ?>
                </p>
                <?php if ($currentUser): ?>
                    <a href="post_ad.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Post Your First Ad
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($ads as $ad): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <?php if ($ad['image_filename']): ?>
                                <img src="assets/uploads/<?php echo htmlspecialchars($ad['image_filename']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($ad['title']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <span class="text-muted">No Image</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($ad['title']); ?></h5>
                                
                                <div class="card-meta">
                                    <i class="bi bi-tag"></i> <?php echo htmlspecialchars($ad['category_name']); ?>
                                    <?php if ($ad['location']): ?>
                                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ad['location']); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text"><?php echo htmlspecialchars(substr($ad['description'], 0, 100)); ?>
                                    <?php if (strlen($ad['description']) > 100): ?>...<?php endif; ?>
                                </p>
                                
                                <?php if ($ad['price']): ?>
                                    <div class="price-display">$<?php echo number_format($ad['price'], 2); ?></div>
                                <?php endif; ?>
                                
                                <div class="mt-auto">
                                    <small class="text-muted d-block mb-2">
                                        Posted by <?php echo htmlspecialchars($ad['first_name']); ?> • 
                                        <?php echo date('M j, Y', strtotime($ad['created_at'])); ?> • 
                                        <?php echo $ad['views_count']; ?> views
                                    </small>
                                    
                                    <a href="view_ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-primary w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="bi bi-shop me-2"></i>Classifieds CMS</h5>
                    <p class="text-muted">Your trusted local marketplace for buying and selling. Connect with your community and discover amazing deals.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-instagram fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted text-decoration-none">Browse Ads</a></li>
                        <li><a href="post_ad.php" class="text-muted text-decoration-none">Post Ad</a></li>
                        <li><a href="login.php" class="text-muted text-decoration-none">Login</a></li>
                        <li><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Popular Categories</h5>
                    <div class="row">
                        <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                            <div class="col-6 mb-2">
                                <a href="?category_id=<?php echo $category['id']; ?>" class="text-muted text-decoration-none">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center my-2">
                <div>
                    <span>© 2025. All rights reserved. </span>
                    <span class="text-light">Developed by </span>
                    <a href="https://rivertheme.com" class="text-light text-decoration-none fw-bold text-primary" target="_blank" rel="noopener">RiverTheme</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

