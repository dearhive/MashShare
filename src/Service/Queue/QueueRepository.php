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
     */
    public function addToQueue($postId)
    {
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId))
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