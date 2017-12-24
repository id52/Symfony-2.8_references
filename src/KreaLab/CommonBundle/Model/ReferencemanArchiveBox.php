<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ReferencemanArchiveBox
 */
abstract class ReferencemanArchiveBox
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
     * @var string
     */
    protected $serie;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blanks;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $operator;

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
     * @return ReferencemanArchiveBox
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
     * @return ReferencemanArchiveBox
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
     * Set serie
     *
     * @param string $serie
     *
     * @return ReferencemanArchiveBox
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
     * Set type
     *
     * @param string $type
     *
     * @return ReferencemanArchiveBox
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     *
     * @return ReferencemanArchiveBox
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
     * Set operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     *
     * @return ReferencemanArchiveBox
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
     * @return ReferencemanArchiveBox
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
     * @return ReferencemanArchiveBox
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
     * @return ReferencemanArchiveBox
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

