<?php

namespace Mashshare\Model\Collection;

use Countable;
use Iterator;
use Mashshare\Model\Queue;

/**
 * Class QueueCollection
 * @package Mashshare\Model\Collection
 */
class QueueCollection implements Iterator, Countable
{

    /**
     * @var int
     */
    private $_index = 0;

    /**
     * @var Queue[]
     */
    private $_data = array();

    /**
     * @param array $data
     *
     * @return $this
     */
    public function hydrate($data)
    {
        foreach ($data as $record)
        {
            $model = new Queue;

            $this->push(
                $model->hydrate($record)
            );
        }

        return $this;
    }

    /**
     * @param Queue $data
     */
    public function add($data)
    {
        $this->_data[] = $data;
    }

    /**
     * @param Queue $data
     */
    public function prepend($data)
    {
        array_unshift($this->_data, $data);
    }

    /**
     * @param Queue $data
     */
    public function push($data)
    {
        array_push($this->_data, $data);
    }

    /**
     * @param int $index
     *
     * @return Queue|null
     */
    public function get($index)
    {
        if (!isset($this->_data[$index]))
        {
            return null;
        }

        return $this->_data[$index];
    }

    /**
     * Save collection
     */
    public function save()
    {
        /** @var Queue $queue */
        foreach ($this->_data as $queue)
        {
            $queue->save();
        }
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->_data[$this->_index];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->_index;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->_index;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->_data[$this->_index]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->_index = 0;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->_data);
    }
}