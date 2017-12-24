<?php

namespace KreaLab\CommonBundle\Model;

/**
 * BlankLifeLog
 */
abstract class BlankLifeLog
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $operation_status;

    /**
     * @var integer
     */
    protected $envelope_id;

    /**
     * @var string
     */
    protected $envelope_type;

    /**
     * @var string
     */
    protected $start_status;

    /**
     * @var string
     */
    protected $end_status;

    /**
     * @var integer
     */
    protected $correct_blank_number;

    /**
     * @var string
     */
    protected $service_name;

    /**
     * @var integer
     */
    protected $penalty_sum;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $start_user;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $end_user;

    /**
     * @var \KreaLab\CommonBundle\Entity\Blank
     */
    protected $blank;

    /**
     * @var \KreaLab\CommonBundle\Entity\Workplace
     */
    protected $workplace;


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
     * Set operationStatus
     *
     * @param string $operationStatus
     *
     * @return BlankLifeLog
     */
    public function setOperationStatus($operationStatus)
    {
        $this->operation_status = $operationStatus;

        return $this;
    }

    /**
     * Get operationStatus
     *
     * @return string
     */
    public function getOperationStatus()
    {
        return $this->operation_status;
    }

    /**
     * Set envelopeId
     *
     * @param integer $envelopeId
     *
     * @return BlankLifeLog
     */
    public function setEnvelopeId($envelopeId)
    {
        $this->envelope_id = $envelopeId;

        return $this;
    }

    /**
     * Get envelopeId
     *
     * @return integer
     */
    public function getEnvelopeId()
    {
        return $this->envelope_id;
    }

    /**
     * Set envelopeType
     *
     * @param string $envelopeType
     *
     * @return BlankLifeLog
     */
    public function setEnvelopeType($envelopeType)
    {
        $this->envelope_type = $envelopeType;

        return $this;
    }

    /**
     * Get envelopeType
     *
     * @return string
     */
    public function getEnvelopeType()
    {
        return $this->envelope_type;
    }

    /**
     * Set startStatus
     *
     * @param string $startStatus
     *
     * @return BlankLifeLog
     */
    public function setStartStatus($startStatus)
    {
        $this->start_status = $startStatus;

        return $this;
    }

    /**
     * Get startStatus
     *
     * @return string
     */
    public function getStartStatus()
    {
        return $this->start_status;
    }

    /**
     * Set endStatus
     *
     * @param string $endStatus
     *
     * @return BlankLifeLog
     */
    public function setEndStatus($endStatus)
    {
        $this->end_status = $endStatus;

        return $this;
    }

    /**
     * Get endStatus
     *
     * @return string
     */
    public function getEndStatus()
    {
        return $this->end_status;
    }

    /**
     * Set correctBlankNumber
     *
     * @param integer $correctBlankNumber
     *
     * @return BlankLifeLog
     */
    public function setCorrectBlankNumber($correctBlankNumber)
    {
        $this->correct_blank_number = $correctBlankNumber;

        return $this;
    }

    /**
     * Get correctBlankNumber
     *
     * @return integer
     */
    public function getCorrectBlankNumber()
    {
        return $this->correct_blank_number;
    }

    /**
     * Set serviceName
     *
     * @param string $serviceName
     *
     * @return BlankLifeLog
     */
    public function setServiceName($serviceName)
    {
        $this->service_name = $serviceName;

        return $this;
    }

    /**
     * Get serviceName
     *
     * @return string
     */
    public function getServiceName()
    {
        return $this->service_name;
    }

    /**
     * Set penaltySum
     *
     * @param integer $penaltySum
     *
     * @return BlankLifeLog
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BlankLifeLog
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
     * Set startUser
     *
     * @param \KreaLab\CommonBundle\Entity\User $startUser
     *
     * @return BlankLifeLog
     */
    public function setStartUser(\KreaLab\CommonBundle\Entity\User $startUser = null)
    {
        $this->start_user = $startUser;

        return $this;
    }

    /**
     * Get startUser
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getStartUser()
    {
        return $this->start_user;
    }

    /**
     * Set endUser
     *
     * @param \KreaLab\CommonBundle\Entity\User $endUser
     *
     * @return BlankLifeLog
     */
    public function setEndUser(\KreaLab\CommonBundle\Entity\User $endUser = null)
    {
        $this->end_user = $endUser;

        return $this;
    }

    /**
     * Get endUser
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getEndUser()
    {
        return $this->end_user;
    }

    /**
     * Set blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     *
     * @return BlankLifeLog
     */
    public function setBlank(\KreaLab\CommonBundle\Entity\Blank $blank = null)
    {
        $this->blank = $blank;

        return $this;
    }

    /**
     * Get blank
     *
     * @return \KreaLab\CommonBundle\Entity\Blank
     */
    public function getBlank()
    {
        return $this->blank;
    }

    /**
     * Set workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return BlankLifeLog
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

