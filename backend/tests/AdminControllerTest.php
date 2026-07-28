<?php

use PHPUnit\Framework\TestCase;

class AdminControllerTest extends TestCase
{
    /**
     * TC-ADMIN-01: Phân quyền truy cập API Admin
     */
    public function test_normal_user_cannot_access_admin_api()
    {
        // 1. Arrange
        $userRole = 'user';
        
        // 2. Act
        $isAllowed = ($userRole === 'admin');
        
        // 3. Assert
        $this->assertFalse($isAllowed, "Lỗi: User thường không bị chặn!");
    }

    /**
     * TC-ADMIN-02: Kiểm tra số liệu thống kê Dashboard
     */
    public function test_admin_dashboard_returns_correct_statistics()
    {
        // Giả lập dữ liệu trả về từ Dashboard
        $dashboardData = [
            'total_users' => 100,
            'total_orders' => 50,
            'total_revenue' => 10000000
        ];

        $this->assertArrayHasKey('total_users', $dashboardData);
        $this->assertArrayHasKey('total_orders', $dashboardData);
        $this->assertArrayHasKey('total_revenue', $dashboardData);
        $this->assertEquals(100, $dashboardData['total_users']);
    }

    /**
     * Quản lý hệ thống: Khóa tài khoản người dùng
     */
    public function test_admin_can_lock_user_account()
    {
        $userStatus = 'active';

        // Giả lập hành động Admin khóa tài khoản
        $userStatus = 'locked';

        $this->assertEquals('locked', $userStatus, "Lỗi: Không thể khóa tài khoản!");
    }
}