<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Blank
 */
abstract class Blank
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
    protected $updated_at;

    /**
     * @var string
     */
    protected $serie;

    /**
     * @var integer
     */
    protected $number;

    /**
     * @var integer
     */
    protected $leading_zeros = 0;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \DateTime
     */
    protected $stockman_applied;

    /**
     * @var \DateTime
     */
    protected $referenceman_applied;

    /**
     * @var \DateTime
     */
    protected $operator_applied;

    /**
     * @var \DateTime
     */
    protected $service_log_applied;

    /**
     * @var integer
     */
    protected $penalty_sum = 0;

    /**
     * @var \DateTime
     */
    protected $penalty_date;

    /**
     * @var boolean
     */
    protected $stamp = false;

    /**
     * @var string
     */
    protected $desc_cancelled;

    /**
     * @var \KreaLab\CommonBundle\Entity\ServiceLog
     */
    protected $service_log;

    /**
     * @var \KreaLab\CommonBundle\Model\Blank
     */
    protected $replaced_by_blank_with_stamp;

    /**
     * @var \KreaLab\CommonBundle\Model\Blank
     */
    protected $replaced_blank_with_no_stamp;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $life_logs;

    /**
     * @var \KreaLab\CommonBundle\Entity\LegalEntity
     */
    protected $legal_entity;

    /**
     * @var \KreaLab\CommonBundle\Entity\ReferenceType
     */
    protected $reference_type;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $stockman;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $operator;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $referenceman;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $old_referenceman;

    /**
     * @var \KreaLab\CommonBundle\Model\BlankOperatorEnvelope
     */
    protected $operator_envelope;

    /**
     * @var \KreaLab\CommonBundle\Model\BlankOperatorReferencemanEnvelope
     */
    protected $operator_referenceman_envelope;

    /**
     * @var \KreaLab\CommonBundle\Model\BlankReferencemanReferencemanEnvelope
     */
    protected $referenceman_referenceman_envelope;

    /**
     * @var \KreaLab\CommonBundle\Model\BlankReferencemanEnvelope
     */
    protected $referenceman_envelope;

    /**
     * @var \KreaLab\CommonBundle\Model\BlankStockmanEnvelope
     */
    protected $stockman_envelope;

    /**
     * @var \KreaLab\CommonBundle\Model\BlankArchive
     */
    protected $archive_number;

    /**
     * @var \KreaLab\CommonBundle\Model\BlankLog
     */
    protected $blank_log;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $penalty_admin;

    /**
     * @var \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox
     */
    protected $referenceman_archive_box;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->life_logs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Blank
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
     * @return Blank
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
     * Set serie
     *
     * @param string $serie
     *
     * @return Blank
     */
    public function setSerie($serie)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie
     *
     * @return string
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Blank
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set leadingZeros
     *
     * @param integer $leadingZeros
     *
     * @return Blank
     */
    public function setLeadingZeros($leadingZeros)
    {
        $this->leading_zeros = $leadingZeros;

        return $this;
    }

    /**
     * Get leadingZeros
     *
     * @return integer
     */
    public function getLeadingZeros()
    {
        return $this->leading_zeros;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Blank
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
     * Set stockmanApplied
     *
     * @param \DateTime $stockmanApplied
     *
     * @return Blank
     */
    public function setStockmanApplied($stockmanApplied)
    {
        $this->stockman_applied = $stockmanApplied;

        return $this;
    }

    /**
     * Get stockmanApplied
     *
     * @return \DateTime
     */
    public function getStockmanApplied()
    {
        return $this->stockman_applied;
    }

    /**
     * Set referencemanApplied
     *
     * @param \DateTime $referencemanApplied
     *
     * @return Blank
     */
    public function setReferencemanApplied($referencemanApplied)
    {
        $this->referenceman_applied = $referencemanApplied;

        return $this;
    }

    /**
     * Get referencemanApplied
     *
     * @return \DateTime
     */
    public function getReferencemanApplied()
    {
        return $this->referenceman_applied;
    }

    /**
     * Set operatorApplied
     *
     * @param \DateTime $operatorApplied
     *
     * @return Blank
     */
    public function setOperatorApplied($operatorApplied)
    {
        $this->operator_applied = $operatorApplied;

        return $this;
    }

    /**
     * Get operatorApplied
     *
     * @return \DateTime
     */
    public function getOperatorApplied()
    {
        return $this->operator_applied;
    }

    /**
     * Set serviceLogApplied
     *
     * @param \DateTime $serviceLogApplied
     *
     * @return Blank
     */
    public function setServiceLogApplied($serviceLogApplied)
    {
        $this->service_log_applied = $serviceLogApplied;

        return $this;
    }

    /**
     * Get serviceLogApplied
     *
     * @return \DateTime
     */
    public function getServiceLogApplied()
    {
        return $this->service_log_applied;
    }

    /**
     * Set penaltySum
     *
     * @param integer $penaltySum
     *
     * @return Blank
     */
    public function setPenaltySum($penaltySum)
    {
        $this->penalty_sum = $penaltySum;

        return $this;
    }

    /**
     * Get penaltySum
     *
     * @return integer
     */
    public function getPenaltySum()
    {
        return $this->penalty_sum;
    }

    /**
     * Set penaltyDate
     *
     * @param \DateTime $penaltyDate
     *
     * @return Blank
     */
    public function setPenaltyDate($penaltyDate)
    {
        $this->penalty_date = $penaltyDate;

        return $this;
    }

    /**
     * Get penaltyDate
     *
     * @return \DateTime
     */
    public function getPenaltyDate()
    {
        return $this->penalty_date;
    }

    /**
     * Set stamp
     *
     * @param boolean $stamp
     *
     * @return Blank
     */
    public function setStamp($stamp)
    {
        $this->stamp = $stamp;

        return $this;
    }

    /**
     * Get stamp
     *
     * @return boolean
     */
    public function getStamp()
    {
        return $this->stamp;
    }

    /**
     * Set descCancelled
     *
     * @param string $descCancelled
     *
     * @return Blank
     */
    public function setDescCancelled($descCancelled)
    {
        $this->desc_cancelled = $descCancelled;

        return $this;
    }

    /**
     * Get descCancelled
     *
     * @return string
     */
    public function getDescCancelled()
    {
        return $this->desc_cancelled;
    }

    /**
     * Set serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     *
     * @return Blank
     */
    public function setServiceLog(\KreaLab\CommonBundle\Entity\ServiceLog $serviceLog = null)
    {
        $this->service_log = $serviceLog;

        return $this;
    }

    /**
     * Get serviceLog
     *
     * @return \KreaLab\CommonBundle\Entity\ServiceLog
     */
    public function getServiceLog()
    {
        return $this->service_log;
    }

    /**
     * Set replacedByBlankWithStamp
     *
     * @param \KreaLab\CommonBundle\Model\Blank $replacedByBlankWithStamp
     *
     * @return Blank
     */
    public function setReplacedByBlankWithStamp(\KreaLab\CommonBundle\Model\Blank $replacedByBlankWithStamp = null)
    {
        $this->replaced_by_blank_with_stamp = $replacedByBlankWithStamp;

        return $this;
    }

    /**
     * Get replacedByBlankWithStamp
     *
     * @return \KreaLab\CommonBundle\Model\Blank
     */
    public function getReplacedByBlankWithStamp()
    {
        return $this->replaced_by_blank_with_stamp;
    }

    /**
     * Set replacedBlankWithNoStamp
     *
     * @param \KreaLab\CommonBundle\Model\Blank $replacedBlankWithNoStamp
     *
     * @return Blank
     */
    public function setReplacedBlankWithNoStamp(\KreaLab\CommonBundle\Model\Blank $replacedBlankWithNoStamp = null)
    {
        $this->replaced_blank_with_no_stamp = $replacedBlankWithNoStamp;

        return $this;
    }

    /**
     * Get replacedBlankWithNoStamp
     *
     * @return \KreaLab\CommonBundle\Model\Blank
     */
    public function getReplacedBlankWithNoStamp()
    {
        return $this->replaced_blank_with_no_stamp;
    }

    /**
     * Add lifeLog
     *
     * @param \KreaLab\CommonBundle\Model\BlankLifeLog $lifeLog
     *
     * @return Blank
     */
    public function addLifeLog(\KreaLab\CommonBundle\Model\BlankLifeLog $lifeLog)
    {
        $this->life_logs[] = $lifeLog;

        return $this;
    }

    /**
     * Remove lifeLog
     *
     * @param \KreaLab\CommonBundle\Model\BlankLifeLog $lifeLog
     */
    public function removeLifeLog(\KreaLab\CommonBundle\Model\BlankLifeLog $lifeLog)
    {
        $this->life_logs->removeElement($lifeLog);
    }

    /**
     * Get lifeLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLifeLogs()
    {
        return $this->life_logs;
    }

    /**
     * Set legalEntity
     *
     * @param \KreaLab\CommonBundle\Entity\LegalEntity $legalEntity
     *
     * @return Blank
     */
    public function setLegalEntity(\KreaLab\CommonBundle\Entity\LegalEntity $legalEntity = null)
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
     * Set referenceType
     *
     * @param \KreaLab\CommonBundle\Entity\ReferenceType $referenceType
     *
     * @return Blank
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

    /**
     * Set stockman
     *
     * @param \KreaLab\CommonBundle\Entity\User $stockman
     *
     * @return Blank
     */
    public function setStockman(\KreaLab\CommonBundle\Entity\User $stockman = null)
    {
        $this->stockman = $stockman;

        return $this;
    }

    /**
     * Get stockman
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getStockman()
    {
        return $this->stockman;
    }

    /**
     * Set operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     *
     * @return Blank
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
     * Set referenceman
     *
     * @param \KreaLab\CommonBundle\Entity\User $referenceman
     *
     * @return Blank
     */
    public function setReferenceman(\KreaLab\CommonBundle\Entity\User $referenceman = null)
    {
        $this->referenceman = $referenceman;

        return $this;
    }

    /**
     * Get referenceman
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getReferenceman()
    {
        return $this->referenceman;
    }

    /**
     * Set oldReferenceman
     *
     * @param \KreaLab\CommonBundle\Entity\User $oldReferenceman
     *
     * @return Blank
     */
    public function setOldReferenceman(\KreaLab\CommonBundle\Entity\User $oldReferenceman = null)
    {
        $this->old_referenceman = $oldReferenceman;

        return $this;
    }

    /**
     * Get oldReferenceman
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getOldReferenceman()
    {
        return $this->old_referenceman;
    }

    /**
     * Set operatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Model\BlankOperatorEnvelope $operatorEnvelope
     *
     * @return Blank
     */
    public function setOperatorEnvelope(\KreaLab\CommonBundle\Model\BlankOperatorEnvelope $operatorEnvelope = null)
    {
        $this->operator_envelope = $operatorEnvelope;

        return $this;
    }

    /**
     * Get operatorEnvelope
     *
     * @return \KreaLab\CommonBundle\Model\BlankOperatorEnvelope
     */
    public function getOperatorEnvelope()
    {
        return $this->operator_envelope;
    }

    /**
     * Set operatorReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Model\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope
     *
     * @return Blank
     */
    public function setOperatorReferencemanEnvelope(\KreaLab\CommonBundle\Model\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope = null)
    {
        $this->operator_referenceman_envelope = $operatorReferencemanEnvelope;

        return $this;
    }

    /**
     * Get operatorReferencemanEnvelope
     *
     * @return \KreaLab\CommonBundle\Model\BlankOperatorReferencemanEnvelope
     */
    public function getOperatorReferencemanEnvelope()
    {
        return $this->operator_referenceman_envelope;
    }

    /**
     * Set referencemanReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Model\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope
     *
     * @return Blank
     */
    public function setReferencemanReferencemanEnvelope(\KreaLab\CommonBundle\Model\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope = null)
    {
        $this->referenceman_referenceman_envelope = $referencemanReferencemanEnvelope;

        return $this;
    }

    /**
     * Get referencemanReferencemanEnvelope
     *
     * @return \KreaLab\CommonBundle\Model\BlankReferencemanReferencemanEnvelope
     */
    public function getReferencemanReferencemanEnvelope()
    {
        return $this->referenceman_referenceman_envelope;
    }

    /**
     * Set referencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Model\BlankReferencemanEnvelope $referencemanEnvelope
     *
     * @return Blank
     */
    public function setReferencemanEnvelope(\KreaLab\CommonBundle\Model\BlankReferencemanEnvelope $referencemanEnvelope = null)
    {
        $this->referenceman_envelope = $referencemanEnvelope;

        return $this;
    }

    /**
     * Get referencemanEnvelope
     *
     * @return \KreaLab\CommonBundle\Model\BlankReferencemanEnvelope
     */
    public function getReferencemanEnvelope()
    {
        return $this->referenceman_envelope;
    }

    /**
     * Set stockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Model\BlankStockmanEnvelope $stockmanEnvelope
     *
     * @return Blank
     */
    public function setStockmanEnvelope(\KreaLab\CommonBundle\Model\BlankStockmanEnvelope $stockmanEnvelope = null)
    {
        $this->stockman_envelope = $stockmanEnvelope;

        return $this;
    }

    /**
     * Get stockmanEnvelope
     *
     * @return \KreaLab\CommonBundle\Model\BlankStockmanEnvelope
     */
    public function getStockmanEnvelope()
    {
        return $this->stockman_envelope;
    }

    /**
     * Set archiveNumber
     *
     * @param \KreaLab\CommonBundle\Model\BlankArchive $archiveNumber
     *
     * @return Blank
     */
    public function setArchiveNumber(\KreaLab\CommonBundle\Model\BlankArchive $archiveNumber = null)
    {
        $this->archive_number = $archiveNumber;

        return $this;
    }

    /**
     * Get archiveNumber
     *
     * @return \KreaLab\CommonBundle\Model\BlankArchive
     */
    public function getArchiveNumber()
    {
        return $this->archive_number;
    }

    /**
     * Set blankLog
     *
     * @param \KreaLab\CommonBundle\Model\BlankLog $blankLog
     *
     * @return Blank
     */
    public function setBlankLog(\KreaLab\CommonBundle\Model\BlankLog $blankLog = null)
    {
        $this->blank_log = $blankLog;

        return $this;
    }

    /**
     * Get blankLog
     *
     * @return \KreaLab\CommonBundle\Model\BlankLog
     */
    public function getBlankLog()
    {
        return $this->blank_log;
    }

    /**
     * Set penaltyAdmin
     *
     * @param \KreaLab\CommonBundle\Entity\User $penaltyAdmin
     *
     * @return Blank
     */
    public function setPenaltyAdmin(\KreaLab\CommonBundle\Entity\User $penaltyAdmin = null)
    {
        $this->penalty_admin = $penaltyAdmin;

        return $this;
    }

    /**
     * Get penaltyAdmin
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getPenaltyAdmin()
    {
        return $this->penalty_admin;
    }

    /**
     * Set referencemanArchiveBox
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox
     *
     * @return Blank
     */
    public function setReferencemanArchiveBox(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox = null)
    {
        $this->referenceman_archive_box = $referencemanArchiveBox;

        return $this;
    }

    /**
     * Get referencemanArchiveBox
     *
     * @return \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox
     */
    public function getReferencemanArchiveBox()
    {
        return $this->referenceman_archive_box;
    }
}

