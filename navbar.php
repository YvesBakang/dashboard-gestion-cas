<?php
$username = $_SESSION['username'] ?? 'utilisateur';
$page_actuelle = basename($_SERVER['PHP_SELF']);
$pages_avec_retour = ['form-cas.php', 'details_cas.php', 'centre_cas.php'];
$afficher_retour = in_array($page_actuelle, $pages_avec_retour);
?>

<nav class="top-navbar">
    <div class="navbar-left">
        <?php if ($afficher_retour): ?>
            <a href="javascript:history.back()" class="navbar-back" title="Page précédente">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
            </a>
        <?php endif; ?>
        <a href="types_cas.php" class="navbar-brand-area">
            <img src="image/logo1Catis.jpg" alt="CATIS">
            <span>Prooftag CATIS</span>
        </a>
    </div>
    <div class="navbar-right">
        <div class="user-badge">
            <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 2)); ?></div>
            <span class="user-name"><?php echo htmlspecialchars(ucfirst($username)); ?></span>
        </div>
        <div class="navbar-divider"></div>
        <a href="logout.php" class="logout-btn" title="Se déconnecter">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 15l3-3m0 0l-3-3m3 3H9"/>
            </svg>
            <span>Déconnexion</span>
        </a>
    </div>
</nav>