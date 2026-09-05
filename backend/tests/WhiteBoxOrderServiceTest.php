<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\NotificationService;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * White-box tests for OrderService::checkout(), cancelOrder(), updateStatus().
 *
 * Test design follows whiteboxtesting.xlsx:
 *  - Statement Testing: ST-01 .. ST-37
 *  - Branch / Decision Testing: BD-01 .. BD-64
 *  - Branch Condition Testing: BC-01 .. BC-33
 */
final class WhiteBoxOrderServiceTest extends TestCase
{
    private OrderRepository $orders;
    private ProductRepository $products;
    private UserRepository $users;
    private NotificationService $notifications;
    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_COOKIE = [];
        $this->buildService();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    private function buildService(): void
    {
        $this->orders = $this->createMock(OrderRepository::class);
        $this->products = $this->createMock(ProductRepository::class);
        $this->users = $this->createMock(UserRepository::class);
        $this->notifications = $this->createMock(NotificationService::class);

        $this->service = new OrderService();
        $this->inject('orderRepository', $this->orders);
        $this->inject('productRepository', $this->products);
        $this->inject('userRepository', $this->users);
        $this->inject('notificationService', $this->notifications);
    }

    private function inject(string $property, object $value): void
    {
        $ref = new \ReflectionClass($this->service);
        $p = $ref->getProperty($property);
        $p->setAccessible(true);
        $p->setValue($this->service, $value);
    }

    private function product(
        int $sellerId = 99,
        int $stock = 5,
        string $status = 'active',
        float $price = 100000
    ): array {
        return [
            'ID' => 10,
            'Name' => 'Test Product',
            'Price' => $price,
            'Stock_quantity' => $stock,
            'Status' => $status,
            'Seller_ID' => $sellerId,
        ];
    }

