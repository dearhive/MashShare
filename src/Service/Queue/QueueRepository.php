<?php

namespace Mashshare\Service\Queue;

use Mashshare\Model\Queue;
use Mashshare\Model\QueueQuery;

/**
 * Class QueueRepository
 * @package Mashshare\Service\Queue
 */
class QueueRepository
{

    /**
     * @var array
     */
    private $types = array();

    public function __construct()
    {
        $settings = get_option('mashsb_settings');

        if (isset($settings['post_types']) && is_array($settings['post_types']) && !empty($settings['post_types']))
        {
            $this->setTypes(array_keys($settings['post_types']));
        }
    }

    /**
     * @param int $postId
     *
     * @return Queue|null
     */
    private function getQueue($postId)
    {
        $queueQuery = new QueueQuery();

        return $queueQuery->findByPostsId($postId);
    }

    /**
     * @param int $postId
     *
     * @return bool
     */
    private function isValidPost($postId)
    {
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId))
        {
            return false;
        }

        $post = get_post($postId);

        return ($post && in_array($post->post_type, $this->getTypes()));
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param array $types
     *
     * @return $this
     */
    public function setTypes($types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @param int $postId
     */
    public function addToQueue($postId)
    {
        if (!$this->isValidPost($postId))
        {
            return;
        }

        $queueQuery = new QueueQuery();
        $queue      = $queueQuery->findByPostsId($postId);

        if (null !== $queue)
        {
            return;
        }

        $queue = new Queue();

        $nextUpdate = new NextUpdate();

        $queue->setPostsId($postId)
            ->setPriority(Queue::PRIORITY_FIRST_TIER)
            ->setIsRequested(Queue::NOT_REQUESTED)
            ->setNextUpdateAt($nextUpdate->getDate())
            ->save()
        ;
    }

    /**
     * @param int $postId
     */
    public function deleteFromQueue($postId)
    {

        $queue = $this->getQueue($postId);

        if (null === $queue)
        {
            return;
        }

        $queue->delete();
    }
}