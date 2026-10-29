Route::get('/health', function (\Illuminate\Http\Request $request) {
    $start = microtime(true);
    $dbTimeMs = null;
    $dbStatus = 'skipped';

    if ($request->boolean('db')) {
        $dbStart = microtime(true);
        try {
            \Illuminate\Support\Facades\DB::select('SELECT 1');
            $dbStatus = 'ok';
        } catch (\Throwable $e) {
            $dbStatus = 'error';
        }
        $dbTimeMs = round((microtime(true) - $dbStart) * 1000, 2);
    }

    $appTimeMs = round((microtime(true) - $start) * 1000, 2);

    return response()->json([
        'status' => 'ok',
        'locale' => app()->getLocale(),
        'session_driver' => config('session.driver'),
        'cache_store' => config('cache.default'),
        'db_status' => $dbStatus,
        'db_time_ms' => $dbTimeMs,
        'total_time_ms' => $appTimeMs,
    ])->withHeaders([
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
        'Server-Timing' => sprintf('app;dur=%s%s', $appTimeMs, $dbTimeMs !== null ? ", db;dur={$dbTimeMs}" : ''),
    ]);
})->name('health');