    private function user(int $id = 1): array
    {
        return [
            'ID' => $id,
            'Fullname' => 'Existing User',
            'Phone' => '0900000000',
            'Address' => 'Existing address',
        ];
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'product_id' => 10,
            'quantity' => 1,
            'shipping_address' => '123 Nguyen Trai, Q1',
            'payment_method' => 'COD',
            'fullname' => 'Nguyen Van A',
            'phone' => '0901234567',
        ], $overrides);
    }

    private function order(
        int $buyerId = 1,
        int $sellerId = 99,
        string $status = 'pending',
        bool $withQuantity = true,
        ?int $buyerForNotification = null
    ): array {
        $o = [
            'ID' => 20,
            'Buyer_ID' => $buyerForNotification ?? $buyerId,
            'Seller_ID' => $sellerId,
            'Product_ID' => 10,
            'ProductName' => 'Test Product',
            'Status' => $status,
        ];
        if ($withQuantity) {
            $o['Quantity'] = 1;
        }
        return $o;
    }

    private function prepareCheckout(array $options = []): void
    {
        $loggedIn = $options['loggedIn'] ?? false;
        $buyerExists = $options['buyerExists'] ?? true;
        $product = $options['product'] ?? $this->product();
        $productExists = $options['productExists'] ?? true;
        $transaction = $options['transaction'] ?? 123;
        $profile = $options['profile'] ?? null;
        $notification = $options['notification'] ?? null;

        $_SESSION = $loggedIn
            ? ['user_id' => 1, 'username' => 'Buyer A']
            : [];

        if ($loggedIn) {
            if ($profile === 'first-user-second-null') {
                $calls = 0;
                $this->users->method('findById')->willReturnCallback(function () use (&$calls) {
                    $calls++;
                    return $calls === 1 ? $this->user(1) : null;
                });
            } else {
                $this->users->method('findById')->willReturn(
                    $buyerExists ? $this->user(1) : null
                );
            }
        }

        $this->products->method('findById')->willReturn(
            $productExists ? $product : null
        );

        if ($transaction instanceof Exception) {
            $this->orders->method('createWithTransaction')->willThrowException($transaction);
        } else {
            $this->orders->method('createWithTransaction')->willReturn((int)$transaction);
        }

        $this->notifications->method('send')->willReturn(true);
    }

    private function assertCode(array $result, int $expected): void
    {
        $this->assertSame($expected, $result['code']);
    }

    // ---------------------------------------------------------------------
    // 1. STATEMENT TESTING — ST-01 .. ST-37
    // ---------------------------------------------------------------------

    /** @dataProvider statementCases */
    public function testStatementTesting(string $id, string $function, int $expected, array $options = [], array $data = []): void
    {
        if ($id === 'ST-01') {
            $this->prepareCheckout();
            $data = $this->checkoutData(['product_id' => '']);
            $this->assertCode($this->service->checkout($data), 400);
            return;
        }

        if (str_starts_with($id, 'ST-')) {
            if (in_array($id, ['ST-18','ST-19','ST-20','ST-21','ST-22','ST-23','ST-24','ST-25'], true)) {
                $data = $this->prepareCancelForStatement($id);
                $this->assertCode($this->service->cancelOrder($data), $expected);
                return;
            }
            if (in_array($id, ['ST-26','ST-27','ST-28','ST-29','ST-30','ST-31','ST-32','ST-33','ST-34','ST-35','ST-36','ST-37'], true)) {
                $data = $this->prepareUpdateForStatement($id);
                $this->assertCode($this->service->updateStatus($data), $expected);
                return;
            }
        }

        $this->prepareCheckout($options);
        $this->assertCode($this->service->checkout($this->checkoutData($data)), $expected);
    }

    public static function statementCases(): array
    {
        return [
            'ST-01 checkout validation' => ['ST-01','checkout()',400],
            'ST-02 quantity default' => ['ST-02','checkout()',201,[], ['quantity'=>null]],
            'ST-03 quantity reset' => ['ST-03','checkout()',201,[], ['quantity'=>0]],
            'ST-04 guest' => ['ST-04','checkout()',201,['loggedIn'=>false]],
            'ST-05 invalid buyer session' => ['ST-05','checkout()',201,['loggedIn'=>true,'buyerExists'=>false]],
            'ST-06 product null' => ['ST-06','checkout()',404,['productExists'=>false]],
            'ST-07 unavailable' => ['ST-07','checkout()',400,['product'=>self::staticProduct('inactive',5)]],
            'ST-08 stock insufficient' => ['ST-08','checkout()',400,['product'=>self::staticProduct('active',1)], ['quantity'=>2]],
            'ST-09 self buy' => ['ST-09','checkout()',400,['loggedIn'=>true,'product'=>self::staticProduct('active',5,1)]],
            'ST-10 guest success' => ['ST-10','checkout()',201,['loggedIn'=>false]],
            'ST-11 logged-in success' => ['ST-11','checkout()',201,['loggedIn'=>true]],
            'ST-12 profile update' => ['ST-12','checkout()',201,['loggedIn'=>true]],
            'ST-13 profile user null' => ['ST-13','checkout()',201,['loggedIn'=>true,'profile'=>'first-user-second-null']],
            'ST-14 COD' => ['ST-14','checkout()',201,['loggedIn'=>false], ['payment_method'=>'COD']],
            'ST-15 Bank' => ['ST-15','checkout()',201,['loggedIn'=>false], ['payment_method'=>'Bank']],
            'ST-16 guest fullname' => ['ST-16','checkout()',201,['loggedIn'=>false], ['fullname'=>'Guest Name']],
            'ST-17 transaction exception' => ['ST-17','checkout()',500,['transaction'=>new Exception('DB error')]],

            'ST-18 cancel unauthenticated' => ['ST-18','cancelOrder()',401],
            'ST-19 cancel missing order id' => ['ST-19','cancelOrder()',400],
            'ST-20 cancel order null' => ['ST-20','cancelOrder()',404],
            'ST-21 cancel wrong buyer' => ['ST-21','cancelOrder()',403],
            'ST-22 cancel non-pending' => ['ST-22','cancelOrder()',400],
            'ST-23 cancel success' => ['ST-23','cancelOrder()',200],
            'ST-24 cancel no Quantity' => ['ST-24','cancelOrder()',200],
            'ST-25 cancel exception' => ['ST-25','cancelOrder()',500],

            'ST-26 update unauthenticated' => ['ST-26','updateStatus()',401],
            'ST-27 update missing order id' => ['ST-27','updateStatus()',400],
            'ST-28 update missing status' => ['ST-28','updateStatus()',400],
            'ST-29 update order null' => ['ST-29','updateStatus()',404],
            'ST-30 update wrong seller' => ['ST-30','updateStatus()',403],
            'ST-31 confirmed' => ['ST-31','updateStatus()',200],
            'ST-32 completed' => ['ST-32','updateStatus()',200],
            'ST-33 cancelled' => ['ST-33','updateStatus()',200],
            'ST-34 default status label' => ['ST-34','updateStatus()',200],
            'ST-35 buyer notification' => ['ST-35','updateStatus()',200],
            'ST-36 no buyer notification' => ['ST-36','updateStatus()',200],
            'ST-37 repository failure' => ['ST-37','updateStatus()',500],
        ];
    }

    private static function staticProduct(string $status, int $stock, int $sellerId = 99): array
    {
        return [
            'ID'=>10,'Name'=>'Test Product','Price'=>100000,
            'Stock_quantity'=>$stock,'Status'=>$status,'Seller_ID'=>$sellerId,
        ];
    }

    private function prepareCancelForStatement(string $id): array
    {
        if ($id === 'ST-18') {
            $_SESSION = [];
            return [];
        }
        $_SESSION = ['user_id'=>1,'username'=>'Buyer A'];
        $data = match ($id) {
            'ST-19' => ['order_id'=>''],
            default => ['order_id'=>20],
        };
        if ($id === 'ST-20') {
            $this->orders->method('findById')->willReturn(null);
        } elseif ($id === 'ST-21') {
            $this->orders->method('findById')->willReturn($this->order(2,99,'pending'));
        } elseif ($id === 'ST-22') {
            $this->orders->method('findById')->willReturn($this->order(1,99,'confirmed'));
        } elseif ($id === 'ST-23') {
            $this->orders->method('findById')->willReturn($this->order());
            $this->orders->method('cancelWithTransaction')->willReturn(null);
        } elseif ($id === 'ST-24') {
            $this->orders->method('findById')->willReturn($this->order(1,99,'pending',false));
            $this->orders->method('cancelWithTransaction')->willReturn(null);
        } elseif ($id === 'ST-25') {
            $this->orders->method('findById')->willReturn($this->order());
            $this->orders->method('cancelWithTransaction')->willThrowException(new Exception('DB error'));
        }
        return $data;
    }

    private function prepareUpdateForStatement(string $id): array
    {
        $_SESSION = [];
        if ($id === 'ST-26') {
            return ['order_id'=>20,'status'=>'confirmed'];
        }
        $_SESSION = ['user_id'=>99,'username'=>'Seller A'];
        $data = match ($id) {
            'ST-27' => ['order_id'=>'','status'=>'confirmed'],
            'ST-28' => ['order_id'=>20,'status'=>''],
            default => ['order_id'=>20,'status'=>'confirmed'],
        };
        if ($id === 'ST-28') {
            // Keep status empty to exercise the validation branch.
            return $data;
        } elseif ($id === 'ST-29') {
            $this->orders->method('findById')->willReturn(null);
        } elseif ($id === 'ST-30') {
            $this->orders->method('findById')->willReturn($this->order(1,88,'pending'));
        } else {
            $buyer = $id === 'ST-36' ? 0 : 1;
            $status = match ($id) {
                'ST-32' => 'completed',
                'ST-33' => 'cancelled',
                'ST-34' => 'pending',
                default => 'confirmed',
            };
            $this->orders->method('findById')->willReturn($this->order(1,99,'pending',true,$buyer));
            $this->orders->method('updateStatus')->willReturn($id === 'ST-37' ? false : true);
            $this->notifications->method('send')->willReturn(true);
            $data['status'] = $status;
        }
        return $data;
    }

    // ---------------------------------------------------------------------
    // 2. BRANCH / DECISION TESTING — BD-01 .. BD-64
    // ---------------------------------------------------------------------

    /** @dataProvider branchDecisionCases */
    public function testBranchDecisionTesting(string $id, string $function, int|string $expected, string $note = ''): void
    {
        if ($function === 'checkout()') {
            $this->runCheckoutBranchCase($id, $expected);
        } elseif ($function === 'cancelOrder()') {
            $this->runCancelBranchCase($id, $expected);
        } else {
            $this->runUpdateBranchCase($id, $expected);
        }
    }

    public static function branchDecisionCases(): array
    {
        $a = [];
        for ($i=1;$i<=32;$i++) {
            $expected = match($i) {
                1=>400, 2=>201, 3=>201, 4=>201, 5=>201, 6=>201,
                7=>201, 8=>201, 9=>201,10=>201,11=>404,12=>201,
                13=>400,14=>201,15=>400,16=>201,17=>400,18=>201,
                19=>201,20=>201,21=>201,22=>201,23=>201,24=>201,
                25=>201,26=>201,27=>201,28=>500,29=>201,30=>201,
                31=>201,32=>201,
            };
            $a["BD-$i checkout"] = ["BD-$i",'checkout()',$expected];
        }
        for ($i=33;$i<=46;$i++) {
            $expected = match($i) {
                34=>401,36=>400,37=>404,39=>403,42=>400,45=>200,46=>500,
                default=>200,
            };
            $a["BD-$i cancel"] = ["BD-$i",'cancelOrder()',$expected];
        }
        for ($i=47;$i<=64;$i++) {
            $expected = match($i) {
                48=>401,49=>400,51=>404,53=>403,56=>500,
                default=>200,
            };
            $a["BD-$i update"] = ["BD-$i",'updateStatus()',$expected];
        }
        return $a;
    }

    private function runCheckoutBranchCase(string $id, int|string $expected): void
    {
        $n = (int)substr($id,3);
        $data = $this->checkoutData();
        $opts = ['loggedIn'=>false, 'product'=>$this->product()];

        switch ($n) {
            case 1: $data['product_id']=''; break;
            case 2: $opts=['productExists'=>true]; break;
            case 3: $data['quantity']=2; break;
            case 4: unset($data['quantity']); break;
            case 5: $data['quantity']=0; break;
            case 6: $data['quantity']=2; break;
            case 7: $opts['loggedIn']=true; break;
            case 8: $opts['loggedIn']=false; break;
            case 9: $opts=['loggedIn'=>true]; break;
            case 10: $opts=['loggedIn'=>true,'buyerExists'=>false]; break;
            case 11: $opts=['productExists'=>false]; break;
            case 12: break;
            case 13: $opts['product']=$this->product(99,0,'inactive'); break;
            case 14: break;
            case 15: $data['quantity']=6; break;
            case 16: $data['quantity']=2; break;
            case 17: $opts=['loggedIn'=>true,'product'=>$this->product(1,5,'active')]; break;
            case 18: break;
            case 19: $opts=['loggedIn'=>false]; break;
            case 20: $opts=['loggedIn'=>true]; break;
            case 21: $opts=['loggedIn'=>true]; break;
            case 22: $opts=['loggedIn'=>false]; break;
            case 23: $opts=['loggedIn'=>true]; break;
            case 24: $opts=['loggedIn'=>true,'buyerExists'=>false]; break;
            case 25: $data['payment_method']='COD'; break;
            case 26: $data['payment_method']='Bank'; break;
            case 27: break;
            case 28: $opts['transaction']=new Exception('DB error'); break;
            case 29: $opts=['loggedIn'=>false]; break;
            case 30:
                $this->markTestSkipped('BD-30 is unreachable through current checkout(): fullname is required by Validator before guest-name branch.');
                return;
            case 31: $opts=['loggedIn'=>true]; break;
            case 32: $opts=['loggedIn'=>false]; break;
        }

        $this->prepareCheckout($opts);
        $this->assertCode($this->service->checkout($data), (int)$expected);
    }

    private function runCancelBranchCase(string $id, int|string $expected): void
    {
        $n=(int)substr($id,3);
        if ($n===34) $_SESSION=[];
        else $_SESSION=['user_id'=>1,'username'=>'Buyer A'];

        $data=['order_id'=>20];
        if ($n===36) $data=['order_id'=>''];

        if ($n===37) {
            $this->orders->method('findById')->willReturn(null);
        } elseif ($n===39) {
            $this->orders->method('findById')->willReturn($this->order(2,99,'pending'));
        } elseif ($n===42) {
            $this->orders->method('findById')->willReturn($this->order(1,99,'confirmed'));
        } elseif ($n===43 || $n===45) {
            $this->orders->method('findById')->willReturn($this->order(1,99,'pending',true));
            $this->orders->method('cancelWithTransaction')->willReturn(null);
        } elseif ($n===44) {
            $this->orders->method('findById')->willReturn($this->order(1,99,'pending',false));
            $this->orders->method('cancelWithTransaction')->willReturn(null);
        } elseif ($n===46) {
            $this->orders->method('findById')->willReturn($this->order());
            $this->orders->method('cancelWithTransaction')->willThrowException(new Exception('DB error'));
        } else {
            $this->orders->method('findById')->willReturn($this->order());
            $this->orders->method('cancelWithTransaction')->willReturn(null);
        }
        $this->notifications->method('send')->willReturn(true);
        $this->assertCode($this->service->cancelOrder($data), (int)$expected);
    }

    private function runUpdateBranchCase(string $id, int|string $expected): void
    {
        $n=(int)substr($id,3);
        if ($n===48) $_SESSION=[];
        else $_SESSION=['user_id'=>99,'username'=>'Seller A'];

        $data=['order_id'=>20,'status'=>'confirmed'];
        if ($n===49) $data=['order_id'=>'','status'=>'confirmed'];
        if ($n===50) $data=['order_id'=>20,'status'=>'confirmed'];
        if ($n===57 || $n===58) $data['status']='confirmed';
        if ($n===59) $data['status']='completed';
        if ($n===60) $data['status']='cancelled';
        if ($n===61) $data['status']='cancelled';
        if ($n===62) $data['status']='pending';

        if ($n===51) {
            $this->orders->method('findById')->willReturn(null);
        } elseif ($n===53) {
            $this->orders->method('findById')->willReturn($this->order(1,88,'pending'));
        } else {
            $buyer = in_array($n,[64],true) ? 0 : 1;
            $this->orders->method('findById')->willReturn($this->order(1,99,'pending',true,$buyer));
            $this->orders->method('updateStatus')->willReturn($n===56 ? false : true);
            $this->notifications->method('send')->willReturn(true);
        }
        $this->assertCode($this->service->updateStatus($data), (int)$expected);
    }

    // ---------------------------------------------------------------------
    // 3. BRANCH CONDITION TESTING — BC-01 .. BC-33
    // ---------------------------------------------------------------------

    /** @dataProvider branchConditionCases */
    public function testBranchConditionTesting(string $id, string $function, ?int $expected, bool $skip = false): void
    {
        if ($skip) {
            $this->markTestSkipped($id . ' is infeasible through the public method because Validator requires fullname, phone and shipping_address.');
        }

        if ($function === 'checkout()') {
            $this->runCheckoutConditionCase($id, $expected);
        } elseif ($function === 'cancelOrder()') {
            $this->runCancelConditionCase($id, $expected);
        } else {
            $this->runUpdateConditionCase($id, $expected);
        }
    }

    public static function branchConditionCases(): array
    {
        return [
            'BC-01'=>['BC-01','checkout()',400], 'BC-02'=>['BC-02','checkout()',400],
            'BC-03'=>['BC-03','checkout()',400], 'BC-04'=>['BC-04','checkout()',201],
            'BC-05'=>['BC-05','checkout()',201], 'BC-06'=>['BC-06','checkout()',201],
            'BC-07'=>['BC-07','checkout()',400], 'BC-08'=>['BC-08','checkout()',201],
            'BC-09'=>['BC-09','checkout()',null,true], 'BC-10'=>['BC-10','checkout()',null,true],
            'BC-11'=>['BC-11','checkout()',null,true], 'BC-12'=>['BC-12','checkout()',null,true],
            'BC-13'=>['BC-13','updateStatus()',400], 'BC-14'=>['BC-14','updateStatus()',400],
            'BC-15'=>['BC-15','updateStatus()',200],
            'BC-16'=>['BC-16','cancelOrder()',401], 'BC-17'=>['BC-17','cancelOrder()',200],
            'BC-18'=>['BC-18','cancelOrder()',200], 'BC-19'=>['BC-19','cancelOrder()',400],
            'BC-20'=>['BC-20','cancelOrder()',404], 'BC-21'=>['BC-21','cancelOrder()',200],
            'BC-22'=>['BC-22','cancelOrder()',403], 'BC-23'=>['BC-23','cancelOrder()',200],
            'BC-24'=>['BC-24','cancelOrder()',200], 'BC-25'=>['BC-25','cancelOrder()',400],
            'BC-26'=>['BC-26','updateStatus()',200], 'BC-27'=>['BC-27','updateStatus()',200],
            'BC-28'=>['BC-28','updateStatus()',200], 'BC-29'=>['BC-29','updateStatus()',200],
            'BC-30'=>['BC-30','updateStatus()',200], 'BC-31'=>['BC-31','updateStatus()',200],
            'BC-32'=>['BC-32','updateStatus()',200], 'BC-33'=>['BC-33','updateStatus()',200],
        ];
    }

    private function runCheckoutConditionCase(string $id, int $expected): void
    {
        $data=$this->checkoutData();
        $opts=['loggedIn'=>false,'product'=>$this->product(99,5,'active')];
        switch ($id) {
            case 'BC-01': $opts['product']=$this->product(99,0,'inactive'); break; // A=T, B=T
            case 'BC-02': $opts['product']=$this->product(99,5,'inactive'); break; // A=T, B=F
            case 'BC-03': $opts['product']=$this->product(99,0,'active'); break;   // A=F, B=T
            case 'BC-04': break; // A=F, B=F
            case 'BC-05': break; // buyer null
            case 'BC-06': $opts['loggedIn']=true; break; // buyer != seller
            case 'BC-07': $opts=['loggedIn'=>true,'product'=>$this->product(1,5,'active')]; break;
            case 'BC-08': break; // guest -> profile condition false
        }
        $this->prepareCheckout($opts);
        $this->assertCode($this->service->checkout($data),$expected);
    }

    private function runCancelConditionCase(string $id, int $expected): void
    {
        $_SESSION=['user_id'=>1,'username'=>'Buyer A'];
        $data=['order_id'=>20];
        switch ($id) {
            case 'BC-16': $_SESSION=[]; break;
            case 'BC-19': $data=['order_id'=>'']; break;
            case 'BC-20': $this->orders->method('findById')->willReturn(null); break;
            case 'BC-22': $this->orders->method('findById')->willReturn($this->order(2,99,'pending')); break;
            case 'BC-24':
            case 'BC-21':
            case 'BC-18':
            case 'BC-17':
                $this->orders->method('findById')->willReturn($this->order());
                $this->orders->method('cancelWithTransaction')->willReturn(null);
                break;
            case 'BC-25': $this->orders->method('findById')->willReturn($this->order(1,99,'confirmed')); break;
            case 'BC-23': $this->orders->method('findById')->willReturn($this->order()); break;
        }
        $this->notifications->method('send')->willReturn(true);
        $this->assertCode($this->service->cancelOrder($data),$expected);
    }

    private function runUpdateConditionCase(string $id, int $expected): void
    {
        $_SESSION=['user_id'=>99,'username'=>'Seller A'];
        $data=['order_id'=>20,'status'=>'confirmed'];
        if ($id==='BC-13') $data=['order_id'=>'','status'=>'confirmed'];
        if ($id==='BC-14') $data=['order_id'=>20,'status'=>''];
        if ($id==='BC-26') $data['status']='confirmed';
        if ($id==='BC-27') $data['status']='completed';
        if ($id==='BC-28') $data['status']='completed';
        if ($id==='BC-29') $data['status']='cancelled';
        if ($id==='BC-30') $data['status']='cancelled';
        if ($id==='BC-31') $data['status']='pending';

        $buyer = in_array($id,['BC-33'],true) ? 0 : 1;
        $this->orders->method('findById')->willReturn($this->order(1,99,'pending',true,$buyer));
        $this->orders->method('updateStatus')->willReturn(true);
        $this->notifications->method('send')->willReturn(true);
        $this->assertCode($this->service->updateStatus($data),$expected);
    }
}
