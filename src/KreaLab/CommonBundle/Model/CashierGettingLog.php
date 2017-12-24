<?php

namespace KreaLab\CommonBundle\Model;

/**
 * CashierGettingLog
 */
abstract class CashierGettingLog
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
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $cashier;


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
     * @return CashierGettingLog
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
     * @return CashierGettingLog
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
     * @return CashierGettingLog
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
     * Set cashier
     *
     * @param \KreaLab\CommonBundle\Entity\User $cashier
     *
     * @return CashierGettingLog
     */
    public function setCashier(\KreaLab\CommonBundle\Entity\User $cashier = null)
    {
        $this->cashier = $cashier;

        return $this;
    }

    /**
     * Get cashier
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getCashier()
    {
        return $this->cashier;
    }
}

