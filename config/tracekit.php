<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TraceKit API Key
    |--------------------------------------------------------------------------
    |
    | Your TraceKit API key. Get one at https://app.tracekit.dev
    |
    */
    'api_key' => env('TRACEKIT_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | TraceKit Endpoint
    |--------------------------------------------------------------------------
    |
    | The OTLP endpoint for sending traces. Default is TraceKit's hosted service.
    |
    */
    'endpoint' => env('TRACEKIT_ENDPOINT', 'https://app.tracekit.dev/v1/traces'),

    /*
    |--------------------------------------------------------------------------
    | Service Name
    |--------------------------------------------------------------------------
    |
    | The name of your service as it will appear in TraceKit.
    |
    */
    'service_name' => env('TRACEKIT_SERVICE_NAME', env('APP_NAME', 'laravel-app')),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Tracing
    |--------------------------------------------------------------------------
    |
    | Enable or disable tracing. Useful for local development.
    |
    */
    'enabled' => env('TRACEKIT_ENABLED', env('APP_ENV') !== 'local'),

    /*
    |--------------------------------------------------------------------------
    | Sample Rate
    |--------------------------------------------------------------------------
    |
    | Percentage of requests to trace (0.0 to 1.0). 1.0 = trace everything.
    |
    */
    'sample_rate' => env('TRACEKIT_SAMPLE_RATE', 1.0),

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific tracing features.
    |
    */
    'features' => [
        'http' => env('TRACEKIT_HTTP_ENABLED', true),
        'database' => env('TRACEKIT_DATABASE_ENABLED', true),
        'cache' => env('TRACEKIT_CACHE_ENABLED', true),
        'queue' => env('TRACEKIT_QUEUE_ENABLED', true),
        'redis' => env('TRACEKIT_REDIS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Routes
    |--------------------------------------------------------------------------
    |
    | Routes to exclude from tracing (e.g., health checks).
    |
    */
    'ignored_routes' => [
        '/health',
        '/up',
        '/_healthz',
    ],

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold (milliseconds)
    |--------------------------------------------------------------------------
    |
    | Queries slower than this will be highlighted in traces.
    |
    */
    'slow_query_threshold' => env('TRACEKIT_SLOW_QUERY_MS', 100),

    /*
    |--------------------------------------------------------------------------
    | Include Query Bindings
    |--------------------------------------------------------------------------
    |
    | Whether to include SQL query bindings in traces. Disable if handling
    | sensitive data.
    |
    */
    'include_query_bindings' => env('TRACEKIT_INCLUDE_BINDINGS', true),

    /*
    |--------------------------------------------------------------------------
    | Code Monitoring
    |--------------------------------------------------------------------------
    |
    | Enable live code debugging with breakpoints and variable inspection.
    |
    | - enabled: Master switch for code monitoring features
    | - poll_interval: How often to check for new breakpoints (in seconds)
    |   Supported intervals: 1, 5, 10, 15, 30, 60, 300, 600
    |   Lower values = faster breakpoint updates, higher server load
    | - max_variable_depth: How deep to inspect nested arrays/objects
    | - max_string_length: Maximum length of captured string values
    |
    */
    'code_monitoring' => [
        'enabled' => env('TRACEKIT_CODE_MONITORING_ENABLED', false),
        'poll_interval' => env('TRACEKIT_CODE_MONITORING_POLL_INTERVAL', 30), // seconds
        'max_variable_depth' => env('TRACEKIT_CODE_MONITORING_MAX_DEPTH', 3), // nested array/object depth
        'max_string_length' => env('TRACEKIT_CODE_MONITORING_MAX_STRING', 1000), // truncate long strings
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Name Mappings
    |--------------------------------------------------------------------------
    |
    | Map hostnames to service names for peer.service attribute in distributed
    | tracing. Useful for mapping localhost URLs to actual service names.
    |
    | Example: ['localhost:8082' => 'go-test-app', 'localhost:8084' => 'node-test-app']
    |
    */
    'service_name_mappings' => [
        'localhost:8082' => 'go-test-app',
        'localhost:8084' => 'node-test-app',
        'localhost:5001' => 'python-test-app',
        'localhost:8086' => 'php-test-app',
    ],

    /*
    |--------------------------------------------------------------------------
    | Suppress Errors
    |--------------------------------------------------------------------------
    |
    | Suppress OpenTelemetry internal error output (export failures, etc.)
    | Set to false in production if you want to see export errors in logs.
    |
    */
    'suppress_errors' => env('TRACEKIT_SUPPRESS_ERRORS', true),
];
