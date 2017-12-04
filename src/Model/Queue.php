<?php

namespace Mashshare\Model;

use DateTime;
use Mashshare\WP\Update;
use wpdb;

/**
 * Class Queue
 * @package Mashshare\Model
 */
class Queue
{

    const PRIORITY_FIRST_TIER   = 1;

    const PRIORITY_SECOND_TIER  = 2;

    const PRIORITY_THIRD_TIER   = 3;

    const PRIORITY_FOURTH_TIER  = 4;

    const REQUESTED = true;

    const NOT_REQUESTED = false;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $postsId;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var bool
     */
    private $isRequested;

    /**
     * @var DateTime
     */
    private $lastUpdateAt;

    /**
     * @var DateTime
     */
    private $nextUpdateAt;

    /**
     * @var wpdb
     */
    private $_wpDB;

    /**
     * @var string
     */
    private $_tableName;

    /**
     * @var bool
     */
    private $_isDeleted = false;

    public function __construct()
    {
        global $wpdb;

        $update = new Update;

        $this->_wpDB         = $wpdb;
        $this->_tableName    = $update->getQueueTableName();
    }

    /**
     * @param bool $isDeleted
     *
     * @return $this
     */
    private function setIsDeleted($isDeleted)
    {
        $this->_isDeleted = (bool) $isDeleted;

        return $this;
    }

    /**
     * @return wpdb
     */
    public function getWpDB()
    {
        return $this->_wpDB;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * @param object $record
     *
     * @return $this
     */
    public function hydrate($record)
    {
        $record = (array) $record;

        foreach ($record as $property => $value)
        {
            $camelizedProperty = str_replace('_', ' ', $property);
            $camelizedProperty = ucwords($camelizedProperty);
            $camelizedProperty = str_replace(' ', null, $camelizedProperty);

            $method = 'set' . $camelizedProperty;

            if (!method_exists($this, $method))
            {
                continue;
            }

            $this->{$method}($value);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostsId()
    {
        return (int) $this->postsId;
    }

    /**
     * @param int $postsId
     *
     * @return $this
     */
    public function setPostsId($postsId)
    {
        $this->postsId = (int) $postsId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return (int) $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = (int) $priority;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequested()
    {
        return (bool) $this->isRequested;
    }

    /**
     * @param bool $isRequested
     *
     * @return $this
     */
    public function setIsRequested($isRequested)
    {
        $this->isRequested = (bool) $isRequested;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLastUpdateAt()
    {
        return $this->lastUpdateAt;
    }

    /**
     * @param DateTime|string $lastUpdateAt
     *
     * @return $this
     */
    public function setLastUpdateAt($lastUpdateAt)
    {
        if (!($lastUpdateAt instanceof DateTime))
        {
            $lastUpdateAt = new DateTime($lastUpdateAt);
        }

        $this->lastUpdateAt = $lastUpdateAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getNextUpdateAt()
    {
        return $this->nextUpdateAt;
    }

    /**
     * @param DateTime|string $nextUpdateAt
     *
     * @return $this
     */
    public function setNextUpdateAt($nextUpdateAt)
    {
        if (!($nextUpdateAt instanceof DateTime))
        {
            $nextUpdateAt = new DateTime($nextUpdateAt);
        }

        $this->nextUpdateAt = $nextUpdateAt;

        return $this;
    }

    /**
     * @return Queue
     */
    public function save()
    {
        return ($this->getId()) ? $this->update() : $this->insert();
    }

    /**
     * @return $this
     */
    public function insert()
    {
        $this->getWpDB()->insert(
            $this->getTableName(),
            array(
                'posts_id'          => $this->getPostsId(),
                'priority'          => 1,
                'is_requested'      => 0,
                'last_update_at'    => $this->getLastUpdateAt()
                                            ->format('Y-m-d H:i:s'),
                'next_update_at'    => $this->getNextUpdateAt()
                                            ->format('Y-m-d H:00:00'),
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s',
            )
        );

        $this->setId($this->getWpDB()->insert_id);

        return $this;
    }

    /**
     * @return $this
     */
    public function update()
    {
        $this->getWpDB()->update(
            $this->getTableName(),
            array(
                'posts_id'          => $this->getPostsId(),
                'priority'          => $this->getPriority(),
                'is_requested'      => (int) $this->isRequested(),
                'last_update_at'    => $this->getLastUpdateAt()
                                            ->format('Y-m-d H:i:s'),
                'next_update_at'    => $this->getNextUpdateAt()
                                            ->format('Y-m-d H:00:00'),
            ),
            array(
                'id' => $this->getId(),
            )
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {

        $result = $this->getWpDB()->delete(
            $this->getTableName(),
            array(
                'id' => $this->getId(),
            ),
            array(
                '%d'
            )
        );

        $this->setIsDeleted((false !== $result));

        return $this;
    }
}