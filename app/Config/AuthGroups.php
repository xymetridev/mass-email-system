<?php

namespace App\Config;

use CodeIgniter\Shield\Authorization\Groups;

class AuthGroups extends Groups
{
    /**
     * Only two roles are allowed by architecture.
     */
    public array $groups = [
        'admin' => [
            'title'       => 'Administrator',
            'description' => 'Full access to all users and resources.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'Access limited to own resources only.',
        ],
    ];

    /**
     * Permission catalog kept intentionally small.
     */
    public array $permissions = [
        'users.read.own'    => 'Read own user-scoped data',
        'users.update.own'  => 'Update own user-scoped data',
        'users.delete.own'  => 'Delete own user-scoped data',
        'users.read.all'    => 'Read any user-scoped data',
        'users.update.all'  => 'Update any user-scoped data',
        'users.delete.all'  => 'Delete any user-scoped data',
    ];

    /**
     * Permission mapping by role.
     */
    public array $matrix = [
        'admin' => [
            'users.read.all',
            'users.update.all',
            'users.delete.all',
            'users.read.own',
            'users.update.own',
            'users.delete.own',
        ],
        'user' => [
            'users.read.own',
            'users.update.own',
            'users.delete.own',
        ],
    ];
}
