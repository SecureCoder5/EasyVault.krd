<?php
declare(strict_types=1);

function passwordStrength(string $password): array
{
    $score = 0;

    if (strlen($password) >= 12) $score += 30;
    if (preg_match('/[A-Z]/', $password)) $score += 15;
    if (preg_match('/[a-z]/', $password)) $score += 15;
    if (preg_match('/[0-9]/', $password)) $score += 20;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 20;

    return [
        'score' => min($score, 100),
        'label' => $score >= 80 ? 'Strong' : ($score >= 50 ? 'Medium' : 'Weak')
    ];
}
