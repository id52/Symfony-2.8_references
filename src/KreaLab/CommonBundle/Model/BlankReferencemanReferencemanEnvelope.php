<?php

namespace KreaLab\CommonBundle\Model;

/**
 * BlankReferencemanReferencemanEnvelope
 */
abstract class BlankReferencemanReferencemanEnvelope
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
     * @var string
     */
    protected $serie;

    /**
     * @var integer
     */
    protected $first_num;

    /**
     * @var integer
     */
    protected $leading_zeros = 0;

    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var \DateTime
     */
    protected $referenceman_applied;

    /**
     * @var array
     */
    protected $intervals;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blanks;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $old_referenceman;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $referenceman;

    /**
     * @var \KreaLab\CommonBundle\Entity\ReferenceType
     */
    protected $reference_type;

    /**
     * @var \KreaLab\CommonBundle\Entity\LegalEntity
     */
    protected $legal_entity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->blanks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set serie
     *
     * @param string $serie
     *
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set firstNum
     *
     * @param integer $firstNum
     *
     * @return BlankReferencemanReferencemanEnvelope
     */
    public function setFirstNum($firstNum)
    {
        $this->first_num = $firstNum;

        return $this;
    }

    /**
     * Get firstNum
     *
     * @return integer
     */
    public function getFirstNum()
    {
        return $this->first_num;
    }

    /**
     * Set leadingZeros
     *
     * @param integer $leadingZeros
     *
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set amount
     *
     * @param integer $amount
     *
     * @return BlankReferencemanReferencemanEnvelope
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set referencemanApplied
     *
     * @param \DateTime $referencemanApplied
     *
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set intervals
     *
     * @param array $intervals
     *
     * @return BlankReferencemanReferencemanEnvelope
     */
    public function setIntervals($intervals)
    {
        $this->intervals = $intervals;

        return $this;
    }

    /**
     * Get intervals
     *
     * @return array
     */
    public function getIntervals()
    {
        return $this->intervals;
    }

    /**
     * Add blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     *
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set oldReferenceman
     *
     * @param \KreaLab\CommonBundle\Entity\User $oldReferenceman
     *
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set referenceman
     *
     * @param \KreaLab\CommonBundle\Entity\User $referenceman
     *
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set referenceType
     *
     * @param \KreaLab\CommonBundle\Entity\ReferenceType $referenceType
     *
     * @return BlankReferencemanReferencemanEnvelope
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
     * Set legalEntity
     *
     * @param \KreaLab\CommonBundle\Entity\LegalEntity $legalEntity
     *
     * @return BlankReferencemanReferencemanEnvelope
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
}

