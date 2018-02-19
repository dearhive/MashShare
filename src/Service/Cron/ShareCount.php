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
    const CRON_TIMER_KEY        = 'shareCount';

    public function __construct()
    {
        $this->defineHooks();
    }

    private function defineHooks()
    {
        add_filter('cron_schedules', array($this, 'addNewSchedule'));
        add_action('init', array($this, 'scheduleCron'));
        add_action(self::CRON_NAME, array($this, 'updateShareCounts'));
    }

    /**
     * @param array $schedules
     *
     * @return array
     */
    public function addNewSchedule($schedules)
    {
        $schedules[self::CRON_TIMER_KEY] = array(
            'interval'  => 360,
            'display'   => 'Mashshare - Share Count',
        );

        return $schedules;
    }

    public function scheduleCron()
    {

        $settings       = get_option('mashsb_settings');
        $cronSchedule   = wp_next_scheduled(self::CRON_NAME);

        if ($cronSchedule && self::CRON_SETTINGS_VALUE !== $settings['caching_method'])
        {
            wp_unschedule_event($cronSchedule, self::CRON_NAME);
        }
        elseif (
             (false === $cronSchedule && ShareCount::CRON_SETTINGS_VALUE !== $settings['caching_method']) ||
             ($cronSchedule && ShareCount::CRON_SETTINGS_VALUE === $settings['caching_method'])
        )
        {
            return;
        }

        wp_schedule_event(current_time('timestamp'), self::CRON_TIMER_KEY, self::CRON_NAME);
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
            
             $now    = new \DateTime();
             $minute = $content->getLastUpdateAt()->format('i');
 
             $increment = 1;
 
             if ($minute > 1)
             {
                 $increment = 2;
             }
              
             $content->setLastUpdateAt($now)
                     ->setNextUpdateAt($content->getLastUpdateAt()->modify('+' . $increment .  ' hour'))
                     ->save()
             ;
        }
    }
}