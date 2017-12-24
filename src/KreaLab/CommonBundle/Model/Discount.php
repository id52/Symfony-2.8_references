<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Discount
 */
abstract class Discount
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $is_online;

    /**
     * @var integer
     */
    protected $position;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $services_discounts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->services_discounts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Discount
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isOnline
     *
     * @param boolean $isOnline
     *
     * @return Discount
     */
    public function setIsOnline($isOnline)
    {
        $this->is_online = $isOnline;

        return $this;
    }

    /**
     * Get isOnline
     *
     * @return boolean
     */
    public function getIsOnline()
    {
        return $this->is_online;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Discount
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Add servicesDiscount
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceDiscount $servicesDiscount
     *
     * @return Discount
     */
    public function addServicesDiscount(\KreaLab\CommonBundle\Entity\ServiceDiscount $servicesDiscount)
    {
        $this->services_discounts[] = $servicesDiscount;

        return $this;
    }

    /**
     * Remove servicesDiscount
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceDiscount $servicesDiscount
     */
    public function removeServicesDiscount(\KreaLab\CommonBundle\Entity\ServiceDiscount $servicesDiscount)
    {
        $this->services_discounts->removeElement($servicesDiscount);
    }

    /**
     * Get servicesDiscounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServicesDiscounts()
    {
        return $this->services_discounts;
    }
}

