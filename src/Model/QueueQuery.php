<?php

namespace Mashshare\Model;

use DateTime;
use Mashshare\Model\Collection\QueueCollection;
use Mashshare\WP\Update;
use wpdb;

/**
 * Class QueueQuery
 * @package Mashshare\Model
 */
class QueueQuery
{
    /**
     * @var wpdb
     */
    private $wpDB;

    /**
     * @var string
     */
    private $tableName;

    public function __construct()
    {
        global $wpdb;

        $update = new Update;

        $this->wpDB         = $wpdb;
        $this->tableName    = $update->getQueueTableName();
    }

    /**
     * @return wpdb
     */
    public function getWpDB()
    {
        return $this->wpDB;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param int $id
     *
     * @return Queue|null
     */
    public function findById($id)
    {
        $id = (int) $id;

        $sql = 'SELECT id, posts_id, priority, is_requested, last_update_at, next_update_at 
FROM ' . $this->getTableName() . ' WHERE id=:id LIMIT 1';

        $prepare = $this->getWpDB()->prepare($sql, array('id' => $id));

        $records = $this->getWpDB()->get_row($prepare);

        if (!is_object($records))
        {
            return null;
        }

        $model = new Queue;

        return $model->hydrate($records);
    }

    /**
     * @param int $postsId
     *
     * @return Queue|null
     */
    public function findByPostsId($postsId)
    {

        $postsId = (int) $postsId;

        $sql = 'SELECT id, posts_id, priority, is_requested, last_update_at, next_update_at 
FROM ' . $this->getTableName() . ' WHERE posts_id=:posts_id LIMIT 1';

        $prepare = $this->getWpDB()->prepare($sql, array('posts_id' => $postsId));

        $records = $this->getWpDB()->get_row($prepare);

        if (!is_object($records))
        {
            return null;
        }

        $model = new Queue;

        return $model->hydrate($records);
    }

    /**
     * @return QueueCollection|null
     */
    public function getRequestQueue()
    {
        $date = new DateTime();

        $sql = 'SELECT id, posts_id, priority, is_requested, last_update_at, next_update_at 
FROM ' . $this->getTableName() . ' WHERE is_requested = 0 AND next_update_at <= :startDate LIMIT 200';

        $prepare = $this->getWpDB()->prepare(
            $sql,
            array(
                'startDate' => $date->format('Y-m-d H:00:00'),
            )
        );

        $records = $this->getWpDB()->get_results($prepare);

        if (!is_array($records))
        {
            return null;
        }

        $collection = new QueueCollection;

        return $collection->hydrate($records);
    }
}