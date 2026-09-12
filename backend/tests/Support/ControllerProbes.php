<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\NotificationController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Services\AuthService;
use App\Services\CategoryService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\ProductService;

trait ControllerProbeResponse
{
    /** @var array<string, mixed> */
    private array $requestBody = [];

    /** @param array<string, mixed> $requestBody */
    public function setRequestBody(array $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    /** @return array<string, mixed> */
    protected function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /** @return array{statusCode: int, body: mixed} */
    protected function json($data, int $statusCode = 200): array
    {
        return ['statusCode' => $statusCode, 'body' => $data];
    }
}

final class AuthControllerProbe extends AuthController
{
    use ControllerProbeResponse;

    /** @param array<string, mixed> $body */
    public function __construct(AuthService $service, array $body = [])
    {
        parent::__construct($service);
        $this->setRequestBody($body);
    }
}

final class ProductControllerProbe extends ProductController
{
    use ControllerProbeResponse;

    /** @param array<string, mixed> $body */
    public function __construct(ProductService $service, array $body = [])
    {
        parent::__construct($service);
        $this->setRequestBody($body);
    }
}

final class OrderControllerProbe extends OrderController
{
    use ControllerProbeResponse;

    /** @param array<string, mixed> $body */
    public function __construct(OrderService $service, array $body = [])
    {
        parent::__construct($service);
        $this->setRequestBody($body);
    }
}

final class NotificationControllerProbe extends NotificationController
{
    use ControllerProbeResponse;

    /** @param array<string, mixed> $body */
    public function __construct(NotificationService $service, array $body = [])
    {
        parent::__construct($service);
        $this->setRequestBody($body);
    }
}

final class CategoryControllerProbe extends CategoryController
{
    use ControllerProbeResponse;

    public function __construct(CategoryService $service)
    {
        parent::__construct($service);
    }
}
