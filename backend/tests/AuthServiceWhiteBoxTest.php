<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use App\Repositories\UserRepository;

class AuthServiceWhiteBoxTest extends TestCase
{
    private $repo;
    private $service;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(UserRepository::class);
        $this->service = new AuthService($this->repo);
    }
public function testRegisterValidationError()
{
    $result = $this->service->register([
        'username' => '',
        'email' => 'abc',
        'password' => ''
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testRegisterUsernameAlreadyExists()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn([
            'ID' => 1,
            'Username' => 'existing'
        ]);

    $result = $this->service->register([
        'username' => 'existing',
        'email' => 'test@gmail.com',
        'password' => '12345678'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(409, $result['code']);
}


public function testRegisterEmailAlreadyExists()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn(null);

    $this->repo
        ->method('findByEmail')
        ->willReturn([
            'ID' => 1,
            'Email' => 'test@gmail.com'
        ]);

    $result = $this->service->register([
        'username' => 'newuser',
        'email' => 'test@gmail.com',
        'password' => '12345678'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(409, $result['code']);
}


public function testRegisterSuccess()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn(null);

    $this->repo
        ->method('findByEmail')
        ->willReturn(null);

    $this->repo
        ->method('create')
        ->willReturn(10);

    $result = $this->service->register([
        'username' => 'newuser',
        'email' => 'new@gmail.com',
        'password' => '12345678'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(201, $result['code']);
    $this->assertEquals(10, $result['user_id']);
}
public function testLoginValidationError()
{
    $result = $this->service->login([
        'username' => '',
        'password' => ''
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testLoginUserNotFound()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn(null);

    $this->repo
        ->method('findByEmail')
        ->willReturn(null);

    $result = $this->service->login([
        'username' => 'unknown@gmail.com',
        'password' => '123456'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(401, $result['code']);
}


public function testLoginWrongPassword()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn([
            'ID' => 1,
            'Username' => 'user1',
            'Password' => password_hash('correctpass', PASSWORD_BCRYPT),
            'Status' => 'active'
        ]);

    $result = $this->service->login([
        'username' => 'user1',
        'password' => 'wrongpass'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(401, $result['code']);
}


public function testLoginAccountBlocked()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn([
            'ID' => 1,
            'Username' => 'user1',
            'Password' => password_hash('123456', PASSWORD_BCRYPT),
            'Status' => 'blocked'
        ]);

    $result = $this->service->login([
        'username' => 'user1',
        'password' => '123456'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(403, $result['code']);
}


public function testLoginSuccess()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn([
            'ID' => 1,
            'Username' => 'user1',
            'Password' => password_hash('123456', PASSWORD_BCRYPT),
            'Status' => 'active'
        ]);

    $result = $this->service->login([
        'username' => 'user1',
        'password' => '123456'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(200, $result['code']);
}
public function testGetCurrentUserNotLoggedIn()
{
    $_SESSION = [];

    $result = $this->service->getCurrentUser();

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(401, $result['code']);
}


public function testGetCurrentUserUserNotFound()
{
    $_SESSION['user_id'] = 999;

    $this->repo
        ->method('findById')
        ->willReturn(null);

    $result = $this->service->getCurrentUser();

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(404, $result['code']);
}


public function testGetCurrentUserSuccess()
{
    $_SESSION['user_id'] = 1;

    $this->repo
        ->method('findById')
        ->willReturn([
            'ID' => 1,
            'Username' => 'user1'
        ]);

    $result = $this->service->getCurrentUser();

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(200, $result['code']);
}
public function testUpdateProfileValidationError()
{
    $result = $this->service->updateProfile(1, [
        'fullname' => ''
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testUpdateProfileSuccess()
{
    $this->repo
        ->method('updateProfile')
        ->willReturn(true);

    $result = $this->service->updateProfile(1, [
        'fullname' => 'Nguyen Van A',
        'phone' => '0901234567',
        'address' => 'TP HCM'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(200, $result['code']);
}


public function testUpdateProfileFail()
{
    $this->repo
        ->method('updateProfile')
        ->willReturn(false);

    $result = $this->service->updateProfile(1, [
        'fullname' => 'Nguyen Van A',
        'phone' => '0901234567',
        'address' => 'TP HCM'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(500, $result['code']);
}
public function testRequestPasswordResetValidationError()
{
    $result = $this->service->requestPasswordReset([
        'email' => ''
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testRequestPasswordResetUserNotFound()
{
    $this->repo
        ->method('findByEmail')
        ->willReturn(null);

    $result = $this->service->requestPasswordReset([
        'email' => 'unknown@gmail.com'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(404, $result['code']);
}


public function testRequestPasswordResetSuccess()
{
    $this->repo
        ->method('findByEmail')
        ->willReturn([
            'ID' => 1,
            'Email' => 'test@gmail.com'
        ]);

    $result = $this->service->requestPasswordReset([
        'email' => 'test@gmail.com'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(200, $result['code']);

    $this->assertArrayHasKey('otp', $result);
}
public function testResetPasswordValidationError()
{
    $result = $this->service->resetPassword([
        'otp' => '',
        'password' => ''
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testResetPasswordSessionInvalid()
{
    $_SESSION = [];

    $result = $this->service->resetPassword([
        'otp' => '123456',
        'password' => 'newpass'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testResetPasswordOtpExpired()
{
    $_SESSION['reset_email'] = 'test@gmail.com';
    $_SESSION['reset_otp'] = '123456';
    $_SESSION['reset_expiry'] = time() - 10;

    $result = $this->service->resetPassword([
        'otp' => '123456',
        'password' => 'newpass'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testResetPasswordWrongOtp()
{
    $_SESSION['reset_email'] = 'test@gmail.com';
    $_SESSION['reset_otp'] = '123456';
    $_SESSION['reset_expiry'] = time() + 300;

    $result = $this->service->resetPassword([
        'otp' => '999999',
        'password' => 'newpass'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(400, $result['code']);
}


public function testResetPasswordUserNotFound()
{
    $_SESSION['reset_email'] = 'test@gmail.com';
    $_SESSION['reset_otp'] = '123456';
    $_SESSION['reset_expiry'] = time() + 300;

    $this->repo
        ->method('findByEmail')
        ->willReturn(null);

    $result = $this->service->resetPassword([
        'otp' => '123456',
        'password' => 'newpass'
    ]);

    $this->assertEquals('error', $result['status']);
    $this->assertEquals(404, $result['code']);
}


public function testResetPasswordSuccess()
{
    $_SESSION['reset_email'] = 'test@gmail.com';
    $_SESSION['reset_otp'] = '123456';
    $_SESSION['reset_expiry'] = time() + 300;

    $this->repo
        ->method('findByEmail')
        ->willReturn([
            'ID' => 1,
            'Email' => 'test@gmail.com'
        ]);

    $this->repo
        ->method('updatePassword')
        ->willReturn(true);

    $result = $this->service->resetPassword([
        'otp' => '123456',
        'password' => 'newpassword'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(200, $result['code']);
}
public function testRegisterSuccessWithPhone()
{
    $this->repo
        ->method('findByUsername')
        ->willReturn(null);

    $this->repo
        ->method('findByEmail')
        ->willReturn(null);

    $this->repo
        ->expects($this->once())
        ->method('create')
        ->with(
            'newuser',
            'newphone@gmail.com',
            $this->anything(),
            '0901234567'
        )
        ->willReturn(20);

    $result = $this->service->register([
        'username' => 'newuser',
        'email' => 'newphone@gmail.com',
        'password' => '12345678',
        'phone' => '0901234567'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(201, $result['code']);
    $this->assertEquals(20, $result['user_id']);
}
public function testRequestPasswordResetStartsSessionWhenNone()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    $this->repo
        ->method('findByEmail')
        ->willReturn([
            'ID' => 1,
            'Email' => 'sessiontest@gmail.com'
        ]);

    $result = $this->service->requestPasswordReset([
        'email' => 'sessiontest@gmail.com'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(200, $result['code']);
}
public function testResetPasswordStartsSessionWhenNone()
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $_SESSION['reset_email'] = 'reset-session@gmail.com';
    $_SESSION['reset_otp'] = '123456';
    $_SESSION['reset_expiry'] = time() + 300;

    session_write_close();

    $this->repo
        ->method('findByEmail')
        ->willReturn([
            'ID' => 1,
            'Email' => 'reset-session@gmail.com'
        ]);

    $this->repo
        ->method('updatePassword')
        ->willReturn(true);

    $result = $this->service->resetPassword([
        'otp' => '123456',
        'password' => 'newpassword'
    ]);

    $this->assertEquals('success', $result['status']);
    $this->assertEquals(200, $result['code']);
}
}
