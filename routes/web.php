<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Service URLs for cross-service communication
define('GO_SERVICE_URL', 'http://localhost:8082');
define('NODE_SERVICE_URL', 'http://localhost:8084');
define('PYTHON_SERVICE_URL', 'http://localhost:5001');
define('PHP_SERVICE_URL', 'http://localhost:8086');

Route::get('/', function () {
    return response()->json([
        'service' => 'laravel-test-app',
        'message' => 'TraceKit Laravel Test Application',
        'endpoints' => [
            'GET /' => 'This endpoint',
            'GET /health' => 'Health check',
            'GET /test' => 'Code monitoring test',
            'GET /error-test' => 'Error test',
            'GET /checkout' => 'Checkout simulation',
            'GET /api/data' => 'Data endpoint (called by other services)',
            'GET /api/call-go' => 'Call Go service',
            'GET /api/call-node' => 'Call Node service',
            'GET /api/call-python' => 'Call Python service',
            'GET /api/call-php' => 'Call PHP service',
            'GET /api/call-all' => 'Call all services',
        ],
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'laravel-test-app',
        'timestamp' => now()->toISOString(),
    ]);
});

// Test routes for code monitoring
Route::get('/test', function () {
    // Poll for breakpoints occasionally
    if (rand(1, 5) === 1) { // 20% chance to poll
        app(\TraceKit\Laravel\SnapshotClient::class)->pollBreakpoints();
    }

    // Capture snapshot at route entry
    tracekit_snapshot('test-route-entry', [
        'route' => 'test',
        'method' => request()->method(),
        'timestamp' => now()->toISOString(),
    ]);

    // Simulate some processing
    $userId = rand(1, 1000);
    $cartTotal = rand(10, 500);

    // Another snapshot during processing
    tracekit_snapshot('test-processing', [
        'user_id' => $userId,
        'cart_total' => $cartTotal,
        'processing_step' => 'validation',
    ]);

    // Simulate database query
    $users = DB::table('users')->limit(5)->get();

    // Final snapshot
    tracekit_snapshot('test-complete', [
        'user_count' => count($users),
        'total_processed' => $cartTotal,
        'status' => 'success',
    ]);

    return response()->json([
        'message' => 'Code monitoring test completed!',
        'data' => [
            'user_id' => $userId,
            'cart_total' => $cartTotal,
            'users_found' => count($users),
        ]
    ]);
});

Route::get('/error-test', function () {
    // Poll for breakpoints occasionally
    if (rand(1, 5) === 1) { // 20% chance to poll
        app(\TraceKit\Laravel\SnapshotClient::class)->pollBreakpoints();
    }

    // Capture snapshot before error
    tracekit_snapshot('error-test-start', [
        'route' => 'error-test',
        'intent' => 'trigger_exception',
    ]);

    // This will trigger an exception that gets captured automatically
    throw new \Exception('This is a test exception for code monitoring!');
});

Route::get('/checkout', function () {
    // Poll for breakpoints occasionally
    if (rand(1, 5) === 1) { // 20% chance to poll
        app(\TraceKit\Laravel\SnapshotClient::class)->pollBreakpoints();
    }

    tracekit_snapshot('checkout-start', [
        'user_id' => request()->get('user_id', 123),
        'amount' => request()->get('amount', 99.99),
    ]);

    // Simulate checkout processing
    $userId = request()->get('user_id', 123);
    $amount = request()->get('amount', 99.99);

    // Process payment
    $result = processPayment($userId, $amount);

    tracekit_snapshot('checkout-complete', [
        'user_id' => $userId,
        'amount' => $amount,
        'payment_id' => $result['payment_id'],
        'status' => $result['status'],
    ]);

    return response()->json($result);
});

if (!function_exists('processPayment')) {
    function processPayment($userId, $amount) {
        // This function will automatically get captured when breakpoints are set
        tracekit_snapshot('payment-processing', [
            'user_id' => $userId,
            'amount' => $amount,
            'processing_at' => now()->toISOString(),
        ]);

        // Simulate payment processing
        if ($amount > 1000) {
            throw new \Exception('Payment amount exceeds limit');
        }

        // Simulate database save
        DB::table('users')->where('id', $userId)->increment('total_spent', $amount);

        return [
            'payment_id' => 'pay_' . uniqid(),
            'amount' => $amount,
            'status' => 'completed',
            'timestamp' => now()->toISOString(),
        ];
    }
}

// Cross-service communication endpoints

Route::get('/api/data', function () {
    // Data endpoint that can be called by other services for distributed tracing
    usleep(rand(10000, 50000)); // Simulate some processing

    return response()->json([
        'service' => 'laravel-test-app',
        'timestamp' => now()->toISOString(),
        'data' => [
            'framework' => 'Laravel',
            'php_version' => PHP_VERSION,
            'random_value' => rand(1, 100),
        ],
    ]);
});

Route::get('/api/call-go', function () {
    // Call Go service - demonstrates distributed tracing
    try {
        $response = Http::timeout(5)
            ->withHeaders(['traceparent' => request()->header('traceparent', '')])
            ->get(GO_SERVICE_URL . '/api/data');

        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'go-test-app',
            'response' => $response->json(),
            'status' => $response->status(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'go-test-app',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/api/call-node', function () {
    // Call Node service - demonstrates distributed tracing
    try {
        $response = Http::timeout(5)
            ->withHeaders(['traceparent' => request()->header('traceparent', '')])
            ->get(NODE_SERVICE_URL . '/api/data');

        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'node-test-app',
            'response' => $response->json(),
            'status' => $response->status(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'node-test-app',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/api/call-python', function () {
    // Call Python service - demonstrates distributed tracing
    try {
        $response = Http::timeout(5)
            ->withHeaders(['traceparent' => request()->header('traceparent', '')])
            ->get(PYTHON_SERVICE_URL . '/api/data');

        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'python-test-app',
            'response' => $response->json(),
            'status' => $response->status(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'python-test-app',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/api/call-php', function () {
    // Call PHP service - demonstrates distributed tracing
    try {
        $response = Http::timeout(5)
            ->withHeaders(['traceparent' => request()->header('traceparent', '')])
            ->get(PHP_SERVICE_URL . '/api/data');

        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'php-test-app',
            'response' => $response->json(),
            'status' => $response->status(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'service' => 'laravel-test-app',
            'called' => 'php-test-app',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/api/call-all', function () {
    // Call all services - demonstrates distributed tracing across multiple services
    $results = [
        'service' => 'laravel-test-app',
        'chain' => [],
    ];

    $services = [
        ['name' => 'go-test-app', 'url' => GO_SERVICE_URL],
        ['name' => 'node-test-app', 'url' => NODE_SERVICE_URL],
        ['name' => 'python-test-app', 'url' => PYTHON_SERVICE_URL],
        ['name' => 'php-test-app', 'url' => PHP_SERVICE_URL],
    ];

    foreach ($services as $service) {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['traceparent' => request()->header('traceparent', '')])
                ->get($service['url'] . '/api/data');

            $results['chain'][] = [
                'service' => $service['name'],
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            $results['chain'][] = [
                'service' => $service['name'],
                'error' => $e->getMessage(),
            ];
        }
    }

    return response()->json($results);
});
