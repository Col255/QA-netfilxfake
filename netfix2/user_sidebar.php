<div class="sidebar">
    <ul>
        <li><a href="profile.php">👤 Trang cá nhân</a></li>
        <li><a href="edit_profile.php">✏️ Chỉnh sửa hồ sơ</a></li>
        <li><a href="watch_history.php">🕓 Lịch sử xem</a></li>
        <li><a href="order_history.php">🛒 Lịch sử đơn hàng</a></li>
        <li><a href="logout.php">🚪 Đăng xuất</a></li>
    </ul>
</div>

<!-- KHÔNG dùng position: fixed -->
<style>
.sidebar {
    width: 240px;
    background-color: #1a1a1a;
    color: #fff;
    padding: 20px;
    border-right: 2px solid #e50914;
}


.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar ul li {
    margin-bottom: 20px;
}

.sidebar ul li a {
    color: #ccc;
    text-decoration: none;
    font-size: 16px;
    display: block;
    padding: 10px 15px;
    border-radius: 8px;
    transition: 0.3s;
}

.sidebar ul li a:hover {
    background-color: #e50914;
    color: #fff;
    transform: translateX(5px);
}
footer.section {
    margin-top: 5px;}
</style>
