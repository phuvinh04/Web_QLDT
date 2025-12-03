<?php
require_once __DIR__ . '/../config.php'; // Load environment variables

$login_url = '#'; // Default fallback
$client = null;

// Chỉ load Google Client nếu vendor tồn tại
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Cấu hình Google Client từ .env
    $clientID = env('GOOGLE_CLIENT_ID');
    $clientSecret = env('GOOGLE_CLIENT_SECRET');
    $redirectUri = env('GOOGLE_REDIRECT_URI');

    if ($clientID && $clientSecret && $redirectUri) {
        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        // URL đăng nhập
        $login_url = $client->createAuthUrl();
    }
}
?>
