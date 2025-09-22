<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const HUB_LOGIN_MAX_ATTEMPTS = 5;
const HUB_LOGIN_WINDOW_SECONDS = 900; // 15 minutes

/**
 * Return in-memory subscriber records that mirror the mobile app store.
 */
function hub_get_subscribers(): array
{
    return [
        'mia@quietgo.com' => [
            'name' => 'Mia Thompson',
            'subscription_status' => 'active',
            'subscription_plan' => 'pro_yearly',
            'subscription_label' => 'Pro Annual',
            'password_hash' => '$2y$12$QPB.bBnk1hcxQbsdoyon9uHbr5wHTRnjXHnpdzX2D5k8W9ZyaLuPC', // TranquilStool!1
        ],
        'casey@quietgo.com' => [
            'name' => 'Casey Morgan',
            'subscription_status' => 'active',
            'subscription_plan' => 'pro_monthly',
            'subscription_label' => 'Pro Monthly',
            'password_hash' => '$2y$12$k.YrekFMVJY2fATnNkAfNeam9e3Ug2UMuhePH3XYYLvqYUjU5R6eG', // QuietGoRocks!2
        ],
        'sam@quietgo.com' => [
            'name' => 'Sam Patel',
            'subscription_status' => 'inactive',
            'subscription_plan' => 'free',
            'subscription_label' => 'Free plan',
            'password_hash' => '$2y$12$1iyb6h2omOYerb9kbC32dOsfxJL0u4Ym05GkDCKu64iUcdiG72XBu', // QuietGoSecure#3
        ],
    ];
}

function hub_find_subscriber(string $email): ?array
{
    $emailKey = strtolower(trim($email));
    $subscribers = hub_get_subscribers();
    return $subscribers[$emailKey] ?? null;
}

function hub_record_audit(string $email, string $status, array $context = []): void
{
    $payload = [
        'timestamp' => gmdate('c'),
        'email' => strtolower($email),
        'status' => $status,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    ];

    error_log('[QuietGoHubLogin] ' . json_encode($payload));
}

function hub_get_attempt_bucket(string $ip): array
{
    if (!isset($_SESSION['hub_login_attempts'][$ip])) {
        $_SESSION['hub_login_attempts'][$ip] = [];
    }

    $now = time();
    $_SESSION['hub_login_attempts'][$ip] = array_filter(
        $_SESSION['hub_login_attempts'][$ip],
        static fn($ts) => ($now - $ts) < HUB_LOGIN_WINDOW_SECONDS
    );

    return $_SESSION['hub_login_attempts'][$ip];
}

function hub_rate_limit_exceeded(string $ip): bool
{
    $bucket = hub_get_attempt_bucket($ip);
    return count($bucket) >= HUB_LOGIN_MAX_ATTEMPTS;
}

function hub_register_login_attempt(string $ip): void
{
    $_SESSION['hub_login_attempts'][$ip][] = time();
}

function hub_clear_attempts(string $ip): void
{
    unset($_SESSION['hub_login_attempts'][$ip]);
}

function hub_login(array $subscriber, string $email): void
{
    $_SESSION['hub_user'] = [
        'email' => strtolower($email),
        'name' => $subscriber['name'],
        'subscription_status' => $subscriber['subscription_status'],
        'subscription_plan' => $subscriber['subscription_plan'],
        'subscription_label' => $subscriber['subscription_label'],
        'authenticated_at' => gmdate('c'),
    ];
}

function hub_logout(): void
{
    unset($_SESSION['hub_user']);
}

function hub_logged_in(): bool
{
    return isset($_SESSION['hub_user']);
}

function hub_current_user(): ?array
{
    return $_SESSION['hub_user'] ?? null;
}

function hub_dispatch_password_reset(string $email): void
{
    hub_record_audit($email, 'password_reset_requested');
    $_SESSION['hub_reset_requested_at'] = time();
}
