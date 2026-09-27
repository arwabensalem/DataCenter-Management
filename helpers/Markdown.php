<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Formatage léger Markdown → HTML sûr (réponses advisor).
 */
final class Markdown
{
    public static function toHtml(string $text): string
    {
        $s = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $s = preg_replace('/^######\s+(.+)$/m', '<h6 class="ai-h">$1</h6>', $s) ?? $s;
        $s = preg_replace('/^#####\s+(.+)$/m', '<h6 class="ai-h">$1</h6>', $s) ?? $s;
        $s = preg_replace('/^####\s+(.+)$/m', '<h6 class="ai-h">$1</h6>', $s) ?? $s;
        $s = preg_replace('/^###\s+(.+)$/m', '<h5 class="ai-h">$1</h5>', $s) ?? $s;
        $s = preg_replace('/^##\s+(.+)$/m', '<h5 class="ai-h">$1</h5>', $s) ?? $s;
        $s = preg_replace('/^#\s+(.+)$/m', '<h5 class="ai-h">$1</h5>', $s) ?? $s;

        $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s) ?? $s;
        $s = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $s) ?? $s;
        $s = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $s) ?? $s;

        $s = preg_replace('/^\s*[-•]\s+(.+)$/m', '<li>$1</li>', $s) ?? $s;
        $s = preg_replace('/^\s*\d+\.\s+(.+)$/m', '<li>$1</li>', $s) ?? $s;
        $s = preg_replace('/(?:<li>.*?<\/li>\s*)+/s', '<ul class="ai-list mb-2">$0</ul>', $s) ?? $s;

        $s = preg_replace('/\n{2,}/', '</p><p class="ai-p">', $s) ?? $s;
        $s = str_replace("\n", '<br>', $s);

        if (!preg_match('/^\s*</', $s)) {
            $s = '<p class="ai-p">' . $s . '</p>';
        }

        return $s;
    }
}
