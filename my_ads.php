<?php
/**
 * My Ads Management Page
 */

require_once 'includes/auth.php';
require_once 'includes/ads.php';

// Require login
requireLogin();

$currentUser = getCurrentUser();
$message = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verifyCSRFToken($_GET['token'] ?? '')) {
        $result = deleteAd((int)$_GET['id'], $currentUser['id']);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    } else {
        $message = 'Invalid request';
        $messageType = 'danger';
    }
}

// Get user's ads
$userAds = getUserAds($currentUser['id']);
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ads - Classifieds CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a class="nav-link" href="index.php">Home</a>
                    <a class="nav-link" href="post_ad.php">Post Ad</a>
                    <a class="nav-link active" href="my_ads.php">My Ads</a>
                </div>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</span>
                    <a class="nav-link" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Classified Ads</h2>
            <a href="post_ad.php" class="btn btn-primary">Post New Ad</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($userAds)): ?>
            <div class="text-center py-5">
                <h4>No ads posted yet</h4>
                <p class="text-muted">Start by posting your first classified ad!</p>
                <a href="post_ad.php" class="btn btn-primary">Post Your First Ad</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($userAds as $ad): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <?php if ($ad['image_filename']): ?>
                                <img src="assets/uploads/<?php echo htmlspecialchars($ad['image_filename']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($ad['title']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($ad['title']); ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="bi bi-tag"></i> <?php echo htmlspecialchars($ad['category_name']); ?>
                                    <?php if ($ad['location']): ?>
                                        | <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ad['location']); ?>
                                    <?php endif; ?>
                                </p>
                                
                                <p class="card-text"><?php echo htmlspecialchars(substr($ad['description'], 0, 100)); ?>
                                    <?php if (strlen($ad['description']) > 100): ?>...<?php endif; ?>
                                </p>
                                
                                <?php if ($ad['price']): ?>
                                    <p class="card-text"><strong>$<?php echo number_format($ad['price'], 2); ?></strong></p>
                                <?php endif; ?>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            Status: 
                                            <span class="badge bg-<?php echo $ad['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($ad['status']); ?>
                                            </span>
                                        </small>
                                        <small class="text-muted"><?php echo $ad['views_count']; ?> views</small>
                                    </div>
                                    
                                    <small class="text-muted d-block mb-2">
                                        Posted: <?php echo date('M j, Y', strtotime($ad['created_at'])); ?>
                                    </small>
                                    
                                    <div class="btn-group w-100" role="group">
                                        <a href="view_ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-outline-primary btn-sm">View</a>
                                        <a href="edit_ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                        <a href="?action=delete&id=<?php echo $ad['id']; ?>&token=<?php echo urlencode($csrfToken); ?>" 
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this ad?')">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

