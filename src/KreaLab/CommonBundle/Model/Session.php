<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Session
 */
abstract class Session
{
    /**
     * @var binary
     */
    protected $sess_id;

    /**
     * @var string
     */
    protected $sess_data;

    /**
     * @var integer
     */
    protected $sess_time;

    /**
     * @var integer
     */
    protected $sess_lifetime;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $user;


    /**
     * Set sessId
     *
     * @param binary $sessId
     *
     * @return Session
     */
    public function setSessId($sessId)
    {
        $this->sess_id = $sessId;

        return $this;
    }

    /**
     * Get sessId
     *
     * @return binary
     */
    public function getSessId()
    {
        return $this->sess_id;
    }

    /**
     * Set sessData
     *
     * @param string $sessData
     *
     * @return Session
     */
    public function setSessData($sessData)
    {
        $this->sess_data = $sessData;

        return $this;
    }

    /**
     * Get sessData
     *
     * @return string
     */
    public function getSessData()
    {
        return $this->sess_data;
    }

    /**
     * Set sessTime
     *
     * @param integer $sessTime
     *
     * @return Session
     */
    public function setSessTime($sessTime)
    {
        $this->sess_time = $sessTime;

        return $this;
    }

    /**
     * Get sessTime
     *
     * @return integer
     */
    public function getSessTime()
    {
        return $this->sess_time;
    }

    /**
     * Set sessLifetime
     *
     * @param integer $sessLifetime
     *
     * @return Session
     */
    public function setSessLifetime($sessLifetime)
    {
        $this->sess_lifetime = $sessLifetime;

        return $this;
    }

    /**
     * Get sessLifetime
     *
     * @return integer
     */
    public function getSessLifetime()
    {
        return $this->sess_lifetime;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Session
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set user
     *
     * @param \KreaLab\CommonBundle\Entity\User $user
     *
     * @return Session
     */
    public function setUser(\KreaLab\CommonBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}

