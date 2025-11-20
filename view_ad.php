<?php

/**
 * View Individual Ad Page
 */

require_once 'includes/auth.php';
require_once 'includes/ads.php';
require_once 'includes/search.php';

// Get ad ID from URL
$adId = (int)($_GET['id'] ?? 0);
if (!$adId) {
    header('Location: index.php');
    exit;
}

// Get ad data
$currentUser = getCurrentUser();
$ad = getAdById($adId);

if (!$ad || $ad['status'] !== 'active') {
    header('Location: index.php');
    exit;
}

// Get related ads
$relatedAds = getRelatedAds($adId, $ad['category_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ad['title']); ?> - Classifieds CMS</title>
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

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="?category_id=<?php echo $ad['category_id']; ?>"><?php echo htmlspecialchars($ad['category_name']); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($ad['title']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Ad Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h1 class="h3"><?php echo htmlspecialchars($ad['title']); ?></h1>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-tag"></i> <?php echo htmlspecialchars($ad['category_name']); ?>
                                    <?php if ($ad['location']): ?>
                                        | <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ad['location']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php if ($ad['price']): ?>
                                <div class="text-end">
                                    <h2 class="text-primary mb-0">$<?php echo number_format($ad['price'], 2); ?></h2>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Ad Image -->
                        <?php if ($ad['image_filename']): ?>
                            <div class="mb-4">
                                <img src="assets/uploads/<?php echo htmlspecialchars($ad['image_filename']); ?>"
                                    class="img-fluid rounded" alt="<?php echo htmlspecialchars($ad['title']); ?>"
                                    style="max-height: 400px; width: 100%; object-fit: contain;">
                            </div>
                        <?php endif; ?>

                        <!-- Ad Description -->
                        <div class="mb-4">
                            <h4>Description</h4>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($ad['description'])); ?></p>
                        </div>

                        <!-- Ad Meta -->
                        <div class="row text-muted small">
                            <div class="col-md-6">
                                <p><strong>Posted:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($ad['created_at'])); ?></p>
                                <p><strong>Views:</strong> <?php echo $ad['views_count']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <?php if ($ad['updated_at'] !== $ad['created_at']): ?>
                                    <p><strong>Updated:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($ad['updated_at'])); ?></p>
                                <?php endif; ?>
                                <p><strong>Ad ID:</strong> #<?php echo $ad['id']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contact Seller</h5>
                    </div>
                    <div class="card-body">
                        <p><strong><?php echo htmlspecialchars($ad['first_name'] . ' ' . $ad['last_name']); ?></strong></p>

                        <?php if ($ad['contact_email']): ?>
                            <p class="mb-2">
                                <i class="bi bi-envelope"></i>
                                <a href="mailto:<?php echo htmlspecialchars($ad['contact_email']); ?>">
                                    <?php echo htmlspecialchars($ad['contact_email']); ?>
                                </a>
                            </p>
                        <?php endif; ?>

                        <?php if ($ad['contact_phone']): ?>
                            <p class="mb-2">
                                <i class="bi bi-telephone"></i>
                                <a href="tel:<?php echo htmlspecialchars($ad['contact_phone']); ?>">
                                    <?php echo htmlspecialchars($ad['contact_phone']); ?>
                                </a>
                            </p>
                        <?php endif; ?>

                        <?php if (!$currentUser): ?>
                            <div class="alert alert-info mt-3">
                                <small><a href="register.php">Register</a> or <a href="login.php">login</a> to contact the seller directly.</small>
                            </div>
                        <?php endif; ?>

                        <!-- Edit/Delete buttons for owner -->
                        <?php if ($currentUser && $currentUser['id'] == $ad['user_id']): ?>
                            <hr>
                            <div class="d-grid gap-2">
                                <a href="edit_ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-outline-primary">Edit Ad</a>
                                <a href="my_ads.php" class="btn btn-outline-secondary">Manage My Ads</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Safety Tips -->
                <div class="card mb-4" style="max-height: 400px;">
                    <div class="card-header py-2">
                        <h6 class="mb-0">Safety Tips</h6>
                    </div>
                    <div class="card-body py-2">
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-1">• Meet in a public place</li>
                            <li class="mb-1">• Inspect items before purchasing</li>
                            <li class="mb-1">• Don't send money in advance</li>
                            <li class="mb-0">• Trust your instincts</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Ads -->
        <?php if (!empty($relatedAds)): ?>
            <div class="mt-5">
                <h3>Related Ads in <?php echo htmlspecialchars($ad['category_name']); ?></h3>
                <div class="row">
                    <?php foreach ($relatedAds as $relatedAd): ?>
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card h-100">
                                <?php if ($relatedAd['image_filename']): ?>
                                    <img src="assets/uploads/<?php echo htmlspecialchars($relatedAd['image_filename']); ?>"
                                        class="card-img-top" alt="<?php echo htmlspecialchars($relatedAd['title']); ?>"
                                        style="height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                        style="height: 150px;">
                                        <span class="text-muted small">No Image</span>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title"><?php echo htmlspecialchars($relatedAd['title']); ?></h6>

                                    <?php if ($relatedAd['price']): ?>
                                        <p class="card-text"><strong class="text-primary">$<?php echo number_format($relatedAd['price'], 2); ?></strong></p>
                                    <?php endif; ?>

                                    <div class="mt-auto">
                                        <small class="text-muted d-block mb-2">
                                            <?php echo date('M j, Y', strtotime($relatedAd['created_at'])); ?>
                                        </small>
                                        <a href="view_ad.php?id=<?php echo $relatedAd['id']; ?>" class="btn btn-outline-primary btn-sm w-100">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="text-center">
                <span>© 2025. All rights reserved. </span>
                <span class="text-light">Developed by </span>
                <a href="https://rivertheme.com" class="text-light text-decoration-none fw-bold text-primary" target="_blank" rel="noopener">RiverTheme</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>