<?php

declare(strict_types=1);

/**
 * Handles the login form submission, generates magic-link tokens, and renders
 * the sign-in page with Bootstrap styling and toast notifications.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\UserRepository;
use App\Models\MagicLinkRepository;
use App\Services\EmailService;

session_start();

// Initialize Repositories + Email service
$userRepo       = new UserRepository();
$magicRepo      = new MagicLinkRepository();
$emailService   = new EmailService();

// Default UI response
$message        = '';
$toastType      = '';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Protection
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $emailRaw = $_POST['email'] ?? '';
    $email    = filter_var(trim(strip_tags($emailRaw)), FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $message   = 'Email is required.';
        $toastType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message   = 'Invalid email format.';
        $toastType = 'danger';
    } else {

        $user = $userRepo->findByEmail($email);

        if (!$user) {
            $message   = 'No user found with that email.';
            $toastType = 'danger';
        } else {
            // Create token + code
            $token = bin2hex(random_bytes(32));
            $code  = (string) random_int(100000, 999999);
            $expiresAt = new DateTimeImmutable('+15 minutes');

            // Store magic login
            $magicRepo->createForUser(
                (int) $user['id'],
                $token,
                $code,
                $expiresAt
            );

            // Build Magic URL, fall back to current host if env var missing
            $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?? '';
            $appUrl = rtrim((string) $appUrl, '/');

            if ($appUrl === '' && isset($_SERVER['HTTP_HOST'])) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $appUrl = $scheme . $_SERVER['HTTP_HOST'];
            }

            $magicUrl = rtrim($appUrl, '/') . '/auth/magic?token=' . urlencode($token);

            // Send magic login email
            $emailService->sendMagicLink(
                $user['email'],
                $user['first_name'] ?? 'user',
                $magicUrl,
                $code
            );

            $message   = 'A magic login link was sent to your email.';
            $toastType = 'success';

            // Reset form value after success
            $email = '';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <link
        href="/assets/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>
<div class="container-fluid min-vh-100 px-0">
    <div class="row min-vh-100 g-0">
        <div class="col-md-8 d-none d-md-block">
            <img
                src="/assets/images/spalsh-image.jpg"
                alt="Team collaborating"
                class="w-100 h-100"
                style="object-fit: cover;"
            >
        </div>

        <div class="col-12 col-md-4 bg-light d-flex flex-column">
            <div class="flex-grow-1 d-flex flex-column justify-content-center px-4 px-md-5 py-5">
                <h3 class="mb-4 text-center">Sign in with Email</h3>

                <form action="/login" method="post" novalidate class="w-100">

                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            class="form-control"
                            value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Send Magic Link
                    </button>

                    <div class="d-flex align-items-center my-3">
                        <hr class="flex-grow-1">
                        <span class="px-2 text-muted text-uppercase small">or</span>
                        <hr class="flex-grow-1">
                    </div>

                    <a class="btn btn-outline-secondary w-100" href="/signup">
                        Create an Account
                    </a>

                    <p class="text-center mt-3 mb-0">
                        <a class="link-secondary text-decoration-none" href="/forgot-password">Forgot your password?</a>
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
