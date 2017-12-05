<?php

namespace Mashshare\Service\Cron;

use Mashshare\Model\Queue;
use Mashshare\Model\QueueQuery;
use Mashshare\Service\Network\ShareCount as ShareCountService;

/**
 * Class ShareCount
 * @package Mashshare\Service\Cron
 */
class ShareCount
{

    const CRON_NAME = 'mashsb_cron_share_counts';

    public function __construct()
    {
        $this->defineHooks();
    }

    private function defineHooks()
    {
        add_action('init', array($this, 'scheduleCron'));
        add_action(self::CRON_NAME, array($this, 'updateShareCounts'));
    }

    public function scheduleCron()
    {
        $cronSchedule = wp_next_scheduled(self::CRON_NAME);

        if (false !== $cronSchedule)
        {
            return;
        }

        wp_schedule_event(current_time('timestamp'), '6min', self::CRON_NAME);
    }

    public function updateShareCounts()
    {
        $queueQuery = new QueueQuery;
        $queue      = $queueQuery->getRequestQueue();

        // Nothing to update
        if (null === $queue)
        {
            return;
        }

        $serviceShareCount = new ShareCountService;

        /** @var Queue $content */
        foreach ($queue as $content)
        {

            $shares = $serviceShareCount->setPostId($content->getPostsId())
                ->requestShares()
                ->getShares()
            ;


        }
    }
}