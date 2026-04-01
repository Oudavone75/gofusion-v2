<?php

namespace App\Helpers;

class EmailPrivacyDetector
{
    /**
     * List of known privacy email service domains
     */
    private static array $privacyDomains = [
        // Apple Private Relay
        'privaterelay.appleid.com',
        'icloud.com', // Apple's Hide My Email

        // Firefox Relay
        'relay.firefox.com',
        'mozmail.com',

        // DuckDuckGo
        'duck.com',

        // FastMail
        'fastmail.com',

        // ProtonMail
        'protonmail.com',
        'proton.me',
        'pm.me',

        // SimpleLogin
        'simplelogin.com',
        'simplelogin.co',
        'slmail.me',
        'aleeas.com',

        // AnonAddy
        'anonaddy.me',
        'anonaddy.com',

        // Blur (formerly MaskMe)
        'abine.com',

        // Guerrilla Mail (temporary)
        'guerrillamail.com',
        'guerrillamail.net',

        // 10 Minute Mail
        '10minutemail.com',

        // Temp Mail
        'tempmail.com',
    ];

    /**
     * Check if an email is from Apple Private Relay
     */
    public static function isApplePrivateRelay(string $email): bool
    {
        return str_ends_with(strtolower($email), '@privaterelay.appleid.com');
    }

    /**
     * Check if an email is from any privacy/relay service
     */
    public static function isPrivacyEmail(string $email): bool
    {
        $email = strtolower(trim($email));
        $domain = substr(strrchr($email, '@'), 1);

        return in_array($domain, self::$privacyDomains);
    }

    /**
     * Get the type of privacy service
     */
    public static function getPrivacyServiceType(string $email): ?string
    {
        $email = strtolower(trim($email));
        $domain = substr(strrchr($email, '@'), 1);

        $serviceMap = [
            'privaterelay.appleid.com' => 'Apple Private Relay',
            'icloud.com' => 'Apple Hide My Email',
            'relay.firefox.com' => 'Firefox Relay',
            'mozmail.com' => 'Firefox Relay',
            'duck.com' => 'DuckDuckGo Email Protection',
            'fastmail.com' => 'FastMail',
            'protonmail.com' => 'ProtonMail',
            'proton.me' => 'ProtonMail',
            'pm.me' => 'ProtonMail',
            'simplelogin.com' => 'SimpleLogin',
            'simplelogin.co' => 'SimpleLogin',
            'slmail.me' => 'SimpleLogin',
            'aleeas.com' => 'SimpleLogin',
            'anonaddy.me' => 'AnonAddy',
            'anonaddy.com' => 'AnonAddy',
            'abine.com' => 'Blur',
            'guerrillamail.com' => 'Guerrilla Mail (Temporary)',
            'guerrillamail.net' => 'Guerrilla Mail (Temporary)',
            '10minutemail.com' => '10 Minute Mail (Temporary)',
            'tempmail.com' => 'Temp Mail (Temporary)',
        ];

        return $serviceMap[$domain] ?? null;
    }

    /**
     * Check if email is from a temporary email service
     */
    public static function isTemporaryEmail(string $email): bool
    {
        $serviceType = self::getPrivacyServiceType($email);

        return $serviceType && str_contains($serviceType, 'Temporary');
    }

    /**
     * Validate Apple Private Relay email format
     */
    public static function isValidApplePrivateRelayFormat(string $email): bool
    {
        // Apple Private Relay format: random_string@privaterelay.appleid.com
        $pattern = '/^[a-z0-9]+@privaterelay\.appleid\.com$/i';

        return (bool) preg_match($pattern, $email);
    }

    /**
     * Get email analysis
     */
    public static function analyzeEmail(string $email): array
    {
        return [
            'email' => $email,
            'is_privacy_email' => self::isPrivacyEmail($email),
            'is_apple_private_relay' => self::isApplePrivateRelay($email),
            'is_temporary' => self::isTemporaryEmail($email),
            'service_type' => self::getPrivacyServiceType($email),
        ];
    }
}

// Usage examples:
// EmailPrivacyDetector::isApplePrivateRelay('kpv4w7g7s2@privaterelay.appleid.com'); // true
// EmailPrivacyDetector::isPrivacyEmail('user@duck.com'); // true
// EmailPrivacyDetector::getPrivacyServiceType('kpv4w7g7s2@privaterelay.appleid.com'); // 'Apple Private Relay'
// EmailPrivacyDetector::analyzeEmail('kpv4w7g7s2@privaterelay.appleid.com');
