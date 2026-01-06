<?php
declare(strict_types=1);

/**
 * Simple password strength scoring
 */
function passwordStrength(string $password): array
{
    $score = 0;

    if (strlen($password) >= 12) $score += 30;
    if (preg_match('/[A-Z]/', $password)) $score += 15;
    if (preg_match('/[a-z]/', $password)) $score += 15;
    if (preg_match('/[0-9]/', $password)) $score += 20;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 20;

    $score = min($score, 100);

    return [
        'score' => $score,
        'label' => $score >= 80 ? 'Strong' : ($score >= 50 ? 'Medium' : 'Weak')
    ];
}

/**
 * Estimate crack time using entropy
 */
function estimateCrackTime(string $password): array
{
    $length = strlen($password);
    $charset = 0;

    if (preg_match('/[a-z]/', $password)) $charset += 26;
    if (preg_match('/[A-Z]/', $password)) $charset += 26;
    if (preg_match('/[0-9]/', $password)) $charset += 10;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $charset += 32;

    if ($charset === 0) $charset = 1;

    $entropy = $length * log($charset, 2);

    // Assumptions
    $onlineGuesses  = 100;   // rate-limited online attack
    $offlineGuesses = 1e10;  // GPU offline attack

    return [
        'entropy' => round($entropy, 2),
        'online'  => secondsToHuman(pow(2, $entropy) / $onlineGuesses),
        'offline' => secondsToHuman(pow(2, $entropy) / $offlineGuesses),
    ];
}

/**
 * Convert seconds to human-readable time
 */
function secondsToHuman(float $seconds): string
{
    if ($seconds < 1) {
        return 'instantly';
    }

    $units = [
        'years'   => 31536000,
        'days'    => 86400,
        'hours'   => 3600,
        'minutes' => 60,
        'seconds' => 1,
    ];

    foreach ($units as $label => $value) {
        if ($seconds >= $value) {
            return round($seconds / $value, 2) . " $label";
        }
    }

    return 'instantly';
}
