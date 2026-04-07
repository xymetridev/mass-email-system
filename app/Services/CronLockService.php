<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;
use Throwable;

class CronLockService
{
    private ConnectionInterface $db;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Acquire a process lock using MySQL/MariaDB GET_LOCK.
     */
    public function acquire(string $name, int $timeoutSeconds = 0): bool
    {
        try {
            $row = $this->db->query(
                'SELECT GET_LOCK(?, ?) AS lock_status',
                [$name, $timeoutSeconds]
            )->getRowArray();

            return isset($row['lock_status']) && (int) $row['lock_status'] === 1;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Always attempt to release the lock.
     */
    public function release(string $name): void
    {
        try {
            $this->db->query('DO RELEASE_LOCK(?)', [$name]);
        } catch (Throwable) {
            // Best effort: swallow lock release errors.
        }
    }
}
