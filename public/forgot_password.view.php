<?php

declare(strict_types=1);

/**
 * Handles password reset requests by generating a token and emailing a reset link.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\PasswordResetRepository;
use App\Models\UserRepository;
use App\Services\EmailService;
use DateTimeImmutable;

session_start();

$userRepo = new UserRepository();
$resetRepo = new PasswordResetRepository();
$emailService = new EmailService();

$message = '';
$toastType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailRaw = $_POST['email'] ?? '';
    $email = filter_var(trim($emailRaw), FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please provide a valid email address.';
        $toastType = 'danger';
    } else {
        $user = $userRepo->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = new DateTimeImmutable('+30 minutes');

            $resetRepo->createForUser((int) $user['id'], $token, $expiresAt);

            $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?? '';
            $appUrl = rtrim((string) $appUrl, '/');

            if ($appUrl === '' && isset($_SERVER['HTTP_HOST'])) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $appUrl = $scheme . $_SERVER['HTTP_HOST'];
            }

            $resetLink = rtrim($appUrl, '/') . '/reset-password?token=' . urlencode($token);

            $emailService->sendPasswordResetLink(
                $user['email'],
                $user['first_name'] ?? 'user',
                $resetLink
            );
        }

        $message = 'If that email address exists in our records, we just sent a reset link.';
        $toastType = 'success';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid min-vh-100 px-0">
    <div class="row min-vh-100 g-0">
        <div class="col-md-8 d-none d-md-block">
            <img
                src="/assets/images/spalsh-image.jpg"
                alt="Password help"
                class="w-100 h-100"
                style="object-fit: cover;"
            >
        </div>

        <div class="col-12 col-md-4 bg-light d-flex flex-column">
            <div class="flex-grow-1 d-flex flex-column justify-content-center px-4 px-md-5 py-5">
                <h3 class="mb-4 text-center">Reset your password</h3>

                <form action="/forgot-password" method="post" novalidate class="w-100">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            class="form-control"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Send reset link</button>

                    <p class="text-center mt-3 mb-0">
                        <a class="link-secondary text-decoration-none" href="/login">Back to login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast"
             class="toast text-bg-<?= htmlspecialchars($toastType) ?> border-0"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toastElement = document.getElementById('liveToast');
        if (toastElement) {
            new bootstrap.Toast(toastElement).show();
        }
    });
</script>
</body>
</html>

