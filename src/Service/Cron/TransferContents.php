<?php

namespace Mashshare\Service\Cron;

use Mashshare\Service\Queue\QueueRepository;
use WP_Query;

/**
 * Class TransferContents
 * @package Mashshare\Service\Cron
 */
class TransferContents
{
    const CRON_OPTION_NAME      = 'mashsb_transfer_contents';
    const CRON_NAME             = 'mashsb_cron_transfer_contents';
    const CRON_TIMER_KEY        = 'transferContents';
    const PROCESS_LIMIT         = 100;

    /**
     * @var array
     */
    private $mashShareSettings;

    /**
     * @var array
     */
    private $transferSettings;

    public function __construct()
    {

        $this->defineHooks();
    }

    private function defineHooks()
    {
        add_filter('cron_schedules', array($this, 'addNewSchedule'));
        add_action('init', array($this, 'scheduleCron'));
        add_action(self::CRON_NAME, array($this, 'addContents'));
    }

    /**
     * @return array|false
     */
    private function getMashShareSettings()
    {
        if (is_array($this->mashShareSettings))
        {
            return $this->mashShareSettings;
        }

        $this->mashShareSettings = get_option('mashsb_settings');

        return $this->mashShareSettings;
    }

    /**
     * @return array
     */
    private function getTransferSettings()
    {
        if ($this->transferSettings)
        {
            return $this->transferSettings;
        }

        $transferSettings   = get_option(self::CRON_OPTION_NAME);
        $mashShareSettings  = $this->getMashShareSettings();

        if (!is_array($transferSettings))
        {
            $transferSettings = array(
                'postTypes' => $mashShareSettings['post_types'],
                'offset'    => 0,
            );
        }

        if ($transferSettings['postTypes'] !== $mashShareSettings['post_types'])
        {
            $transferSettings['offset'] = 0;
        }

        $this->transferSettings = $transferSettings;

        return $this->transferSettings;
    }

    /**
     * @param array $schedules
     *
     * @return array
     */
    public function addNewSchedule($schedules)
    {
        $schedules[self::CRON_TIMER_KEY] = array(
            'interval'  => 1800,
            'display'   => 'Mashshare - Transfer Contents',
        );

        return $schedules;
    }

    public function scheduleCron()
    {
        $settings       = $this->getMashShareSettings();
        $cronSchedule   = wp_next_scheduled(self::CRON_NAME);

        if ($cronSchedule && ShareCount::CRON_SETTINGS_VALUE !== $settings['caching_method'])
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

    public function addContents()
    {
        $transferSettings   = $this->getTransferSettings();

        $arguments = array(
            'post_type'         => array_keys($transferSettings['postTypes']),
            'orderby'           => 'ID',
            'order'             => 'ASC',
            'posts_per_page'    => self::PROCESS_LIMIT,
            'offset'            => (int) $transferSettings['offset'],
        );

        $query = new WP_Query($arguments);

        if (!$query->have_posts())
        {
            return;
        }

        $queueRepository = new QueueRepository();

        while ($query->have_posts())
        {
            $query->the_post();
            $queueRepository->addToQueue(get_the_ID());
        }

        wp_reset_postdata();
    }
}