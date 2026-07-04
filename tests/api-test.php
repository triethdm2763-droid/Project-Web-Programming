<?php
// Quick integration test for local development
// 1) Connects to database via backend Database class
// 2) Queries products count
// 3) Calls products API endpoint and verifies JSON

chdir(__DIR__ . '/../'); // ensure project root

// include Database class directly (no composer required)
require_once __DIR__ . '/../backend/src/config/Database.php';

use App\Config\Database as DB;

echo "Running API tests...\n";

// 1) DB connection and products count
try {
	$db = DB::getInstance()->getConnection();
	$stmt = $db->query('SELECT COUNT(*) AS cnt FROM products');
	$row = $stmt->fetch();
	$cnt = $row ? (int)$row['cnt'] : 0;
	echo "DB: products count = $cnt\n";
} catch (Exception $e) {
	echo "DB connection/query failed: " . $e->getMessage() . "\n";
	exit(2);
}

// 2) Call repository directly (in-process) to compare results
require_once __DIR__ . '/../backend/src/core/BaseRepository.php';
require_once __DIR__ . '/../backend/src/repositories/ProductRepository.php';
use App\Repositories\ProductRepository;

try {
	$repo = new ProductRepository();
	$products = $repo->findAllActive([]);
	echo "Repo: findAllActive returned " . count($products) . " items\n";
} catch (Exception $e) {
	echo "Repo call failed: " . $e->getMessage() . "\n";
}

// 3) Call products API
$url = 'http://localhost/backend/public/index.php/api/products';
$opts = [
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_TIMEOUT => 5,
	CURLOPT_HTTPHEADER => ['Accept: application/json']
];

$ch = curl_init();
curl_setopt_array($ch, $opts);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
	echo "HTTP request failed: $err\n";
	exit(3);
}

$decoded = json_decode($res, true);
if ($decoded === null) {
	echo "API returned invalid JSON (raw):\n$res\n";
	exit(4);
}

if (is_array($decoded) && count($decoded) > 0) {
	echo "API: returned " . count($decoded) . " products — OK\n";
	exit(0);
} else {
	echo "API: returned empty list.\n";
	exit(1);
}

