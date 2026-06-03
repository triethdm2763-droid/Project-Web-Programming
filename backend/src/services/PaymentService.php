<?php
namespace App\Services;

use App\Repositories\PaymentRepository;

class PaymentService {
    private $paymentRepository;

    public function __construct() {
        $this->paymentRepository = new PaymentRepository();
    }

    /**
     * Update status of payment helper
     * 
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updatePaymentStatus(int $orderId, string $status): bool {
        return $this->paymentRepository->updateStatus($orderId, $status);
    }
}
