<?php

return [
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),
    
    'auth' => [
        'guard' => env('FILAMENT_AUTH_GUARD', 'web'),
        'pages' => [
            'login' => \Filament\Pages\Auth\Login::class,
        ],
    ],
    
    'pages' => [
        'namespace' => 'App\\Filament\\Pages',
        'path' => app_path('Filament/Pages'),
        'register' => [
            \App\Filament\Pages\Dashboard::class,
        ],
    ],
    
    'resources' => [
        'namespace' => 'App\\Filament\\Resources',
        'path' => app_path('Filament/Resources'),
        'register' => [],
    ],
    
    'widgets' => [
        'namespace' => 'App\\Filament\\Widgets',
        'path' => app_path('Filament/Widgets'),
        'register' => [
            \App\Filament\Widgets\DashboardStats::class,
            \App\Filament\Widgets\BarbeariaOverview::class,
            \App\Filament\Widgets\RecentAgendamentos::class,
        ],
    ],
    
    'livewire' => [
        'namespace' => 'App\\Filament',
        'path' => app_path('Filament'),
    ],
    
    'dark_mode' => true,
    
    'database_notifications' => [
        'enabled' => true,
        'polling_interval' => '30s',
    ],
    
    'broadcasting' => [
        'enabled' => false,
        'channel' => 'filament.notifications',
    ],
    
    'layout' => [
        'forms' => [
            'actions' => [
                'alignment' => 'left',
            ],
            'have_inline_labels' => false,
        ],
        'footer' => [
            'should_show_logo' => true,
        ],
    ],
    
    'plugins' => [
        //
    ],
];