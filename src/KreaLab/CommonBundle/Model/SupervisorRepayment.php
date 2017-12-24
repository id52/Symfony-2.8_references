<?php

namespace KreaLab\CommonBundle\Model;

/**
 * SupervisorRepayment
 */
abstract class SupervisorRepayment
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
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $supervisor;

    /**
     * @var \KreaLab\CommonBundle\Entity\Order
     */
    protected $order;


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
     * @return SupervisorRepayment
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
     * @return SupervisorRepayment
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
     * Set supervisor
     *
     * @param \KreaLab\CommonBundle\Entity\User $supervisor
     *
     * @return SupervisorRepayment
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
     * Set order
     *
     * @param \KreaLab\CommonBundle\Entity\Order $order
     *
     * @return SupervisorRepayment
     */
    public function setOrder(\KreaLab\CommonBundle\Entity\Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \KreaLab\CommonBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}

