<?php

/**
 * Presents and processes the user registration form, persisting new accounts
 * via the UserRepository and emitting Bootstrap toasts for feedback.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\LoginForm;
use App\Models\UserRepository;

$form = new LoginForm();
$userRepo = new UserRepository();

$message = '';
$toastType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form->loadData($_POST);

    if ($form->validate()) {

        if ($userRepo->emailExists($form->getEmail())) {
            $message = 'Email already registered.';
            $toastType = 'danger';
        } else {
            $userRepo->create(
                $form->getFirstName(),
                $form->getSecondName(),
                $form->getEmail(),
                $form->getPassword()
            );

            $message = 'User registered successfully!';
            $toastType = 'success';

            $form = new LoginForm(); // Clear the form
        }

    } else {
        $message = 'Please correct the errors.';
        $toastType = 'danger';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>

    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid min-vh-100 px-0">
        <div class="row min-vh-100 g-0">
            <div class="col-md-8 d-none d-md-block">
                <img
                    src="/assets/images/spalsh-image.jpg"
                    alt="People collaborating"
                    class="w-100 h-100"
                    style="object-fit: cover;"
                >
            </div>

            <div class="col-12 col-md-4 d-flex align-items-center justify-content-center bg-light">
                <div class="w-100 px-4 px-md-5" style="max-width: 440px;">
                    <form action="" method="post">

                        <h3 class="mb-4 text-center">Create Account</h3>

                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text"
                                name="first_name"
                                required
                                minlength="2"
                                maxlength="50"
                                autocomplete="given-name"
                                value="<?= htmlspecialchars($form->getFirstName(), ENT_QUOTES, 'UTF-8') ?>"
                                class="form-control <?= isset($form->getErrors()['first_name']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($form->getErrors()['first_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= $form->getErrors()['first_name'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="second_name" class="form-label">Second Name</label>
                            <input type="text"
                                name="second_name"
                                required
                                minlength="2"
                                maxlength="50"
                                autocomplete="family-name"
                                value="<?= htmlspecialchars($form->getSecondName(), ENT_QUOTES, 'UTF-8') ?>"
                                class="form-control <?= isset($form->getErrors()['second_name']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($form->getErrors()['second_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= $form->getErrors()['second_name'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email"
                                name="email"
                                required
                                autocomplete="email"
                                value="<?= htmlspecialchars($form->getEmail(), ENT_QUOTES, 'UTF-8') ?>"
                                class="form-control <?= isset($form->getErrors()['email']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($form->getErrors()['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= $form->getErrors()['email'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                name="password"
                                required
                                minlength="6"
                                autocomplete="new-password"
                                class="form-control <?= isset($form->getErrors()['password']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($form->getErrors()['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= $form->getErrors()['password'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3 text-center pt-3">
                            <input type="submit" value="Sign Up" class="btn btn-primary w-100">
                        </div>

                    </form>

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

                </div>
            </div>
        </div>
    </div>

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
