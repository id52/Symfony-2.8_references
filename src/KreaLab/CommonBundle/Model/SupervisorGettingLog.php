<?php

namespace KreaLab\CommonBundle\Model;

/**
 * SupervisorGettingLog
 */
abstract class SupervisorGettingLog
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $sum;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $envelopes;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $courier;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $supervisor;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->envelopes = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set sum
     *
     * @param integer $sum
     *
     * @return SupervisorGettingLog
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Get sum
     *
     * @return integer
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return SupervisorGettingLog
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Add envelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $envelope
     *
     * @return SupervisorGettingLog
     */
    public function addEnvelope(\KreaLab\CommonBundle\Entity\Envelope $envelope)
    {
        $this->envelopes[] = $envelope;

        return $this;
    }

    /**
     * Remove envelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $envelope
     */
    public function removeEnvelope(\KreaLab\CommonBundle\Entity\Envelope $envelope)
    {
        $this->envelopes->removeElement($envelope);
    }

    /**
     * Get envelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnvelopes()
    {
        return $this->envelopes;
    }

    /**
     * Set courier
     *
     * @param \KreaLab\CommonBundle\Entity\User $courier
     *
     * @return SupervisorGettingLog
     */
    public function setCourier(\KreaLab\CommonBundle\Entity\User $courier = null)
    {
        $this->courier = $courier;

        return $this;
    }

    /**
     * Get courier
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getCourier()
    {
        return $this->courier;
    }

    /**
     * Set supervisor
     *
     * @param \KreaLab\CommonBundle\Entity\User $supervisor
     *
     * @return SupervisorGettingLog
     */
    public function setSupervisor(\KreaLab\CommonBundle\Entity\User $supervisor = null)
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * Get supervisor
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }
}

