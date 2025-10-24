<?php
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function setOld($data) {
    $_SESSION['old'] = $data;
}

function old($key, $default = '') {
    if (isset($_SESSION['old'][$key])) {
        return $_SESSION['old'][$key];
    }
    return $default;
}

function clearOld() {
    unset($_SESSION['old']);
}

function setErrors($errors) {
    $_SESSION['errors'] = $errors;
}

function getError($key) {
    if (isset($_SESSION['errors'][$key])) {
        return $_SESSION['errors'][$key];
    }
    return null;
}

function clearErrors() {
    unset($_SESSION['errors']);
}


function redirect($url) {
    header("Location: $url");
    exit();
}

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

function uploadImage($file) {
    $targetDir = "motor_modif_shop/public/uploads/products/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File bukan gambar'];
    }
    
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 2MB)'];
    }
    
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return ['success' => false, 'message' => 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ['success' => true, 'filename' => $newFileName];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

function generateTransactionCode() {
    return 'TRX' . date('Ymd') . rand(1000, 9999);
}
?>