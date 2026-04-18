<footer class="site-footer">
    <!-- LOCATIONS SECTION -->
    <div class="footer-locations">
        <div class="location">
            <h4>Cinema 8</h4>
            <p>Shop 12, Sovereign Centre<br>Kingston 6, Jamaica</p>
            <p><i class="fa-solid fa-phone"></i> (876) 555-8008</p>
        </div>

        <div class="location">
            <h4>Cinema 7</h4>
            <p>Portmore Mall<br>Portmore, St. Catherine</p>
            <p><i class="fa-solid fa-phone"></i> (876) 555-7007</p>
        </div>

        <div class="location">
            <h4>Cinema 6</h4>
            <p>Fairview Shopping Centre<br>Montego Bay, St. James</p>
            <p><i class="fa-solid fa-phone"></i> (876) 555-6006</p>
        </div>
    </div>

    <div class="footer-inner">
        <!-- LEFT -->
        <div class="footer-social">
            <a href="#" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
            <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
        </div>

        <!-- CENTER -->
        <div class="footer-brand">
            <p class="footer-logo">876 Screens</p>
            <p class="footer-tagline">Experience movies the 876 way.</p>
            <p class="footer-copy">© <?= date('Y') ?> 876 Screens. All Rights Reserved.</p>
        </div>

        <!-- RIGHT -->
        <nav class="footer-links">
            <a href="<?= $basePath ?>public/schedule.php" class="nav-link <?= $activePage === 'schedule' ? 'active' : '' ?>">Schedule</a>
            <a href="<?= $basePath ?>public/contact.php" class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>">Contact Us</a>
            <a href="<?= $basePath ?>public/about.php" class="nav-link <?= $activePage === 'about' ? 'active' : '' ?>">About Us</a>
        </nav>
    </div>
</footer>