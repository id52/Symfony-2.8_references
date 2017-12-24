<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Cashbox
 */
abstract class Cashbox
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var boolean
     */
    protected $active;

    /**
     * @var string
     */
    protected $num;

    /**
     * @var string
     */
    protected $inv_num;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $service_logs;

    /**
     * @var \KreaLab\CommonBundle\Entity\LegalEntity
     */
    protected $legal_entity;

    /**
     * @var \KreaLab\CommonBundle\Entity\Workplace
     */
    protected $workplace;

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
     * Set active
     *
     * @param boolean $active
     *
     * @return Cashbox
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
     * Set num
     *
     * @param string $num
     *
     * @return Cashbox
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return string
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set invNum
     *
     * @param string $invNum
     *
     * @return Cashbox
     */
    public function setInvNum($invNum)
    {
        $this->inv_num = $invNum;

        return $this;
    }

    /**
     * Get invNum
     *
     * @return string
     */
    public function getInvNum()
    {
        return $this->inv_num;
    }

    /**
     * Add serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     *
     * @return Cashbox
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
     * Set legalEntity
     *
     * @param \KreaLab\CommonBundle\Entity\LegalEntity $legalEntity
     *
     * @return Cashbox
     */
    public function setLegalEntity(\KreaLab\CommonBundle\Entity\LegalEntity $legalEntity)
    {
        $this->legal_entity = $legalEntity;

        return $this;
    }

    /**
     * Get legalEntity
     *
     * @return \KreaLab\CommonBundle\Entity\LegalEntity
     */
    public function getLegalEntity()
    {
        return $this->legal_entity;
    }

    /**
     * Set workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return Cashbox
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
}

