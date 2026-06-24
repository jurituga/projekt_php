<?php

namespace App\Providers;

use App\Support\NativeViewCache;
use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        NativeViewCache::refreshIfStale();

        Window::open()
            ->title('Vacanto')
            ->width(1280)
            ->height(800)
            ->minWidth(900)
            ->minHeight(600);
    }

    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'max_execution_time' => '0',
            'upload_max_filesize' => '10M',
            'post_max_size' => '12M',
        ];
    }
}
