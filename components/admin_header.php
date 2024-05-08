<header class="header">
    <section class="flex">
        <a href="../admin/dashboard.php" class="logo">Admin<span>Panel</span></a>
        <nav class="navbar">
            <a href="../admins/dashboard.php">home</a>
            <a href="../admins/products.php">products</a>
            <a href="../admins/placed_orders.php">orders</a>
            <a href="../admins/admin_accounts.php">admin</a>
            <a href="../admins/users_accounts.php">users</a>
            <a href="../admins/messages.php">messages</a>
        </nav>
        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>
        <div class="profile">

            <?php
            // Check if $fetch_profile is an array before accessing its elements
            if (is_array($fetch_profile) && isset($fetch_profile['name'])) {
            echo "<p>{$fetch_profile['name']}</p>";
            } else {
                echo "<p>No profile found</p>";
            }
            ?>
            <a href="../admins/update_profile.php" class="btn1">update profile</a>
            <div class="flex-btn">
                <a href="../admins/admin_login.php" class="option-btn">login</a>
                <a href="../admins/register_admin.php" class="option-btn">register</a>
            </div>
            <a href="../component/admin_logout.php" class="delete-btn" onclick="return confirm('log out from this website?');">
                logout
            </a>

        </div>
        
    </section>
</header>
    