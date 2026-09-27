<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Internationalisation simple FR / EN (session).
 */
final class Lang
{
    public const FR = 'fr';
    public const EN = 'en';

    /** @var array<string, string>|null */
    private static ?array $catalog = null;

    public static function boot(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            return;
        }
        if (!isset($_SESSION['lang']) || !in_array($_SESSION['lang'], [self::FR, self::EN], true)) {
            $_SESSION['lang'] = self::FR;
        }
        self::$catalog = null;
    }

    public static function locale(): string
    {
        self::boot();
        return (string) ($_SESSION['lang'] ?? self::FR);
    }

    public static function set(string $lang): void
    {
        $lang = strtolower($lang);
        if (!in_array($lang, [self::FR, self::EN], true)) {
            return;
        }
        $_SESSION['lang'] = $lang;
        self::$catalog = null;
    }

    public static function is(string $lang): bool
    {
        return self::locale() === $lang;
    }

    public static function htmlLang(): string
    {
        return self::locale();
    }

    /**
     * Traduit une clé. Placeholders : :name
     *
     * @param array<string, string|int|float> $replace
     */
    public static function t(string $key, array $replace = []): string
    {
        $catalog = self::load();
        $text = $catalog[$key] ?? $key;

        foreach ($replace as $k => $v) {
            $text = str_replace(':' . $k, (string) $v, $text);
        }

        return $text;
    }

    /**
     * @return array<string, string>
     */
    private static function load(): array
    {
        if (self::$catalog !== null) {
            return self::$catalog;
        }

        $file = dirname(__DIR__) . '/lang/' . self::locale() . '.php';
        if (!is_file($file)) {
            $file = dirname(__DIR__) . '/lang/fr.php';
        }

        /** @var array<string, string> $catalog */
        $catalog = require $file;
        self::$catalog = $catalog;

        return self::$catalog;
    }
}
