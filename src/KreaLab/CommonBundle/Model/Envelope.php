<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Envelope
 */
abstract class Envelope
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
    protected $courier_datetime;

    /**
     * @var \DateTime
     */
    protected $supervisor_datetime;

    /**
     * @var \DateTime
     */
    protected $supervisor_accepted_at;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $service_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $orders;

    /**
     * @var \KreaLab\CommonBundle\Entity\Workplace
     */
    protected $workplace;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $operator;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $courier;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $supervisor;

    /**
     * @var \KreaLab\CommonBundle\Entity\SupervisorGettingLog
     */
    protected $sgl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->service_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Envelope
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
     * Set courierDatetime
     *
     * @param \DateTime $courierDatetime
     *
     * @return Envelope
     */
    public function setCourierDatetime($courierDatetime)
    {
        $this->courier_datetime = $courierDatetime;

        return $this;
    }

    /**
     * Get courierDatetime
     *
     * @return \DateTime
     */
    public function getCourierDatetime()
    {
        return $this->courier_datetime;
    }

    /**
     * Set supervisorDatetime
     *
     * @param \DateTime $supervisorDatetime
     *
     * @return Envelope
     */
    public function setSupervisorDatetime($supervisorDatetime)
    {
        $this->supervisor_datetime = $supervisorDatetime;

        return $this;
    }

    /**
     * Get supervisorDatetime
     *
     * @return \DateTime
     */
    public function getSupervisorDatetime()
    {
        return $this->supervisor_datetime;
    }

    /**
     * Set supervisorAcceptedAt
     *
     * @param \DateTime $supervisorAcceptedAt
     *
     * @return Envelope
     */
    public function setSupervisorAcceptedAt($supervisorAcceptedAt)
    {
        $this->supervisor_accepted_at = $supervisorAcceptedAt;

        return $this;
    }

    /**
     * Get supervisorAcceptedAt
     *
     * @return \DateTime
     */
    public function getSupervisorAcceptedAt()
    {
        return $this->supervisor_accepted_at;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Envelope
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
     * Add serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     *
     * @return Envelope
     */
    public function addServiceLog(\KreaLab\CommonBundle\Entity\ServiceLog $serviceLog)
    {
        $this->service_logs[] = $serviceLog;

        return $this;
    }

    /**
     * Remove serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     */
    public function removeServiceLog(\KreaLab\CommonBundle\Entity\ServiceLog $serviceLog)
    {
        $this->service_logs->removeElement($serviceLog);
    }

    /**
     * Get serviceLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServiceLogs()
    {
        return $this->service_logs;
    }

    /**
     * Add order
     *
     * @param \KreaLab\CommonBundle\Entity\Order $order
     *
     * @return Envelope
     */
    public function addOrder(\KreaLab\CommonBundle\Entity\Order $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param \KreaLab\CommonBundle\Entity\Order $order
     */
    public function removeOrder(\KreaLab\CommonBundle\Entity\Order $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Set workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return Envelope
     */
    public function setWorkplace(\KreaLab\CommonBundle\Entity\Workplace $workplace = null)
    {
        $this->workplace = $workplace;

        return $this;
    }

    /**
     * Get workplace
     *
     * @return \KreaLab\CommonBundle\Entity\Workplace
     */
    public function getWorkplace()
    {
        return $this->workplace;
    }

    /**
     * Set operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     *
     * @return Envelope
     */
    public function setOperator(\KreaLab\CommonBundle\Entity\User $operator = null)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set courier
     *
     * @param \KreaLab\CommonBundle\Entity\User $courier
     *
     * @return Envelope
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
     * @return Envelope
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

    /**
     * Set sgl
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorGettingLog $sgl
     *
     * @return Envelope
     */
    public function setSgl(\KreaLab\CommonBundle\Entity\SupervisorGettingLog $sgl = null)
    {
        $this->sgl = $sgl;

        return $this;
    }

    /**
     * Get sgl
     *
     * @return \KreaLab\CommonBundle\Entity\SupervisorGettingLog
     */
    public function getSgl()
    {
        return $this->sgl;
    }
}

