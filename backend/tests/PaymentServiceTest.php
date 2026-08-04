<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Services\PaymentService;


class PaymentServiceTest extends TestCase
{

    private PaymentService $service;


    protected function setUp(): void
    {
        $this->service = new PaymentService();
    }



    // TC01: Cập nhật trạng thái thanh toán
    public function testUpdatePaymentStatus()
    {

        $result =
            $this->service->updatePaymentStatus(
                1,
                "success"
            );


        $this->assertIsBool($result);

    }



    // TC02: Order ID không tồn tại
    public function testUpdatePaymentInvalidOrder()
    {

        $result =
            $this->service->updatePaymentStatus(
                999999,
                "success"
            );


        $this->assertIsBool($result);

    }




    // TC03: Kiểm tra các trạng thái thanh toán
    public function testPaymentAcceptStatus()
    {

        $statusList = [
            "pending",
            "success",
            "failed"
        ];


        foreach ($statusList as $status)
        {

            $result =
                $this->service
                     ->updatePaymentStatus(
                        1,
                        $status
                     );


            $this->assertIsBool($result);

        }

    }


}