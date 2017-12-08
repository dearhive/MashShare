<?php

namespace Mashshare\WP\PostMeta;

class ShareCount
{

    const POST_META_KEY = 'mashsb_jsonshares';

    /**
     * @var int
     */
    private $postId;

    /**
     * @var array
     */
    private $shares;

    /**
     * @var array
     */
    private $errors;

    /**
     * @return array
     */
    private function getData()
    {

        $mashshareData      = get_post_meta($this->getPostId(), self::POST_META_KEY);

        $newData            = $this->getShares();
        $newData            = array_change_key_case($newData);
        $newData['error']   = http_build_query($this->getErrors());

        if (empty($mashshareData))
        {
            return $newData;
        }

        foreach ($mashshareData as $key => $oldShareCount)
        {
            if ('error' === $key || !isset($newData[$key]))
            {
                continue;
            }

            $oldShareCount = (int) $oldShareCount;

            if ($newData[$key] < $oldShareCount)
            {
                $newData[$key] = $oldShareCount;
            }
        }

        return $newData;
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @param int $postId
     *
     * @return $this
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * @return array
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * @param array $shares
     *
     * @return $this
     */
    public function setShares($shares)
    {
        $this->shares = $shares;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     *
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return bool
     */
    public function update()
    {
        $data = $this->getData();

        $result = update_post_meta($this->getPostId(), self::POST_META_KEY, $data);

        return (false !== $result);
    }
}