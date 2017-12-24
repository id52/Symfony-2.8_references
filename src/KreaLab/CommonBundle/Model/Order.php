<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Order
 */
abstract class Order
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $appointment;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var integer
     */
    protected $pin;

    /**
     * @var integer
     */
    protected $sum;

    /**
     * @var integer
     */
    protected $residual;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \DateTime
     */
    protected $updated_at;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $supervisor_repayments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $consumables;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $children;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $acquittanceman;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $operator;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $treasurer;

    /**
     * @var \KreaLab\CommonBundle\Entity\Workplace
     */
    protected $workplace;

    /**
     * @var \KreaLab\CommonBundle\Entity\Envelope
     */
    protected $envelope;

    /**
     * @var \KreaLab\CommonBundle\Model\OrderType
     */
    protected $order_type;

    /**
     * @var \KreaLab\CommonBundle\Model\Order
     */
    protected $parent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->supervisor_repayments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->consumables = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set appointment
     *
     * @param string $appointment
     *
     * @return Order
     */
    public function setAppointment($appointment)
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * Get appointment
     *
     * @return string
     */
    public function getAppointment()
    {
        return $this->appointment;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Order
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set pin
     *
     * @param integer $pin
     *
     * @return Order
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get pin
     *
     * @return integer
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set sum
     *
     * @param integer $sum
     *
     * @return Order
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
     * Set residual
     *
     * @param integer $residual
     *
     * @return Order
     */
    public function setResidual($residual)
    {
        $this->residual = $residual;

        return $this;
    }

    /**
     * Get residual
     *
     * @return integer
     */
    public function getResidual()
    {
        return $this->residual;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Order
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Order
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Order
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add supervisorRepayment
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment
     *
     * @return Order
     */
    public function addSupervisorRepayment(\KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment)
    {
        $this->supervisor_repayments[] = $supervisorRepayment;

        return $this;
    }

    /**
     * Remove supervisorRepayment
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment
     */
    public function removeSupervisorRepayment(\KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment)
    {
        $this->supervisor_repayments->removeElement($supervisorRepayment);
    }

    /**
     * Get supervisorRepayments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupervisorRepayments()
    {
        return $this->supervisor_repayments;
    }

    /**
     * Add consumable
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumable
     *
     * @return Order
     */
    public function addConsumable(\KreaLab\CommonBundle\Entity\Consumable $consumable)
    {
        $this->consumables[] = $consumable;

        return $this;
    }

    /**
     * Remove consumable
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumable
     */
    public function removeConsumable(\KreaLab\CommonBundle\Entity\Consumable $consumable)
    {
        $this->consumables->removeElement($consumable);
    }

    /**
     * Get consumables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumables()
    {
        return $this->consumables;
    }

    /**
     * Add child
     *
     * @param \KreaLab\CommonBundle\Model\Order $child
     *
     * @return Order
     */
    public function addChild(\KreaLab\CommonBundle\Model\Order $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \KreaLab\CommonBundle\Model\Order $child
     */
    public function removeChild(\KreaLab\CommonBundle\Model\Order $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set acquittanceman
     *
     * @param \KreaLab\CommonBundle\Entity\User $acquittanceman
     *
     * @return Order
     */
    public function setAcquittanceman(\KreaLab\CommonBundle\Entity\User $acquittanceman = null)
    {
        $this->acquittanceman = $acquittanceman;

        return $this;
    }

    /**
     * Get acquittanceman
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getAcquittanceman()
    {
        return $this->acquittanceman;
    }

    /**
     * Set operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     *
     * @return Order
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
     * Set treasurer
     *
     * @param \KreaLab\CommonBundle\Entity\User $treasurer
     *
     * @return Order
     */
    public function setTreasurer(\KreaLab\CommonBundle\Entity\User $treasurer = null)
    {
        $this->treasurer = $treasurer;

        return $this;
    }

    /**
     * Get treasurer
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getTreasurer()
    {
        return $this->treasurer;
    }

    /**
     * Set workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return Order
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
     * Set envelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $envelope
     *
     * @return Order
     */
    public function setEnvelope(\KreaLab\CommonBundle\Entity\Envelope $envelope = null)
    {
        $this->envelope = $envelope;

        return $this;
    }

    /**
     * Get envelope
     *
     * @return \KreaLab\CommonBundle\Entity\Envelope
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * Set orderType
     *
     * @param \KreaLab\CommonBundle\Model\OrderType $orderType
     *
     * @return Order
     */
    public function setOrderType(\KreaLab\CommonBundle\Model\OrderType $orderType = null)
    {
        $this->order_type = $orderType;

        return $this;
    }

    /**
     * Get orderType
     *
     * @return \KreaLab\CommonBundle\Model\OrderType
     */
    public function getOrderType()
    {
        return $this->order_type;
    }

    /**
     * Set parent
     *
     * @param \KreaLab\CommonBundle\Model\Order $parent
     *
     * @return Order
     */
    public function setParent(\KreaLab\CommonBundle\Model\Order $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \KreaLab\CommonBundle\Model\Order
     */
    public function getParent()
    {
        return $this->parent;
    }
}

