<?php

namespace Mashshare\Service\Queue;

use DateTime;
use WP_Post;
use wpdb;

/**
 * Class NextUpdate
 * @package Mashshare\Service\Queue
 */
class NextUpdate
{

    /**
     * @var WP_Post
     */
    private $post;

    /**
     * @param int $hoursToAdd
     *
     * @return DateTime
     */
    private function getNextFullHour($hoursToAdd = 1)
    {
        $date       = new DateTime('now');
        $minute     = $date->format('');
        $hoursToAdd = ($hoursToAdd >= 1)?: 1;

        if ($minute > 0)
        {
            $hoursToAdd++;

            $date->modify('+ ' . $hoursToAdd . ' hour');
        }
        else
        {
            $date->modify('+ ' . $hoursToAdd . ' hour');
        }

        return $date;
    }

    /**
     * @param DateTime $nextUpdateDate
     *
     * @return int
     */
    private function totalQueue($nextUpdateDate)
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $sql = 'SELECT count(1) as `total`  FROM `'. $wpdb->prefix . 'mashshare_queue` 
        WHERE is_requested = 0 AND next_update_at <= %s';

        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                $sql,
                $nextUpdateDate->format('Y-m-d H:00:00')
            )
        );

        return $total;
    }

    /**
     * @param int $hoursToAdd
     *
     * @return DateTime
     */
    public function getDate($hoursToAdd = 1)
    {
        $nextUpdateDate = $this->getNextFullHour($hoursToAdd);

        $totalInQueue   = $this->totalQueue($nextUpdateDate);

        if ($totalInQueue >= 200)
        {
            return $this->getDate($hoursToAdd + 1);
        }

        return $nextUpdateDate;
    }
}