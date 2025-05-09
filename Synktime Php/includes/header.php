<?php
/**
 * Header
 * SynkTime - Sistema de Control de Asistencia
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: " . (strpos($_SERVER['PHP_SELF'], 'modules/') !== false ? '../../' : '') . "login.php");
    exit();
}

// Get current path to adjust relative paths
$root_path = "";
if (strpos($_SERVER['PHP_SELF'], 'modules/') !== false) {
    $root_path = "../../";
} elseif (strpos($_SERVER['PHP_SELF'], 'admin/') !== false) {
    $root_path = "../";
}

// Helper function for displaying flash messages
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Set flash message
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - SynkTime' : 'SynkTime - Sistema de Control de Asistencia' ?></title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.2/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $root_path ?>assets/css/styles.css">
    
    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
        <?= $page_css ?>
    <?php endif; ?>
</head>
<body>
    <div class="app-container">
        <?php 
        if (basename($_SERVER['PHP_SELF']) != 'login.php') {
            include_once($root_path . 'includes/sidebar.php'); 
        }
        ?>
        
        <div class="app-content">
            <?php if (basename($_SERVER['PHP_SELF']) != 'login.php'): ?>
            <header class="app-header">
                <div class="app-header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle d-lg-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="page-title"><?= $page_title ?? 'Dashboard' ?></span>
                </div>
                <div class="app-header-right">
                    <?php if (isset($_SESSION['id_sede'])): ?>
                    <div class="sede-info me-3">
                        <span class="sede-label">Sede:</span>
                        <span class="sede-name"><?= htmlspecialchars($_SESSION['sede_nombre'] ?? '') ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <?php
                            // Check for notifications
                            // This is a placeholder - integrate with your notification system
                            $notificationCount = 0; // Replace with actual notification count
                            if ($notificationCount > 0): ?>
                                <span class="notification-badge"><?= $notificationCount ?></span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                            <li class="dropdown-header">Notificaciones</li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">No hay notificaciones nuevas</a></li>
                        </ul>
                    </div>
                    
                    <div class="user-menu dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                            <div class="user-image">
                                <!-- Replace with user image if available -->
                                <i class="fas fa-user-circle"></i>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= $root_path ?>perfil.php"><i class="fas fa-user-edit me-2"></i>Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $root_path ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            <?php endif; ?>
            
            <div class="content-wrapper">
                <?php displayFlashMessage(); ?>
                
                <!-- Page content will be inserted here -->