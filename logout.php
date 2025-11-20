<?php
/**
 * User Logout Script
 */

require_once 'includes/auth.php';

// Logout user
logoutUser();

// Redirect to home page with success message
header('Location: index.php?message=logged_out');
exit;
?>

