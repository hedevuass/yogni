<?php
$settings = include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';

function encrypt_password(string $password): string {
    $key = base64_decode(APP_SECRET_KEY);
    $iv = random_bytes(16);

    $encrypted = openssl_encrypt(
        $password,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    return base64_encode($iv . $encrypted);
}

function decrypt_password(string $encrypted): string {
    $key = base64_decode(APP_SECRET_KEY);

    $data = base64_decode($encrypted);
    $iv = substr($data, 0, 16);
    $ciphertext = substr($data, 16);

    return openssl_decrypt(
        $ciphertext,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
}

?>