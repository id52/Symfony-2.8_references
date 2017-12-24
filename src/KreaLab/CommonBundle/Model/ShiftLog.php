<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ShiftLog
 */
abstract class ShiftLog
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var \DateTime
     */
    protected $startTime;

    /**
     * @var \DateTime
     */
    protected $endTime;

    /**
     * @var boolean
     */
    protected $closed = false;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $user;

    /**
     * @var \KreaLab\CommonBundle\Entity\Filial
     */
    protected $filial;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ShiftLog
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return ShiftLog
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     *
     * @return ShiftLog
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set closed
     *
     * @param boolean $closed
     *
     * @return ShiftLog
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed
     *
     * @return boolean
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Set user
     *
     * @param \KreaLab\CommonBundle\Entity\User $user
     *
     * @return ShiftLog
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

    /**
     * Set filial
     *
     * @param \KreaLab\CommonBundle\Entity\Filial $filial
     *
     * @return ShiftLog
     */
    public function setFilial(\KreaLab\CommonBundle\Entity\Filial $filial = null)
    {
        $this->filial = $filial;

        return $this;
    }

    /**
     * Get filial
     *
     * @return \KreaLab\CommonBundle\Entity\Filial
     */
    public function getFilial()
    {
        return $this->filial;
    }
}

