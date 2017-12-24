<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Service
 */
abstract class Service
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
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $_desc;

    /**
     * @var integer
     */
    protected $price;

    /**
     * @var boolean
     */
    protected $is_not_duplicate_price;

    /**
     * @var boolean
     */
    protected $is_not_revisit_price;

    /**
     * @var integer
     */
    protected $revisit_price = 0;

    /**
     * @var integer
     */
    protected $duplicate_price = 0;

    /**
     * @var integer
     */
    protected $lifetime = 1;

    /**
     * @var boolean
     */
    protected $is_eeg_conclusion;

    /**
     * @var boolean
     */
    protected $is_gnoch;

    /**
     * @var integer
     */
    protected $position;

    /**
     * @var array
     */
    protected $subjects;

    /**
     * @var array
     */
    protected $medical_center_errors;

    /**
     * @var array
     */
    protected $duplicates;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $services_discounts;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $agreements;

    /**
     * @var \KreaLab\CommonBundle\Entity\ReferenceType
     */
    protected $reference_type;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->services_discounts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Service
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
     * @return Service
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
     * Set code
     *
     * @param string $code
     *
     * @return Service
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set desc
     *
     * @param string $desc
     *
     * @return Service
     */
    public function setDesc($desc)
    {
        $this->_desc = $desc;

        return $this;
    }

    /**
     * Get desc
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->_desc;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Service
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set isNotDuplicatePrice
     *
     * @param boolean $isNotDuplicatePrice
     *
     * @return Service
     */
    public function setIsNotDuplicatePrice($isNotDuplicatePrice)
    {
        $this->is_not_duplicate_price = $isNotDuplicatePrice;

        return $this;
    }

    /**
     * Get isNotDuplicatePrice
     *
     * @return boolean
     */
    public function getIsNotDuplicatePrice()
    {
        return $this->is_not_duplicate_price;
    }

    /**
     * Set isNotRevisitPrice
     *
     * @param boolean $isNotRevisitPrice
     *
     * @return Service
     */
    public function setIsNotRevisitPrice($isNotRevisitPrice)
    {
        $this->is_not_revisit_price = $isNotRevisitPrice;

        return $this;
    }

    /**
     * Get isNotRevisitPrice
     *
     * @return boolean
     */
    public function getIsNotRevisitPrice()
    {
        return $this->is_not_revisit_price;
    }

    /**
     * Set revisitPrice
     *
     * @param integer $revisitPrice
     *
     * @return Service
     */
    public function setRevisitPrice($revisitPrice)
    {
        $this->revisit_price = $revisitPrice;

        return $this;
    }

    /**
     * Get revisitPrice
     *
     * @return integer
     */
    public function getRevisitPrice()
    {
        return $this->revisit_price;
    }

    /**
     * Set duplicatePrice
     *
     * @param integer $duplicatePrice
     *
     * @return Service
     */
    public function setDuplicatePrice($duplicatePrice)
    {
        $this->duplicate_price = $duplicatePrice;

        return $this;
    }

    /**
     * Get duplicatePrice
     *
     * @return integer
     */
    public function getDuplicatePrice()
    {
        return $this->duplicate_price;
    }

    /**
     * Set lifetime
     *
     * @param integer $lifetime
     *
     * @return Service
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Get lifetime
     *
     * @return integer
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set isEegConclusion
     *
     * @param boolean $isEegConclusion
     *
     * @return Service
     */
    public function setIsEegConclusion($isEegConclusion)
    {
        $this->is_eeg_conclusion = $isEegConclusion;

        return $this;
    }

    /**
     * Get isEegConclusion
     *
     * @return boolean
     */
    public function getIsEegConclusion()
    {
        return $this->is_eeg_conclusion;
    }

    /**
     * Set isGnoch
     *
     * @param boolean $isGnoch
     *
     * @return Service
     */
    public function setIsGnoch($isGnoch)
    {
        $this->is_gnoch = $isGnoch;

        return $this;
    }

    /**
     * Get isGnoch
     *
     * @return boolean
     */
    public function getIsGnoch()
    {
        return $this->is_gnoch;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Service
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
     * Set subjects
     *
     * @param array $subjects
     *
     * @return Service
     */
    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;

        return $this;
    }

    /**
     * Get subjects
     *
     * @return array
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * Set medicalCenterErrors
     *
     * @param array $medicalCenterErrors
     *
     * @return Service
     */
    public function setMedicalCenterErrors($medicalCenterErrors)
    {
        $this->medical_center_errors = $medicalCenterErrors;

        return $this;
    }

    /**
     * Get medicalCenterErrors
     *
     * @return array
     */
    public function getMedicalCenterErrors()
    {
        return $this->medical_center_errors;
    }

    /**
     * Set duplicates
     *
     * @param array $duplicates
     *
     * @return Service
     */
    public function setDuplicates($duplicates)
    {
        $this->duplicates = $duplicates;

        return $this;
    }

    /**
     * Get duplicates
     *
     * @return array
     */
    public function getDuplicates()
    {
        return $this->duplicates;
    }

    /**
     * Add log
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $log
     *
     * @return Service
     */
    public function addLog(\KreaLab\CommonBundle\Model\ServiceLog $log)
    {
        $this->logs[] = $log;

        return $this;
    }

    /**
     * Remove log
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $log
     */
    public function removeLog(\KreaLab\CommonBundle\Model\ServiceLog $log)
    {
        $this->logs->removeElement($log);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add servicesDiscount
     *
     * @param \KreaLab\CommonBundle\Model\ServiceDiscount $servicesDiscount
     *
     * @return Service
     */
    public function addServicesDiscount(\KreaLab\CommonBundle\Model\ServiceDiscount $servicesDiscount)
    {
        $this->services_discounts[] = $servicesDiscount;

        return $this;
    }

    /**
     * Remove servicesDiscount
     *
     * @param \KreaLab\CommonBundle\Model\ServiceDiscount $servicesDiscount
     */
    public function removeServicesDiscount(\KreaLab\CommonBundle\Model\ServiceDiscount $servicesDiscount)
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

    /**
     * Add agreement
     *
     * @param \KreaLab\CommonBundle\Entity\Agreement $agreement
     *
     * @return Service
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
     * Set referenceType
     *
     * @param \KreaLab\CommonBundle\Entity\ReferenceType $referenceType
     *
     * @return Service
     */
    public function setReferenceType(\KreaLab\CommonBundle\Entity\ReferenceType $referenceType = null)
    {
        $this->reference_type = $referenceType;

        return $this;
    }

    /**
     * Get referenceType
     *
     * @return \KreaLab\CommonBundle\Entity\ReferenceType
     */
    public function getReferenceType()
    {
        return $this->reference_type;
    }
}

