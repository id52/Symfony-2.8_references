<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Workplace
 */
abstract class Workplace
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
    protected $name;

    /**
     * @var integer
     */
    protected $sum;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operators;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $cashboxes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $service_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $workplace_orders;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blank_life_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $agreements;

    /**
     * @var \KreaLab\CommonBundle\Entity\Filial
     */
    protected $filial;

    /**
     * @var \KreaLab\CommonBundle\Entity\LegalEntity
     */
    protected $legal_entity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->operators = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cashboxes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->service_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->workplace_orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blank_life_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->agreements = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Workplace
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
     * Set name
     *
     * @param string $name
     *
     * @return Workplace
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
     * Set sum
     *
     * @param integer $sum
     *
     * @return Workplace
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
     * Add operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     *
     * @return Workplace
     */
    public function addOperator(\KreaLab\CommonBundle\Entity\User $operator)
    {
        $this->operators[] = $operator;

        return $this;
    }

    /**
     * Remove operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     */
    public function removeOperator(\KreaLab\CommonBundle\Entity\User $operator)
    {
        $this->operators->removeElement($operator);
    }

    /**
     * Get operators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * Add cashbox
     *
     * @param \KreaLab\CommonBundle\Entity\Cashbox $cashbox
     *
     * @return Workplace
     */
    public function addCashbox(\KreaLab\CommonBundle\Entity\Cashbox $cashbox)
    {
        $this->cashboxes[] = $cashbox;

        return $this;
    }

    /**
     * Remove cashbox
     *
     * @param \KreaLab\CommonBundle\Entity\Cashbox $cashbox
     */
    public function removeCashbox(\KreaLab\CommonBundle\Entity\Cashbox $cashbox)
    {
        $this->cashboxes->removeElement($cashbox);
    }

    /**
     * Get cashboxes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCashboxes()
    {
        return $this->cashboxes;
    }

    /**
     * Add serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     *
     * @return Workplace
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
     * Add envelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $envelope
     *
     * @return Workplace
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
     * Add workplaceOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $workplaceOrder
     *
     * @return Workplace
     */
    public function addWorkplaceOrder(\KreaLab\CommonBundle\Entity\Order $workplaceOrder)
    {
        $this->workplace_orders[] = $workplaceOrder;

        return $this;
    }

    /**
     * Remove workplaceOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $workplaceOrder
     */
    public function removeWorkplaceOrder(\KreaLab\CommonBundle\Entity\Order $workplaceOrder)
    {
        $this->workplace_orders->removeElement($workplaceOrder);
    }

    /**
     * Get workplaceOrders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkplaceOrders()
    {
        return $this->workplace_orders;
    }

    /**
     * Add blankLifeLog
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLifeLog $blankLifeLog
     *
     * @return Workplace
     */
    public function addBlankLifeLog(\KreaLab\CommonBundle\Entity\BlankLifeLog $blankLifeLog)
    {
        $this->blank_life_logs[] = $blankLifeLog;

        return $this;
    }

    /**
     * Remove blankLifeLog
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLifeLog $blankLifeLog
     */
    public function removeBlankLifeLog(\KreaLab\CommonBundle\Entity\BlankLifeLog $blankLifeLog)
    {
        $this->blank_life_logs->removeElement($blankLifeLog);
    }

    /**
     * Get blankLifeLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlankLifeLogs()
    {
        return $this->blank_life_logs;
    }

    /**
     * Add agreement
     *
     * @param \KreaLab\CommonBundle\Entity\Agreement $agreement
     *
     * @return Workplace
     */
    public function addAgreement(\KreaLab\CommonBundle\Entity\Agreement $agreement)
    {
        $this->agreements[] = $agreement;

        return $this;
    }

    /**
     * Remove agreement
     *
     * @param \KreaLab\CommonBundle\Entity\Agreement $agreement
     */
    public function removeAgreement(\KreaLab\CommonBundle\Entity\Agreement $agreement)
    {
        $this->agreements->removeElement($agreement);
    }

    /**
     * Get agreements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgreements()
    {
        return $this->agreements;
    }

    /**
     * Set filial
     *
     * @param \KreaLab\CommonBundle\Entity\Filial $filial
     *
     * @return Workplace
     */
    public function setFilial(\KreaLab\CommonBundle\Entity\Filial $filial)
    {
        $this->filial = $filial;

        return $this;
    }

    /**
     * Get filial
     *
     * @return \KreaLab\CommonBundle\Entity\Filial
     */
    public function getFilial()
    {
        return $this->filial;
    }

    /**
     * Set legalEntity
     *
     * @param \KreaLab\CommonBundle\Entity\LegalEntity $legalEntity
     *
     * @return Workplace
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
}

