</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>/index.php" class="footer-logo">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:linear-gradient(135deg,#0d9488,#2dd4bf);border-radius:5px;color:#fff;font-weight:800;font-size:.7rem;">V</span>
                Vacanto
            </a>
            <p class="footer-tagline">Connecting talent with opportunity.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Platform</h4>
                <a href="<?= BASE_URL ?>/jobs.php">Browse Jobs</a>
                <a href="<?= BASE_URL ?>/services.php">Browse Services</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <?php if (isLoggedIn()): ?>
                    <?php $__role = currentUserRole(); ?>
                    <?php if ($__role === ROLE_COMPANY): ?>
                        <a href="<?= BASE_URL ?>/company/dashboard.php">Dashboard</a>
                    <?php elseif ($__role === ROLE_FREELANCER): ?>
                        <a href="<?= BASE_URL ?>/freelancer/dashboard.php">Dashboard</a>
                    <?php elseif ($__role === ROLE_ADMIN): ?>
                        <a href="<?= BASE_URL ?>/admin/index.php">Admin</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/auth/logout.php">Log out</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login.php">Log in</a>
                    <a href="<?= BASE_URL ?>/auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Vacanto. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (isLoggedIn() && currentUserRole() !== ROLE_ADMIN): ?>
<script>
(function() {
    if (window.location.pathname.indexOf('/messages/chat.php') !== -1) return;
    setInterval(function() {
        fetch('<?= BASE_URL ?>/messages/poll.php?with=0&after=0')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var badge = document.getElementById('msgBadge');
                if (!badge) return;
                if (data.unread_total > 0) {
                    badge.textContent = data.unread_total;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            })
            .catch(function() {});
    }, 15000);
})();
</script>
<?php endif; ?>
</body>
</html>
