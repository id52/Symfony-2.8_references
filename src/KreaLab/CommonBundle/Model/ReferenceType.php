<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ReferenceType
 */
abstract class ReferenceType
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
    protected $driver_reference;

    /**
     * @var boolean
     */
    protected $is_serie;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blanks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $services;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $stockman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blank_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_archive_boxes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->blanks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stockman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blank_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_archive_boxes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ReferenceType
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
     * Set driverReference
     *
     * @param boolean $driverReference
     *
     * @return ReferenceType
     */
    public function setDriverReference($driverReference)
    {
        $this->driver_reference = $driverReference;

        return $this;
    }

    /**
     * Get driverReference
     *
     * @return boolean
     */
    public function getDriverReference()
    {
        return $this->driver_reference;
    }

    /**
     * Set isSerie
     *
     * @param boolean $isSerie
     *
     * @return ReferenceType
     */
    public function setIsSerie($isSerie)
    {
        $this->is_serie = $isSerie;

        return $this;
    }

    /**
     * Get isSerie
     *
     * @return boolean
     */
    public function getIsSerie()
    {
        return $this->is_serie;
    }

    /**
     * Add blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     *
     * @return ReferenceType
     */
    public function addBlank(\KreaLab\CommonBundle\Entity\Blank $blank)
    {
        $this->blanks[] = $blank;

        return $this;
    }

    /**
     * Remove blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     */
    public function removeBlank(\KreaLab\CommonBundle\Entity\Blank $blank)
    {
        $this->blanks->removeElement($blank);
    }

    /**
     * Get blanks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlanks()
    {
        return $this->blanks;
    }

    /**
     * Add service
     *
     * @param \KreaLab\CommonBundle\Entity\Service $service
     *
     * @return ReferenceType
     */
    public function addService(\KreaLab\CommonBundle\Entity\Service $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * Remove service
     *
     * @param \KreaLab\CommonBundle\Entity\Service $service
     */
    public function removeService(\KreaLab\CommonBundle\Entity\Service $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Add referencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelope
     *
     * @return ReferenceType
     */
    public function addReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelope)
    {
        $this->referenceman_envelopes[] = $referencemanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelope
     */
    public function removeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelope)
    {
        $this->referenceman_envelopes->removeElement($referencemanEnvelope);
    }

    /**
     * Get referencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanEnvelopes()
    {
        return $this->referenceman_envelopes;
    }

    /**
     * Add operatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelope
     *
     * @return ReferenceType
     */
    public function addOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelope)
    {
        $this->operator_envelopes[] = $operatorEnvelope;

        return $this;
    }

    /**
     * Remove operatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelope
     */
    public function removeOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelope)
    {
        $this->operator_envelopes->removeElement($operatorEnvelope);
    }

    /**
     * Get operatorEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorEnvelopes()
    {
        return $this->operator_envelopes;
    }

    /**
     * Add operatorReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope
     *
     * @return ReferenceType
     */
    public function addOperatorReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope)
    {
        $this->operator_referenceman_envelopes[] = $operatorReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove operatorReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope
     */
    public function removeOperatorReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope)
    {
        $this->operator_referenceman_envelopes->removeElement($operatorReferencemanEnvelope);
    }

    /**
     * Get operatorReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorReferencemanEnvelopes()
    {
        return $this->operator_referenceman_envelopes;
    }

    /**
     * Add referencemanReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope
     *
     * @return ReferenceType
     */
    public function addReferencemanReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelopes[] = $referencemanReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope
     */
    public function removeReferencemanReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelopes->removeElement($referencemanReferencemanEnvelope);
    }

    /**
     * Get referencemanReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanReferencemanEnvelopes()
    {
        return $this->referenceman_referenceman_envelopes;
    }

    /**
     * Add stockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelope
     *
     * @return ReferenceType
     */
    public function addStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelope)
    {
        $this->stockman_envelopes[] = $stockmanEnvelope;

        return $this;
    }

    /**
     * Remove stockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelope
     */
    public function removeStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelope)
    {
        $this->stockman_envelopes->removeElement($stockmanEnvelope);
    }

    /**
     * Get stockmanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStockmanEnvelopes()
    {
        return $this->stockman_envelopes;
    }

    /**
     * Add blankLog
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLog $blankLog
     *
     * @return ReferenceType
     */
    public function addBlankLog(\KreaLab\CommonBundle\Entity\BlankLog $blankLog)
    {
        $this->blank_logs[] = $blankLog;

        return $this;
    }

    /**
     * Remove blankLog
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLog $blankLog
     */
    public function removeBlankLog(\KreaLab\CommonBundle\Entity\BlankLog $blankLog)
    {
        $this->blank_logs->removeElement($blankLog);
    }

    /**
     * Get blankLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlankLogs()
    {
        return $this->blank_logs;
    }

    /**
     * Add referencemanArchiveBox
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox
     *
     * @return ReferenceType
     */
    public function addReferencemanArchiveBox(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox)
    {
        $this->referenceman_archive_boxes[] = $referencemanArchiveBox;

        return $this;
    }

    /**
     * Remove referencemanArchiveBox
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox
     */
    public function removeReferencemanArchiveBox(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox)
    {
        $this->referenceman_archive_boxes->removeElement($referencemanArchiveBox);
    }

    /**
     * Get referencemanArchiveBoxes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanArchiveBoxes()
    {
        return $this->referenceman_archive_boxes;
    }
}

