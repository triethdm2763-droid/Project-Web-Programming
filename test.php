<?php
require_once __DIR__ . '/backend/src/config/Database.php';
require_once __DIR__ . '/backend/src/repositories/OrderRepository.php';

$repo = new \App\Repositories\OrderRepository();
echo json_encode($repo->findBySeller(2));
