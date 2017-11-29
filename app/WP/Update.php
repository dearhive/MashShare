<?php

namespace Mashshare\WP;

use Mashshare\Mashshare;

/**
 * Class Update
 * @package Mashshare\WP
 */
class Update
{

    private function updateVersion()
    {
        if (!update_option(Mashshare::OPTION_NAME, Mashshare::VERSION))
        {
            add_option(Mashshare::OPTION_NAME, Mashshare::VERSION);
        }
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
        $tableName          = $wpdb->prefix . 'mashshare_queue';

        $sql = "CREATE TABLE {$tableName} (
            id int(10) NOT NULL AUTO_INCREMENT,
            posts_id bigint(20) NOT NULL,
            prioirty tinyint(3) DEFAULT 1 NULL,
            last_update datetime DEFAULT '0000-00-00 00:00:00' NULL,
            next_update datetime DEFAULT '0000-00-00 00:00:00' NULL,
            UNIQUE KEY id (id)
        ) {$charsetCollate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

        $this->updateVersion();
    }
}