<?php
declare(strict_types=1);

namespace Nexo\Sdk;

final class NexoClient
{
    public function __construct(private string $baseUrl) {}

    public function get(string $path, array $query = []): array
    {
        if ($query) {
            $path .= (str_contains($path, '?') ? '&' : '?') . http_build_query($query);
        }
        return $this->request('GET', $path);
    }

    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, $body);
    }

    public function createContent(string $typeId, array $data, ?string $createdBy = null): array
    {
        return $this->post('/v1/content', array_filter([
            'typeId' => $typeId,
            'data' => $data,
            'createdBy' => $createdBy,
        ], fn ($v) => $v !== null));
    }

    public function getContent(string $id): array
    {
        return $this->get("/v1/content/{$id}");
    }

    public function publishContent(string $id, ?string $publishedBy = null): array
    {
        return $this->post("/v1/content/{$id}/publish", $publishedBy ? ['publishedBy' => $publishedBy] : []);
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        $opts = ['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'ignore_errors' => true]];
        if ($body !== null) {
            $opts['http']['content'] = json_encode($body);
        }
        $result = file_get_contents(rtrim($this->baseUrl, '/') . $path, false, stream_context_create($opts));
        $decoded = json_decode($result ?: '', true);
        if (!is_array($decoded)) {
            throw new \RuntimeException("Invalid response from API: " . substr((string) $result, 0, 200));
        }
        if (isset($decoded['error'])) {
            throw new \RuntimeException($decoded['error']['message'] ?? 'API error');
        }
        return $decoded;
    }
}
