<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Access Denied');
}

function uploadImage($file, $subdir = '') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    $new_name = uniqid() . '_' . time() . '.' . $ext;
    
    $target_dir = UPLOAD_DIR . ($subdir ? $subdir . '/' : '');
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . $new_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return 'assets/uploads/' . ($subdir ? $subdir . '/' : '') . $new_name;
    }
    return false;
}
?>
