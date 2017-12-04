<?php

namespace Mashshare\WP;

use Mashshare\Mashshare;
use wpdb;

/**
 * Class Update
 * @package Mashshare\WP
 */
class Update
{

    const QUEUE_TABLE_NAME = 'mashshare_queue';

    private function updateVersion()
    {
        if (!update_option(Mashshare::OPTION_NAME, Mashshare::VERSION))
        {
            add_option(Mashshare::OPTION_NAME, Mashshare::VERSION);
        }
    }

    public function getQueueTableName()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        return $wpdb->prefix . self::QUEUE_TABLE_NAME;
    }

    public function update()
    {
        /** @var \wpdb $wpdb  */
        global $wpdb;

        $version = get_option(Mashshare::OPTION_NAME, '1.0');

        if ($version && version_compare($version, '4.0.0') < 1)
        {
            return;
        }

        $charsetCollate     = $wpdb->get_charset_collate();
        $tableName          = $this->getQueueTableName();

        $sql = "CREATE TABLE {$tableName} (
            id int(10) NOT NULL AUTO_INCREMENT,
            posts_id bigint(20) NOT NULL,
            priority tinyint(3) DEFAULT 1 NULL,
            is_requested tinyint(1) DEFAULT 0 NULL,
            last_update_at datetime DEFAULT '0000-00-00 00:00:00' NULL,
            next_update_at datetime DEFAULT '0000-00-00 00:00:00' NULL,
            UNIQUE KEY id (id)
        ) {$charsetCollate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

        $this->updateVersion();
    }
}