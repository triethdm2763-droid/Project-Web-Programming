<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuthServiceWhiteBoxTest extends TestCase
{
    /** @var UserRepository&MockObject */
    private UserRepository $repo;
    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_save_path(sys_get_temp_dir());
            @session_start();
        }
        $_SESSION = [];

        $this->repo = $this->createMock(UserRepository::class);
        $this->service = new AuthService($this->repo);
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
        parent::tearDown();
    }

    public function testRegisterValidationErrorDoesNotCallRepository(): void
    {
        $this->repo->expects(self::never())->method('findByUsername');
        $this->repo->expects(self::never())->method('findByEmail');
        $this->repo->expects(self::never())->method('create');

        $result = $this->service->register([
            'username' => '',
            'email' => 'abc',
            'password' => '',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        self::assertArrayHasKey('username', $result['errors']);
        self::assertArrayHasKey('email', $result['errors']);
        self::assertArrayHasKey('password', $result['errors']);
    }

    public function testRegisterUsernameAlreadyExistsStopsBeforeEmailLookup(): void
    {
        $this->repo->expects(self::once())
            ->method('findByUsername')->with('existing')
            ->willReturn(['ID' => 1, 'Username' => 'existing']);
        $this->repo->expects(self::never())->method('findByEmail');
        $this->repo->expects(self::never())->method('create');

        $result = $this->service->register([
            'username' => ' existing ',
            'email' => 'test@gmail.com',
            'password' => '12345678',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(409, $result['code']);
        self::assertArrayHasKey('username', $result['errors']);
    }

    public function testRegisterEmailAlreadyExistsDoesNotCreateUser(): void
    {
        $this->repo->expects(self::once())->method('findByUsername')->with('newuser')->willReturn(null);
        $this->repo->expects(self::once())
            ->method('findByEmail')->with('test@gmail.com')
            ->willReturn(['ID' => 1, 'Email' => 'test@gmail.com']);
        $this->repo->expects(self::never())->method('create');

        $result = $this->service->register([
            'username' => 'newuser',
            'email' => ' test@gmail.com ',
            'password' => '12345678',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(409, $result['code']);
        self::assertArrayHasKey('email', $result['errors']);
    }

    public function testRegisterSuccessWithoutPhoneUsesNullAndBcryptHash(): void
    {
        $this->repo->expects(self::once())->method('findByUsername')->with('newuser')->willReturn(null);
        $this->repo->expects(self::once())->method('findByEmail')->with('new@gmail.com')->willReturn(null);
        $this->repo->expects(self::once())
            ->method('create')
            ->with(
                'newuser',
                'new@gmail.com',
                self::callback(static fn (string $hash): bool =>
                    password_verify('12345678', $hash)
                    && password_get_info($hash)['algoName'] === 'bcrypt'),
                null
            )
            ->willReturn(10);

        $result = $this->service->register([
            'username' => 'newuser',
            'email' => 'new@gmail.com',
            'password' => '12345678',
        ]);

        self::assertSame(['status' => 'success', 'code' => 201, 'user_id' => 10], $result);
    }

    public function testRegisterSuccessWithPhoneTrimsPhone(): void
    {
        $this->repo->method('findByUsername')->willReturn(null);
        $this->repo->method('findByEmail')->willReturn(null);
        $this->repo->expects(self::once())
            ->method('create')
            ->with(
                'newuser',
                'newphone@gmail.com',
                self::callback(static fn (string $hash): bool => password_verify('12345678', $hash)),
                '0901234567'
            )
            ->willReturn(20);

        $result = $this->service->register([
            'username' => 'newuser',
            'email' => 'newphone@gmail.com',
            'password' => '12345678',
            'phone' => ' 0901234567 ',
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame(20, $result['user_id']);
    }

    public function testLoginValidationErrorDoesNotQueryRepository(): void
    {
        $this->repo->expects(self::never())->method('findByUsername');
        $this->repo->expects(self::never())->method('findByEmail');

        $result = $this->service->login(['username' => '', 'password' => '']);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
    }

    public function testLoginUserNotFoundChecksUsernameThenEmail(): void
    {
        $this->repo->expects(self::once())->method('findByUsername')->with('unknown@gmail.com')->willReturn(null);
        $this->repo->expects(self::once())->method('findByEmail')->with('unknown@gmail.com')->willReturn(null);

        $result = $this->service->login([
            'username' => ' unknown@gmail.com ',
            'password' => '123456',
        ]);

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }

    public function testLoginByEmailSuccessCoversFallbackBranch(): void
    {
        $user = $this->activeUser('emailuser', 'correctpass');
        $user['Email'] = 'emailuser@gmail.com';
        $this->repo->expects(self::once())->method('findByUsername')->with('emailuser@gmail.com')->willReturn(null);
        $this->repo->expects(self::once())->method('findByEmail')->with('emailuser@gmail.com')->willReturn($user);

        $result = $this->service->login([
            'username' => 'emailuser@gmail.com',
            'password' => 'correctpass',
        ]);

        self::assertSame('success', $result['status']);
        self::assertSame('emailuser@gmail.com', $result['user']['Email']);
        self::assertArrayNotHasKey('Password', $result['user']);
    }

    public function testLoginWrongPasswordStopsBeforeSuccess(): void
    {
        $this->repo->expects(self::once())
            ->method('findByUsername')->with('user1')
            ->willReturn($this->activeUser('user1', 'correctpass'));
        $this->repo->expects(self::never())->method('findByEmail');

        $result = $this->service->login(['username' => 'user1', 'password' => 'wrongpass']);

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }

    public function testLoginBannedAccountReturnsForbidden(): void
    {
        $user = $this->activeUser('user1', '123456');
        $user['Status'] = 'banned';
        $this->repo->method('findByUsername')->willReturn($user);

        $result = $this->service->login(['username' => 'user1', 'password' => '123456']);

        self::assertSame('error', $result['status']);
        self::assertSame(403, $result['code']);
    }

    public function testLoginSuccessReturnsUserWithoutPassword(): void
    {
        $this->repo->method('findByUsername')->willReturn($this->activeUser('user1', '123456'));

        $result = $this->service->login(['username' => 'user1', 'password' => '123456']);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertSame('user1', $result['user']['Username']);
        self::assertArrayNotHasKey('Password', $result['user']);
    }

    public function testGetCurrentUserNotLoggedInDoesNotQueryRepository(): void
    {
        $this->repo->expects(self::never())->method('findById');
        $result = $this->service->getCurrentUser();

        self::assertSame('error', $result['status']);
        self::assertSame(401, $result['code']);
    }

    public function testGetCurrentUserStartsSessionWhenNone(): void
    {
        $this->repo->expects(self::never())->method('findById');
        session_write_close();

        $result = $this->service->getCurrentUser();

        self::assertSame(PHP_SESSION_ACTIVE, session_status());
        self::assertSame(401, $result['code']);
    }

    public function testGetCurrentUserUserNotFound(): void
    {
        $_SESSION['user_id'] = '999';
        $this->repo->expects(self::once())->method('findById')->with(999)->willReturn(null);

        $result = $this->service->getCurrentUser();

        self::assertSame('error', $result['status']);
        self::assertSame(404, $result['code']);
    }

    public function testGetCurrentUserSuccessReturnsRepositoryUser(): void
    {
        $_SESSION['user_id'] = 1;
        $user = ['ID' => 1, 'Username' => 'user1'];
        $this->repo->expects(self::once())->method('findById')->with(1)->willReturn($user);

        $result = $this->service->getCurrentUser();

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertSame($user, $result['user']);
    }

    public function testUpdateProfileValidationErrorDoesNotUpdateRepository(): void
    {
        $this->repo->expects(self::never())->method('updateProfile');
        $result = $this->service->updateProfile(1, ['fullname' => '']);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        self::assertArrayHasKey('fullname', $result['errors']);
    }

    public function testUpdateProfileSuccessPassesExactData(): void
    {
        $data = ['fullname' => 'Nguyen Van A', 'phone' => '0901234567', 'address' => 'TP HCM'];
        $this->repo->expects(self::once())->method('updateProfile')->with(1, $data)->willReturn(true);

        $result = $this->service->updateProfile(1, $data);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
    }

    public function testUpdateProfileRepositoryFailureReturnsServerError(): void
    {
        $data = ['fullname' => 'Nguyen Van A', 'phone' => '0901234567', 'address' => 'TP HCM'];
        $this->repo->expects(self::once())->method('updateProfile')->with(1, $data)->willReturn(false);

        $result = $this->service->updateProfile(1, $data);

        self::assertSame('error', $result['status']);
        self::assertSame(500, $result['code']);
    }

    public function testRequestPasswordResetValidationErrorDoesNotQueryRepository(): void
    {
        $this->repo->expects(self::never())->method('findByEmail');
        $result = $this->service->requestPasswordReset(['email' => '']);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
    }

    public function testRequestPasswordResetUserNotFound(): void
    {
        $this->repo->expects(self::once())->method('findByEmail')->with('unknown@gmail.com')->willReturn(null);
        $result = $this->service->requestPasswordReset(['email' => ' unknown@gmail.com ']);

        self::assertSame('error', $result['status']);
        self::assertSame(404, $result['code']);
    }

    public function testRequestPasswordResetSuccessStoresSixDigitOtpForFiveMinutes(): void
    {
        $this->repo->expects(self::once())
            ->method('findByEmail')->with('test@gmail.com')
            ->willReturn(['ID' => 1, 'Email' => 'test@gmail.com']);

        $before = time();
        $result = $this->service->requestPasswordReset(['email' => ' test@gmail.com ']);
        $after = time();

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        self::assertMatchesRegularExpression('/^\d{6}$/', $result['otp']);
        self::assertSame('test@gmail.com', $_SESSION['reset_email']);
        self::assertSame($result['otp'], $_SESSION['reset_otp']);
        self::assertGreaterThanOrEqual($before + 300, $_SESSION['reset_expiry']);
        self::assertLessThanOrEqual($after + 300, $_SESSION['reset_expiry']);
    }

    public function testRequestPasswordResetStartsSessionWhenNone(): void
    {
        $this->repo->expects(self::once())
            ->method('findByEmail')->with('session@gmail.com')
            ->willReturn(['ID' => 2, 'Email' => 'session@gmail.com']);
        session_write_close();

        $result = $this->service->requestPasswordReset(['email' => 'session@gmail.com']);

        self::assertSame(PHP_SESSION_ACTIVE, session_status());
        self::assertSame('success', $result['status']);
        self::assertSame('session@gmail.com', $_SESSION['reset_email']);
    }

    public function testResetPasswordValidationErrorDoesNotQueryRepository(): void
    {
        $this->repo->expects(self::never())->method('findByEmail');
        $this->repo->expects(self::never())->method('updatePassword');
        $result = $this->service->resetPassword(['otp' => '', 'password' => '']);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
    }

    /** @param array<string, int|string> $session */
    #[DataProvider('incompleteResetSessionProvider')]
    public function testResetPasswordRejectsEachIncompleteSessionState(array $session): void
    {
        $_SESSION = $session;
        $this->repo->expects(self::never())->method('findByEmail');
        $this->repo->expects(self::never())->method('updatePassword');

        $result = $this->service->resetPassword(['otp' => '123456', 'password' => 'newpass']);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        self::assertStringContainsString('không hợp lệ', $result['message']);
    }

    public static function incompleteResetSessionProvider(): array
    {
        return [
            'missing email' => [['reset_otp' => '123456', 'reset_expiry' => 1]],
            'missing otp' => [['reset_email' => 'test@gmail.com', 'reset_expiry' => 1]],
            'missing expiry' => [['reset_email' => 'test@gmail.com', 'reset_otp' => '123456']],
        ];
    }

    public function testResetPasswordExpiredOtpClearsSession(): void
    {
        $this->setResetSession(time() - 10);
        $this->repo->expects(self::never())->method('findByEmail');
        $this->repo->expects(self::never())->method('updatePassword');
        $result = $this->service->resetPassword(['otp' => '123456', 'password' => 'newpass']);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        $this->assertResetSessionCleared();
    }

    public function testResetPasswordWrongOtpKeepsSessionForRetry(): void
    {
        $this->setResetSession(time() + 300);
        $this->repo->expects(self::never())->method('findByEmail');
        $this->repo->expects(self::never())->method('updatePassword');
        $result = $this->service->resetPassword(['otp' => '999999', 'password' => 'newpass']);

        self::assertSame('error', $result['status']);
        self::assertSame(400, $result['code']);
        self::assertSame('test@gmail.com', $_SESSION['reset_email']);
    }

    public function testResetPasswordUserNotFoundDoesNotUpdatePassword(): void
    {
        $this->setResetSession(time() + 300);
        $this->repo->expects(self::once())->method('findByEmail')->with('test@gmail.com')->willReturn(null);
        $this->repo->expects(self::never())->method('updatePassword');
        $result = $this->service->resetPassword(['otp' => ' 123456 ', 'password' => 'newpass']);

        self::assertSame('error', $result['status']);
        self::assertSame(404, $result['code']);
    }

    public function testResetPasswordRepositoryFailureReturnsServerErrorAndKeepsSession(): void
    {
        $this->setResetSession(time() + 300);
        $this->repo->expects(self::once())->method('findByEmail')->with('test@gmail.com')->willReturn(['ID' => 1]);
        $this->repo->expects(self::once())
            ->method('updatePassword')
            ->with(1, self::callback(static fn (string $hash): bool => password_verify('newpassword', $hash)))
            ->willReturn(false);

        $result = $this->service->resetPassword(['otp' => '123456', 'password' => 'newpassword']);

        self::assertSame('error', $result['status']);
        self::assertSame(500, $result['code']);
        self::assertSame('test@gmail.com', $_SESSION['reset_email']);
    }

    public function testResetPasswordSuccessUsesBcryptAndClearsSession(): void
    {
        $this->setResetSession(time() + 300);
        $this->repo->expects(self::once())->method('findByEmail')->with('test@gmail.com')->willReturn(['ID' => 1]);
        $this->repo->expects(self::once())
            ->method('updatePassword')
            ->with(
                1,
                self::callback(static fn (string $hash): bool =>
                    password_verify('newpassword', $hash)
                    && password_get_info($hash)['algoName'] === 'bcrypt')
            )
            ->willReturn(true);

        $result = $this->service->resetPassword(['otp' => '123456', 'password' => 'newpassword']);

        self::assertSame('success', $result['status']);
        self::assertSame(200, $result['code']);
        $this->assertResetSessionCleared();
    }

    public function testResetPasswordStartsSessionWhenNone(): void
    {
        $this->setResetSession(time() + 300);
        session_write_close();
        $this->repo->expects(self::once())->method('findByEmail')->with('test@gmail.com')->willReturn(['ID' => 1]);
        $this->repo->expects(self::once())->method('updatePassword')->willReturn(true);

        $result = $this->service->resetPassword(['otp' => '123456', 'password' => 'newpassword']);

        self::assertSame(PHP_SESSION_ACTIVE, session_status());
        self::assertSame('success', $result['status']);
        $this->assertResetSessionCleared();
    }

    /** @return array<string, mixed> */
    private function activeUser(string $username, string $password): array
    {
        return [
            'ID' => 1,
            'Username' => $username,
            'Email' => $username . '@gmail.com',
            'Password' => password_hash($password, PASSWORD_BCRYPT),
            'Role' => 'user',
            'Status' => 'active',
        ];
    }

    private function setResetSession(int $expiry): void
    {
        $_SESSION['reset_email'] = 'test@gmail.com';
        $_SESSION['reset_otp'] = '123456';
        $_SESSION['reset_expiry'] = $expiry;
    }

    private function assertResetSessionCleared(): void
    {
        self::assertArrayNotHasKey('reset_email', $_SESSION);
        self::assertArrayNotHasKey('reset_otp', $_SESSION);
        self::assertArrayNotHasKey('reset_expiry', $_SESSION);
    }
}
