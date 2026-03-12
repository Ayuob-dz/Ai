<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Access Denied');
}

function clean($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function validateFreeFireId($id) {
    return preg_match('/^[0-9]{1,20}$/', $id);
}

function validateRequired($value) {
    return !empty(trim($value));
}
?>
