<?php

namespace Mashshare\Service\Cron;

use Mashshare\Model\Queue;
use Mashshare\Model\QueueQuery;
use Mashshare\Service\Network\ShareCount as ShareCountService;
use Mashshare\WP\PostMeta\ShareCount as PostMetaShareCount;

/**
 * Class ShareCount
 * @package Mashshare\Service\Cron
 */
class ShareCount
{

    const CRON_SETTINGS_VALUE   = 'cron';
    const CRON_NAME             = 'mashsb_cron_share_counts';

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

        $settings       = get_option('mashsb_settings');
        $cronSchedule   = wp_next_scheduled(self::CRON_NAME);

        if ($cronSchedule && self::CRON_SETTINGS_VALUE !== $settings['caching_method'])
        {
            wp_unschedule_event($cronSchedule, self::CRON_NAME);
        }
        elseif (false === $cronSchedule && self::CRON_SETTINGS_VALUE !== $settings['caching_method'])
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

        $serviceShareCount  = new ShareCountService;

        $postMetaShareCount = new PostMetaShareCount();

        /** @var Queue $content */
        foreach ($queue as $content)
        {

            $shares = $serviceShareCount->setPostId($content->getPostsId())
                ->requestShares()
                ->getShares()
            ;

            $postMetaShareCount->setPostId($content->getPostsId())
                ->setShares($shares)
                ->setErrors($serviceShareCount->getErrors())
                ->update()
            ;
        }
    }
}