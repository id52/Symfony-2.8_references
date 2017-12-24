<?php

namespace KreaLab\CommonBundle\Model;

/**
 * OrdermanConsumableBox
 */
abstract class OrdermanConsumableBox
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \DateTime
     */
    protected $closed_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $consumables;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $orderman;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $acquittanceman;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->consumables = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return OrdermanConsumableBox
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
     * Set closedAt
     *
     * @param \DateTime $closedAt
     *
     * @return OrdermanConsumableBox
     */
    public function setClosedAt($closedAt)
    {
        $this->closed_at = $closedAt;

        return $this;
    }

    /**
     * Get closedAt
     *
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closed_at;
    }

    /**
     * Add consumable
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumable
     *
     * @return OrdermanConsumableBox
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
     * Set orderman
     *
     * @param \KreaLab\CommonBundle\Entity\User $orderman
     *
     * @return OrdermanConsumableBox
     */
    public function setOrderman(\KreaLab\CommonBundle\Entity\User $orderman = null)
    {
        $this->orderman = $orderman;

        return $this;
    }

    /**
     * Get orderman
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getOrderman()
    {
        return $this->orderman;
    }

    /**
     * Set acquittanceman
     *
     * @param \KreaLab\CommonBundle\Entity\User $acquittanceman
     *
     * @return OrdermanConsumableBox
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
}

