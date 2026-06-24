<?php

namespace App\Support;

class ConfigureVacantoFilesystem
{
    public static function apply(): void
    {
        $nativeStorage = config('nativephp-internal.storage_path')
            ?: env('NATIVEPHP_STORAGE_PATH');

        if (! config('nativephp-internal.running') && ! filter_var(env('NATIVEPHP_RUNNING', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        if (! is_string($nativeStorage) || $nativeStorage === '') {
            return;
        }

        config([
            'filesystems.disks.vacanto.root' => rtrim($nativeStorage, '/').'/app',
        ]);
    }
}
