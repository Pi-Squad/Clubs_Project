<nav class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php" <?= basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-tachometer-alt"></i>
        <span>Tableau de bord</span>
    </a>
    <a href="admin_users.php" <?= basename($_SERVER['PHP_SELF']) === 'admin_users.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-users"></i>
        <span>Utilisateurs</span>
    </a>
    <a href="admin_clubs.php" <?= basename($_SERVER['PHP_SELF']) === 'admin_clubs.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-chess-queen"></i>
        <span>Clubs</span>
    </a>
    <a href="admin_domains.php" <?= basename($_SERVER['PHP_SELF']) === 'admin_domains.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-tags"></i>
        <span>Domaines</span>
    </a>
    <a href="admin_events.php" <?= basename($_SERVER['PHP_SELF']) === 'admin_events.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-calendar-alt"></i>
        <span>Événements</span>
    </a>
    <a href="admin_messages.php" <?= basename($_SERVER['PHP_SELF']) === 'admin_messages.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-envelope"></i>
        <span>Messagerie</span>
        <?php if (isset($unread_messages_count) && $unread_messages_count > 0): ?>
            <span class="badge"><?= $unread_messages_count ?></span>
        <?php endif; ?>
    </a>
    <a href="admin_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'admin_settings.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-cog"></i>
        <span>Paramètres</span>
    </a>
    <a href="deconnexion.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Déconnexion</span>
    </a>
</nav>