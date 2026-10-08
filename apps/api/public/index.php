<?php
declare(strict_types=1);

$baseDir = __DIR__;
while (!is_dir($baseDir . '/vendor') && $baseDir !== '/') {
    $baseDir = dirname($baseDir);
}
$vendorPath = $baseDir . '/vendor/autoload.php';
if (!is_file($vendorPath)) {
    $vendorPath = __DIR__ . '/../../../../vendor/autoload.php';
}
require_once $vendorPath;

use Nexo\Api\Application;
use Nexo\Content\ContentEntry;
use Nexo\Content\ContentId;
use Nexo\Content\ContentRepository;
use Nexo\Content\ContentStatus;
use Nexo\Http\HttpRequest;
use Nexo\Http\HttpResponse;
use Nexo\Http\Router;

$app = new Application();
$app->boot();
$container = $app->getContainer();

$serialize = static function (ContentEntry $entry): array {
    return [
        'id' => $entry->id(),
        'type' => $entry->typeId(),
        'status' => $entry->status()->value,
        'revision' => $entry->revisionNumber(),
        'data' => $entry->data(),
        'created_at' => $entry->createdAt()->format(\DateTimeInterface::ATOM),
        'updated_at' => $entry->updatedAt()->format(\DateTimeInterface::ATOM),
        'created_by' => $entry->createdBy(),
    ];
};

$router = new Router();

$router->get('/', fn () => [
    'name' => 'Nexo',
    'version' => $container->get('app')->version(),
    'status' => 'foundation-ready',
]);

$router->get('/health', function () use ($container) {
    $result = $container->get(\Nexo\Observability\HealthRegistry::class)->check();
    return HttpResponse::json($result, $result['healthy'] ? 200 : 503);
});

$router->get('/v1/content', function (HttpRequest $req) use ($container, $serialize) {
    $repo = $container->get(ContentRepository::class);
    $criteria = [];
    if (isset($req->query['type'])) {
        $criteria['type_id'] = $req->query['type'];
    }
    if (isset($req->query['status'])) {
        $criteria['status'] = $req->query['status'];
    }

    $items = [];
    foreach ($repo->findBy($criteria) as $entry) {
        $items[] = $entry;
    }

    $limit = max(1, min(100, (int) ($req->query['limit'] ?? 50)));
    $offset = max(0, (int) ($req->query['offset'] ?? 0));
    $slice = array_slice($items, $offset, $limit);

    return HttpResponse::json([
        'items' => array_map($serialize, $slice),
        'total' => count($items),
        'limit' => $limit,
        'offset' => $offset,
    ]);
});

$router->post('/v1/content', function (HttpRequest $req) use ($container, $serialize) {
    $typeId = $req->body['typeId'] ?? null;
    $data = $req->body['data'] ?? null;
    if (!is_string($typeId) || $typeId === '' || !is_array($data)) {
        return HttpResponse::error('validation_error', 'Fields typeId (string) and data (object) are required', 422);
    }

    $entry = new ContentEntry(
        ContentId::generate(),
        $typeId,
        $data,
        ContentStatus::DRAFT,
        isset($req->body['createdBy']) ? (string) $req->body['createdBy'] : null
    );
    $container->get(ContentRepository::class)->save($entry);

    return HttpResponse::json($serialize($entry), 201);
});

$router->get('/v1/content/:id', function (HttpRequest $req) use ($container, $serialize) {
    try {
        $id = ContentId::create($req->params['id']);
    } catch (\InvalidArgumentException) {
        return HttpResponse::error('validation_error', 'id must be a UUID', 422);
    }
    $entry = $container->get(ContentRepository::class)->findById($id);
    return $entry ? HttpResponse::json($serialize($entry)) : HttpResponse::error('not_found', 'Content not found', 404);
});

$router->post('/v1/content/:id/publish', function (HttpRequest $req) use ($container, $serialize) {
    try {
        $id = ContentId::create($req->params['id']);
    } catch (\InvalidArgumentException) {
        return HttpResponse::error('validation_error', 'id must be a UUID', 422);
    }
    $repo = $container->get(ContentRepository::class);
    $entry = $repo->findById($id);
    if ($entry === null) {
        return HttpResponse::error('not_found', 'Content not found', 404);
    }
    $result = $entry->publish(isset($req->body['publishedBy']) ? (string) $req->body['publishedBy'] : null);
    if ($result->isErr()) {
        return HttpResponse::error('state_error', $result->unwrapErr()->getMessage(), 409);
    }
    $repo->save($entry);
    return HttpResponse::json($serialize($entry));
});

$request = HttpRequest::fromGlobals();
$router->dispatch($request)->send();
