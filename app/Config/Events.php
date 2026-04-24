<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

/*
 * --------------------------------------------------------------------
 * Shield Authentication Event Logging
 * --------------------------------------------------------------------
 */
Events::on('login', static function ($user) {
    if (function_exists('record_activity')) {
        record_activity('LOGIN_SUCCESS', "User '{$user->username}' berhasil login.", ['user_id' => $user->id]);
    }
});

Events::on('logout', static function ($user) {
    if (function_exists('record_activity') && $user) {
        record_activity('LOGOUT', "User '{$user->username}' melakukan logout.", ['user_id' => $user->id]);
    }
});

Events::on('failedLogin', static function ($credentials) {
    if (function_exists('record_activity')) {
        $email = $credentials['email'] ?? 'unknown';
        // Note: auth()->id() might be null here, but record_activity handles it (it sets user_id to null).
        record_activity('LOGIN_FAILED', "Gagal login dengan email '{$email}'. Kemungkinan password salah atau akun diblokir.");
    }
});
