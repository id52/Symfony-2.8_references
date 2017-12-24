<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ServiceDiscount
 */
abstract class ServiceDiscount
{
    /**
     * @var boolean
     */
    protected $active;

    /**
     * @var integer
     */
    protected $sum;

    /**
     * @var \KreaLab\CommonBundle\Entity\Service
     */
    protected $service;

    /**
     * @var \KreaLab\CommonBundle\Entity\Discount
     */
    protected $discount;


    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return ServiceDiscount
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set sum
     *
     * @param integer $sum
     *
     * @return ServiceDiscount
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
     * Set service
     *
     * @param \KreaLab\CommonBundle\Entity\Service $service
     *
     * @return ServiceDiscount
     */
    public function setService(\KreaLab\CommonBundle\Entity\Service $service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \KreaLab\CommonBundle\Entity\Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set discount
     *
     * @param \KreaLab\CommonBundle\Entity\Discount $discount
     *
     * @return ServiceDiscount
     */
    public function setDiscount(\KreaLab\CommonBundle\Entity\Discount $discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return \KreaLab\CommonBundle\Entity\Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }
}

