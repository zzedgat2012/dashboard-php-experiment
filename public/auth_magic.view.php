<?php

declare(strict_types=1);

/**
 * Validates incoming magic-link tokens, finalizes one-time login records, and
 * establishes the authenticated session before redirecting the user onward.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\MagicLinkRepository;

session_start();

$magicRepo = new MagicLinkRepository();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo 'Invalid magic link.';
    exit;
}

$link = $magicRepo->findValidByToken($token);

if (!$link) {
    http_response_code(400);
    echo 'This magic link is invalid or has expired.';
    exit;
}

// Mark as used to prevent reuse
$magicRepo->markAsUsed((int) $link['id']);

// Start login session
$_SESSION['user_id']    = (int) $link['user_id'];
$_SESSION['user_email'] = $link['email'] ?? null;

// (Improvement: regenerate session ID)
session_regenerate_id(true);

// Redirect to dashboard (to be implemented)
header('Location: /dashboard');
exit;
