<?php

namespace App\Services;

/**
 * Sends transactional emails, preferring a Mailpit API transport with a PHP
 * mail() fallback for environments without the local capture service.
 */
class EmailService
{
    private string $from;

    public function __construct()
    {
        $this->from = $_ENV['EMAIL_FROM'] ?? 'no-reply@example.test';
    }

    public function sendMagicLink(string $toEmail, string $name, string $linkUrl, string $code): bool
    {
        $subject = 'Your Magic Login Link';

        $message = sprintf(
            "Hello %s,\n\n" .
            "Click this link to login:\n%s\n\n" .
            "Or enter this code: %s\n\n" .
            'This link expires in 15 minutes.',
            htmlspecialchars($name),
            htmlspecialchars($linkUrl),
            htmlspecialchars($code)
        );

        return $this->sendPlainTextEmail($toEmail, $subject, $message);
    }

    public function sendPasswordResetLink(string $toEmail, string $name, string $linkUrl): bool
    {
        $subject = 'Reset your password';

        $message = sprintf(
            "Hello %s,\n\n" .
            "We received a request to reset your password. You can choose a new one using the link below:\n%s\n\n" .
            "If you did not request this change, you can ignore this message.\n\n" .
            'This link expires in 30 minutes.',
            htmlspecialchars($name),
            htmlspecialchars($linkUrl)
        );

        return $this->sendPlainTextEmail($toEmail, $subject, $message);
    }

    private function sendPlainTextEmail(string $toEmail, string $subject, string $message): bool
    {
        $headers = [
            'From: ' . $this->from,
            'Content-Type: text/plain; charset=UTF-8',
        ];

        if ($this->sendViaMailpit($toEmail, $subject, $message)) {
            return true;
        }

        return mail($toEmail, $subject, $message, implode("\r\n", $headers));
    }

    private function sendViaMailpit(string $toEmail, string $subject, string $message): bool
    {
        $mailpitEndpoint = $_ENV['MAILPIT_HTTP_URL'] ?? getenv('MAILPIT_HTTP_URL');
        $success = false;

        if (!empty($mailpitEndpoint)) {
            $payload = json_encode([
                'From'    => $this->from,
                'To'      => [$toEmail],
                'Subject' => $subject,
                'Text'    => $message,
            ]);

            if ($payload !== false) {
                $context = stream_context_create([
                    'http' => [
                        'method'  => 'POST',
                        'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
                        'content' => $payload,
                        'timeout' => 5,
                    ],
                ]);

                $response = @file_get_contents($mailpitEndpoint, false, $context);
                $statusLine = $http_response_header[0] ?? '';

                if ($response !== false && $statusLine !== '') {
                    $parts = explode(' ', $statusLine, 3);
                    $code = isset($parts[1]) ? (int) $parts[1] : 0;

                    $success = $code >= 200 && $code < 300;
                }
            }
        }

        return $success;
    }
}
