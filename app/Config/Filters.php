<?php

namespace App\Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,

        // Shield filters
        'session'       => \CodeIgniter\Shield\Filters\SessionAuth::class,
        'group'         => \CodeIgniter\Shield\Filters\GroupFilter::class,
        'permission'    => \CodeIgniter\Shield\Filters\PermissionFilter::class,

        // App filters
        'ownership'     => \App\Filters\OwnershipFilter::class,
    ];

    public array $globals = [
        'before' => [
            'csrf',
            'secureheaders',
        ],
        'after' => [
            'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}
