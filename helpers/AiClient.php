<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Client HTTP vers le service Python RAG.
 * Timeout, validation, jamais de secrets dans les logs.
 */
final class AiClient
{
    /**
     * @param array<string, mixed> $payload
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, http_code?: int}
     */
    public static function query(array $payload): array
    {
        $base = rtrim((string) Env::get('RAG_API_URL', 'http://127.0.0.1:8000'), '/');
        $url = $base . '/api/rag/query';
        $timeout = Env::getInt('RAG_TIMEOUT_SECONDS', 30);

        return self::postJson($url, $payload, $timeout);
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, http_code?: int}
     */
    public static function health(): array
    {
        $base = rtrim((string) Env::get('RAG_API_URL', 'http://127.0.0.1:8000'), '/');
        $url = $base . '/api/rag/health';
        $timeout = min(5, Env::getInt('RAG_TIMEOUT_SECONDS', 30));

        return self::getJson($url, $timeout);
    }

    /**
     * Orchestration : contexte + RAG + fallback.
     *
     * @param array<string, mixed>              $businessContext
     * @param list<array{role: string, content: string}> $history
     * @return array<string, mixed>
     */
    public static function ask(
        string $question,
        array $businessContext,
        ?int $dataCenterId = null,
        ?int $userId = null,
        array $history = [],
        string $mode = 'chat'
    ): array {
        $question = trim($question);
        if (mb_strlen($question) < 3) {
            return [
                'answer'   => 'Veuillez poser une question plus précise (3 caractères minimum).',
                'sources'  => [],
                'metrics'  => ['fallback' => true],
                'recommendations' => [],
                'fallback' => true,
            ];
        }
        if (mb_strlen($question) > 2000) {
            $question = mb_substr($question, 0, 2000);
        }

        // Limiter l'historique envoyé
        $history = array_slice($history, -8);

        $payload = [
            'question'          => $question,
            'data_center_id'    => $dataCenterId,
            'user_id'           => $userId,
            'business_context'  => $businessContext,
            'history'           => $history,
            'mode'              => $mode,
            'top_k'             => 5,
        ];

        $result = self::query($payload);
        if ($result['ok'] && isset($result['data']) && is_array($result['data'])) {
            $data = $result['data'];
            $data['fallback'] = false;
            return $data;
        }

        self::logError('RAG query failed: ' . ($result['error'] ?? 'unknown'));

        $fallback = AiFallback::analyze($businessContext, $question);
        $fallback['error_detail'] = $result['error'] ?? 'service_unavailable';

        return $fallback;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, http_code?: int}
     */
    private static function postJson(string $url, array $payload, int $timeout): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            return ['ok' => false, 'error' => 'json_encode_failed'];
        }

        if (function_exists('curl_init')) {
            return self::curlRequest($url, $body, $timeout, true);
        }

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $body,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return ['ok' => false, 'error' => 'connection_failed'];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'invalid_json_response'];
        }

        return ['ok' => true, 'data' => $decoded, 'http_code' => 200];
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, http_code?: int}
     */
    private static function getJson(string $url, int $timeout): array
    {
        if (function_exists('curl_init')) {
            return self::curlRequest($url, null, $timeout, false);
        }

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "Accept: application/json\r\n",
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return ['ok' => false, 'error' => 'connection_failed'];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'invalid_json_response'];
        }

        return ['ok' => true, 'data' => $decoded, 'http_code' => 200];
    }

    /**
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, http_code?: int}
     */
    private static function curlRequest(string $url, ?string $body, int $timeout, bool $post): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'curl_init_failed'];
        }

        $headers = ['Accept: application/json'];
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
            CURLOPT_HTTPHEADER     => $headers,
        ];

        if ($post) {
            $headers[] = 'Content-Type: application/json';
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $body ?? '{}';
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'error' => $err !== '' ? $err : 'curl_exec_failed', 'http_code' => $code];
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'invalid_json_response', 'http_code' => $code];
        }

        if ($code >= 400) {
            $detail = is_string($decoded['detail'] ?? null) ? $decoded['detail'] : 'http_' . $code;
            return ['ok' => false, 'error' => $detail, 'http_code' => $code, 'data' => $decoded];
        }

        return ['ok' => true, 'data' => $decoded, 'http_code' => $code];
    }

    private static function logError(string $message): void
    {
        $dir = dirname(__DIR__) . '/rag/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = date('Y-m-d H:i:s') . ' | PHP | ' . $message . PHP_EOL;
        @file_put_contents($dir . '/php_ai.log', $line, FILE_APPEND);
    }
}
