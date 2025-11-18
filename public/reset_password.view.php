<?php

declare(strict_types=1);

/**
 * Allows a user to choose a new password given a valid reset token.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\PasswordResetRepository;
use App\Models\UserRepository;

session_start();

$resetRepo = new PasswordResetRepository();
$userRepo = new UserRepository();

$token = $_GET['token'] ?? '';
$message = '';
$toastType = '';
$formDisabled = false;

if ($token === '') {
    $message = 'Reset token missing. Please use the email link we sent you.';
    $toastType = 'danger';
    $formDisabled = true;
} else {
    $resetRecord = $resetRepo->findValidByToken($token);

    if (!$resetRecord) {
        $message = 'This password reset link is invalid or has expired. Request a new one.';
        $toastType = 'danger';
        $formDisabled = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($message)) {
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $toastType = 'danger';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match. Please try again.';
        $toastType = 'danger';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userRepo->updatePassword((int) $resetRecord['user_id'], $passwordHash);
        $resetRepo->markAsUsed((int) $resetRecord['id']);

        $message = 'Your password has been reset. You can sign in with your new password now.';
        $toastType = 'success';
        $formDisabled = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid min-vh-100 px-0">
    <div class="row min-vh-100 g-0">
        <div class="col-md-8 d-none d-md-block">
            <img
                src="/assets/images/spalsh-image.jpg"
                alt="Reset password"
                class="w-100 h-100"
                style="object-fit: cover;"
            >
        </div>

        <div class="col-12 col-md-4 bg-light d-flex flex-column">
            <div class="flex-grow-1 d-flex flex-column justify-content-center px-4 px-md-5 py-5">
                <h3 class="mb-4 text-center">Choose a new password</h3>

                <form action="/reset-password?token=<?= urlencode($token) ?>" method="post" class="w-100">
                    <fieldset <?= $formDisabled ? 'disabled' : '' ?>>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" name="password" id="password" class="form-control" required minlength="6" autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6" autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update password</button>
                    </fieldset>

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

