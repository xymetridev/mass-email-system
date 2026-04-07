<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\CampaignDeliveryService;
use App\Services\CronLockService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class DeliverCampaigns extends BaseCommand
{
    protected $group = 'Cron';
    protected $name = 'cron:deliver-campaigns';
    protected $description = 'Deliver RUNNING campaigns in batches to pending recipients.';

    private const LOCK_NAME = 'cron:deliver-campaigns:lock';

    public function run(array $params): void
    {
        if (! is_cli()) {
            CLI::error('This command is CLI-only.');

            return;
        }

        $lockService = new CronLockService();
        $deliveryService = new CampaignDeliveryService();

        if (! $lockService->acquire(self::LOCK_NAME, 0)) {
            CLI::write('Another delivery process is running.');

            return;
        }

        try {
            $campaigns = $deliveryService->getRunningCampaigns();

            foreach ($campaigns as $campaign) {
                $deliveryService->processCampaign(
                    $campaign,
                    static fn (string $message): int => CLI::write($message)
                );
            }
        } catch (Throwable $e) {
            CLI::error('Delivery cron failed safely: ' . $e->getMessage());
        } finally {
            $lockService->release(self::LOCK_NAME);
        }
    }
}
