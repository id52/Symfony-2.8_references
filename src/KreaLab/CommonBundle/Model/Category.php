<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Category
 */
abstract class Category
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
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $service_logs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->service_logs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Category
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
     * Add serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     *
     * @return Category
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
}

