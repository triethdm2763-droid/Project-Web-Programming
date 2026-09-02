<?php

use PHPUnit\Framework\TestCase;

class ProductStateTransitionTest extends TestCase
{
    private function processStateTransition($currentState, $event)
    {
        if ($currentState === 'pending' && $event === 'APPROVE') return 'active';
        if ($currentState === 'pending' && $event === 'REJECT') return 'rejected';
        if ($currentState === 'active' && $event === 'PURCHASE') return 'sold';

        // Invalid: Seller update/delete khi đã sold -> Giữ nguyên sold và chặn
        if ($currentState === 'sold' && ($event === 'UPDATE' || $event === 'DELETE')) {
            return 'sold';
        }

        return 'ERROR';
    }

    /**
     * @dataProvider stateTransitionProvider
     */
    public function testProductStateTransitions($testId, $startState, $event, $expectedEndState)
    {
        $actualEndState = $this->processStateTransition($startState, $event);
        $this->assertEquals($expectedEndState, $actualEndState, "Failed at Test Case: $testId");
    }

    public static function stateTransitionProvider()
    {
        return [
            'ST-PROD-01' => ['ST-PROD-01', 'pending', 'APPROVE', 'active'],
            'ST-PROD-02' => ['ST-PROD-02', 'pending', 'REJECT', 'rejected'],
            'ST-PROD-03' => ['ST-PROD-03', 'active', 'PURCHASE', 'sold'],
            'ST-PROD-04' => ['ST-PROD-04', 'sold', 'UPDATE', 'sold'],
        ];
    }
}