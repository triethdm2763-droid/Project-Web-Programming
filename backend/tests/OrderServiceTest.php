<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use App\Services\OrderService;


class OrderServiceTest extends TestCase
{

    private OrderService $orderService;


    protected function setUp(): void
    {
        $this->orderService = new OrderService();

        $_SESSION = [];
    }



    // TC01: Checkout thiếu product_id 
    public function testCheckoutMissingProductId()
    {

        $data = [
            'shipping_address'=>'123 Nguyen Van Cu',
            'payment_method'=>'COD'
        ];


        $result = $this->orderService
                       ->checkout($data);


        $this->assertEquals(
            'error',
            $result['status']
        );


        $this->assertEquals(
            400,
            $result['code']
        );
    }





    // TC02: Checkout thiếu phương thức thanh toán
    public function testCheckoutMissingPaymentMethod()
    {

        $data=[
            'product_id'=>1,
            'shipping_address'=>'Ho Chi Minh City'
        ];


        $result =
            $this->orderService
                 ->checkout($data);


        $this->assertEquals(
            'error',
            $result['status']
        );

    }





    // TC03: Product không tồn tại    
    public function testCheckoutProductNotFound()
    {

        $data=[
            'product_id'=>999999,
            'shipping_address'=>'Ho Chi Minh City',
            'payment_method'=>'COD'
        ];


        $result =
            $this->orderService
                 ->checkout($data);



        $this->assertEquals(
            404,
            $result['code']
        );


        $this->assertEquals(
            'error',
            $result['status']
        );

    }





    // TC04: Người dùng chưa đăng nhập hủy đơn 
    public function testCancelOrderWithoutLogin()
    {

        $_SESSION=[];


        $result =
            $this->orderService
                 ->cancelOrder([
                    'order_id'=>1
                 ]);



        $this->assertEquals(
            401,
            $result['code']
        );

    }





    // TC05: Không truyền order_id khi hủy 
    public function testCancelOrderMissingId()
    {

        $_SESSION['user_id']=1;


        $result =
            $this->orderService
                 ->cancelOrder([]);



        $this->assertEquals(
            400,
            $result['code']
        );

    }





    // TC06: Lấy lịch sử mua hàng khi chưa login 
    public function testBuyerHistoryWithoutLogin()
    {

        $_SESSION=[];


        $result =
            $this->orderService
                 ->getBuyerHistory();



        $this->assertEquals(
            401,
            $result['code']
        );

    }





    // TC07: Lấy đơn bán hàng khi chưa login 
    public function testSellerOrderWithoutLogin()
    {

        $_SESSION=[];


        $result =
            $this->orderService
                 ->getSellerOrders();



        $this->assertEquals(
            401,
            $result['code']
        );

    }





    // TC08: Update status thiếu dữ liệu 
    public function testUpdateStatusMissingData()
    {

        $_SESSION['user_id']=2;


        $result =
            $this->orderService
                 ->updateStatus([]);



        $this->assertEquals(
            400,
            $result['code']
        );

    }





    // TC09: Track order không tồn tại
    public function testTrackOrderNotFound()
    {

        $result =
            $this->orderService
                 ->trackOrder(
                    "INVALID_ORDER"
                 );


        $this->assertEquals(
            404,
            $result['code']
        );

    }





    // TC10: Kết quả trả về phải là array 
    public function testCheckoutReturnArray()
    {

        $result =
            $this->orderService
                 ->checkout([]);


        $this->assertIsArray(
            $result
        );

    }


}