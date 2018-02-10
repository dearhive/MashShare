<?php

namespace Mashshare;

use Mashshare\Service\Cron\ShareCount;
use Mashshare\Service\Cron\TransferContents;
use Mashshare\Service\Queue\QueueRepository;

/**
 * Class Mashshare
 * @package Mashshare
 */
final class Mashshare
{

    const VERSION = '4.0.0';

    const OPTION_NAME = 'mashsb_version';

    public function __construct()
    {
        $this->defineHooks();
    }

    public function defineHooks()
    {
        $queueRepository = new QueueRepository();

        add_action('save_post', array($queueRepository, 'saveQueue'));
        add_action('deleteFromQueue', array($queueRepository, 'deleteFromQueue'), 10);

        // CronJobs - Share Count
        new ShareCount();

        // CronJobs - Add Content
        new TransferContents();
    }
}