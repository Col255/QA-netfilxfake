<div class="sidebar">
        <a href="admin_dashboard.php" class="menu-item <?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>">Admin</a>
        <a href="statistics.php" class="menu-item <?php echo $current_page == 'statistics.php' ? 'active' : ''; ?>">Thống kê</a>
        <a href="manage_movies.php" class="menu-item">Quản lý phim</a>
        <a href="add_movie.php" class="menu-item">Thêm phim</a>
        <a href="manage_vip.php" class="menu-item">Quản lý VIP</a>
        <a href="customers.php" class="menu-item">Khách hàng</a>
        <a href="orders.php" class="menu-item">Đơn hàng</a>
    </div>

<style>
.sidebar {
    width: 260px;
    background-color: #141414;
    padding: 30px 20px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    border-right: 1px solid #2e2e2e;
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3);
}

.sidebar .menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    font-size: 16px;
    color: #bbb;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.25s ease;
    font-weight: 500;
}

.sidebar .menu-item i {
    font-size: 18px;
    transition: transform 0.3s ease;
}

.sidebar .menu-item:hover {
    background-color: #e50914;
    color: #fff;
    box-shadow: 0 4px 12px rgba(229, 9, 20, 0.4);
    transform: scale(1.02);
}

.sidebar .menu-item:hover i {
    transform: scale(1.2);
}

.sidebar .menu-item.active {
    background-color: #e50914;
    color: #fff;
    box-shadow: 0 0 12px rgba(229, 9, 20, 0.5);
}

.sidebar .watch-now {
    margin-top: auto;
    background-color: #e50914;
    color: #fff;
    justify-content: center;
    font-weight: 600;
    font-size: 17px;
    box-shadow: 0 0 15px rgba(229, 9, 20, 0.5);
}
.sidebar .watch-now:hover {
    background-color: #ff0f23;
}

</style>