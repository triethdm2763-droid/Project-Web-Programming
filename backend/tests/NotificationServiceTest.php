<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use App\Services\NotificationService;



class NotificationServiceTest extends TestCase
{

    private NotificationService $service;



    protected function setUp():void
    {

        $this->service =
            new NotificationService();


        $_SESSION=[];

    }




        // TC01:Lấy notification khi chưa login
    public function testGetNotificationWithoutLogin()
    {


        $result =
            $this->service
                 ->getMyNotifications();



        $this->assertEquals(
            401,
            $result['code']
        );

    }





    // TC02: Send notification
    public function testSendNotification()
    {

        $result =
            $this->service
                 ->send(
                    1,
                    "Test Notification",
                    "Hello User"
                 );



        $this->assertIsBool(
            $result
        );

    }





    // TC03: Mark read khi chưa login   
    public function testMarkReadWithoutLogin()
    {


        $result =
            $this->service
                 ->markAsRead(1);



        $this->assertEquals(
            401,
            $result['code']
        );

    }





    // TC04: Return format get notification 
    public function testNotificationResponseFormat()
    {

        $_SESSION['user_id']=1;


        $result =
            $this->service
                 ->getMyNotifications();



        $this->assertArrayHasKey(
            'status',
            $result
        );


        $this->assertArrayHasKey(
            'data',
            $result
        );

    }





    // TC05: Mark notification response 
    public function testMarkReadResponse()
    {

        $_SESSION['user_id']=1;


        $result =
            $this->service
                 ->markAsRead(1);



        $this->assertArrayHasKey(
            'code',
            $result
        );

    }


}