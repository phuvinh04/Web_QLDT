<?php
// Get user info from session
$user_fullname = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
$user_role = 'Nhân viên';
if(isset($_SESSION['role_id'])) {
    if($_SESSION['role_id'] == 1) $user_role = 'Quản trị viên';
    if($_SESSION['role_id'] == 2) $user_role = 'Quản lý';
}

$avatar_img = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'default-avatar.png';
$display_avatar = false;
$final_avatar_url = '';

// Check if avatar is a remote URL (Google) or local file
if (strpos($avatar_img, 'http') === 0) {
    $final_avatar_url = $avatar_img;
    $display_avatar = true;
} elseif ($avatar_img != 'default-avatar.png') {
    // Local file check
    $local_path = 'assets/uploads/avatars/' . $avatar_img;
    // Check if file exists (relative to the script executing this, usually index.php or pages/x.php)
    // We use base_url to navigate back if inside pages/
    if (file_exists(__DIR__ . '/../assets/uploads/avatars/' . $avatar_img)) {
        $final_avatar_url = $base_url . $local_path;
        $display_avatar = true;
    }
}

$user_initial = substr($user_fullname, 0, 1);
?>
<!-- Header -->
<header class="header">
  <div class="header-left">
    <button class="btn border-0 p-0 me-3" id="sidebarToggle">
      <i class="bi bi-list fs-3 text-secondary"></i>
    </button>
  </div>
  <div class="header-right">
    <div class="header-search">
      <input type="text" placeholder="Tìm kiếm...">
      <i class="bi bi-search"></i>
    </div>
    <div class="header-user">
      <?php if($display_avatar): ?>
        <img src="<?php echo $final_avatar_url; ?>" class="user-avatar user-avatar-img">
      <?php else: ?>
        <div class="user-avatar"><?php echo strtoupper($user_initial); ?></div>
      <?php endif; ?>
      <div class="user-info">
        <div class="user-info-name"><?php echo $user_fullname; ?></div>
        <div class="user-info-role"><?php echo $user_role; ?></div>
      </div>
    </div>
  </div>
</header>
