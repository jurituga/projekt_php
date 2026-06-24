<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;

/**
 * NativePHP stores compiled Blade views under Application Support. If a cached
 * file has a newer mtime than the source .blade.php, Laravel keeps serving stale
 * HTML (missing nav items, dashboard sections, etc.). Refresh when watched views change.
 */
class NativeViewCache
{
    private const MARKER = 'framework/views/.vacanto-blade-stamp';

    /** @var list<string> */
    private const WATCHED_VIEWS = [
        'views/layouts/app.blade.php',
        'views/user/dashboard.blade.php',
        'views/jobs/show.blade.php',
        'views/notifications/index.blade.php',
        'views/user/cvs.blade.php',
        'views/components/dashboard-nav.blade.php',
        'views/auth/register.blade.php',
        'views/admin/users/review.blade.php',
        'views/components/admin-document.blade.php',
    ];

    public static function refreshIfStale(): void
    {
        if (! self::isNativeRuntime()) {
            return;
        }

        $stamp = self::currentStamp();
        $markerPath = storage_path(self::MARKER);

        if (is_file($markerPath) && file_get_contents($markerPath) === $stamp) {
            return;
        }

        Artisan::call('view:clear');

        if (! is_dir(dirname($markerPath))) {
            @mkdir(dirname($markerPath), 0755, true);
        }

        file_put_contents($markerPath, $stamp);
    }

    private static function isNativeRuntime(): bool
    {
        return config('nativephp-internal.running')
            || filter_var(env('NATIVEPHP_RUNNING', false), FILTER_VALIDATE_BOOL);
    }

    private static function currentStamp(): string
    {
        $parts = [config('nativephp.version', '1.0.0')];

        foreach (self::WATCHED_VIEWS as $relative) {
            $path = resource_path($relative);
            $parts[] = is_file($path) ? (string) filemtime($path) : '0';
        }

        return md5(implode('|', $parts));
    }
}
