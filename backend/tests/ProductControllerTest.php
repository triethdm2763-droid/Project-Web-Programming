<?php

namespace Tests\Product;

use PHPUnit\Framework\TestCase;
use App\Controllers\ProductController;

class ProductControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_FILES = [];

        parent::tearDown();
    }

    /**
     * TC-CONTROLLER-01
     * detail không có ID -> 400
     */
    public function testDetailWithoutId(): void
    {
        $_GET = [];

        $controller = new ProductController();

        ob_start();
        $controller->detail();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertSame(400, http_response_code());
    }

    /**
     * TC-CONTROLLER-02
     * upload không đăng nhập -> 401
     */
    public function testUploadImageWithoutLogin(): void
    {
        $_SESSION = [];

        $controller = new ProductController();

        ob_start();
        $controller->uploadImage();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertSame(401, http_response_code());
    }

    /**
     * TC-CONTROLLER-03
     * upload đã đăng nhập nhưng không có file -> 400
     */
    public function testUploadImageWithoutFile(): void
    {
        $_SESSION['user_id'] = 1;
        $_FILES = [];

        $controller = new ProductController();

        ob_start();
        $controller->uploadImage();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertSame(400, http_response_code());
    }

    /**
     * TC-CONTROLLER-04
     * update không có ID -> 400
     */
    public function testUpdateWithoutId(): void
    {
        $_SESSION['user_id'] = 1;

        // getRequestBody() sẽ fallback về $_POST
        $_POST = [];

        $controller = new ProductController();

        ob_start();
        $controller->update();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertSame(
            'Thiếu tham số ID sản phẩm.',
            $response['error']
        );
        $this->assertSame(400, http_response_code());
    }

    /**
     * TC-CONTROLLER-05
     * delete không có ID -> 400
     */
    public function testDeleteWithoutId(): void
    {
        $_SESSION['user_id'] = 1;

        // getRequestBody() sẽ fallback về $_POST
        $_POST = [];

        $controller = new ProductController();

        ob_start();
        $controller->delete();
        $output = ob_get_clean();

        $this->assertJson($output);

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertSame(
            'Thiếu tham số ID sản phẩm.',
            $response['error']
        );
        $this->assertSame(400, http_response_code());
    }
}