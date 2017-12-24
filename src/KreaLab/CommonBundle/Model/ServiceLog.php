<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ServiceLog
 */
abstract class ServiceLog
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var boolean
     */
    protected $import;

    /**
     * @var string
     */
    protected $num;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var integer
     */
    protected $sum = 0;

    /**
     * @var \DateTime
     */
    protected $date_giving;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var string
     */
    protected $first_name;

    /**
     * @var string
     */
    protected $last_name;

    /**
     * @var string
     */
    protected $patronymic;

    /**
     * @var string
     */
    protected $eeg_conclusion;

    /**
     * @var \DateTime
     */
    protected $birthday;

    /**
     * @var string
     */
    protected $num_blank;

    /**
     * @var string
     */
    protected $return_comment;

    /**
     * @var \KreaLab\CommonBundle\Entity\Blank
     */
    protected $blank;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $docs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $medical_center_corrects;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $children;

    /**
     * @var \KreaLab\CommonBundle\Entity\Service
     */
    protected $service;

    /**
     * @var \KreaLab\CommonBundle\Entity\Workplace
     */
    protected $workplace;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $operator;

    /**
     * @var \KreaLab\CommonBundle\Entity\Cashbox
     */
    protected $cashbox;

    /**
     * @var \KreaLab\CommonBundle\Entity\Envelope
     */
    protected $envelope;

    /**
     * @var \KreaLab\CommonBundle\Model\ServiceLog
     */
    protected $parent;

    /**
     * @var \KreaLab\CommonBundle\Model\ServiceLog
     */
    protected $medical_center_error;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $categories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->docs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->medical_center_corrects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set import
     *
     * @param boolean $import
     *
     * @return ServiceLog
     */
    public function setImport($import)
    {
        $this->import = $import;

        return $this;
    }

    /**
     * Get import
     *
     * @return boolean
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * Set num
     *
     * @param string $num
     *
     * @return ServiceLog
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
     * Set params
     *
     * @param array $params
     *
     * @return ServiceLog
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set sum
     *
     * @param integer $sum
     *
     * @return ServiceLog
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
     * Set dateGiving
     *
     * @param \DateTime $dateGiving
     *
     * @return ServiceLog
     */
    public function setDateGiving($dateGiving)
    {
        $this->date_giving = $dateGiving;

        return $this;
    }

    /**
     * Get dateGiving
     *
     * @return \DateTime
     */
    public function getDateGiving()
    {
        return $this->date_giving;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ServiceLog
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return ServiceLog
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return ServiceLog
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set patronymic
     *
     * @param string $patronymic
     *
     * @return ServiceLog
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * Get patronymic
     *
     * @return string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * Set eegConclusion
     *
     * @param string $eegConclusion
     *
     * @return ServiceLog
     */
    public function setEegConclusion($eegConclusion)
    {
        $this->eeg_conclusion = $eegConclusion;

        return $this;
    }

    /**
     * Get eegConclusion
     *
     * @return string
     */
    public function getEegConclusion()
    {
        return $this->eeg_conclusion;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return ServiceLog
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set numBlank
     *
     * @param string $numBlank
     *
     * @return ServiceLog
     */
    public function setNumBlank($numBlank)
    {
        $this->num_blank = $numBlank;

        return $this;
    }

    /**
     * Get numBlank
     *
     * @return string
     */
    public function getNumBlank()
    {
        return $this->num_blank;
    }

    /**
     * Set returnComment
     *
     * @param string $returnComment
     *
     * @return ServiceLog
     */
    public function setReturnComment($returnComment)
    {
        $this->return_comment = $returnComment;

        return $this;
    }

    /**
     * Get returnComment
     *
     * @return string
     */
    public function getReturnComment()
    {
        return $this->return_comment;
    }

    /**
     * Set blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     *
     * @return ServiceLog
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
     * Add doc
     *
     * @param \KreaLab\CommonBundle\Entity\Image $doc
     *
     * @return ServiceLog
     */
    public function addDoc(\KreaLab\CommonBundle\Entity\Image $doc)
    {
        $this->docs[] = $doc;

        return $this;
    }

    /**
     * Remove doc
     *
     * @param \KreaLab\CommonBundle\Entity\Image $doc
     */
    public function removeDoc(\KreaLab\CommonBundle\Entity\Image $doc)
    {
        $this->docs->removeElement($doc);
    }

    /**
     * Get docs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * Add medicalCenterCorrect
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $medicalCenterCorrect
     *
     * @return ServiceLog
     */
    public function addMedicalCenterCorrect(\KreaLab\CommonBundle\Model\ServiceLog $medicalCenterCorrect)
    {
        $this->medical_center_corrects[] = $medicalCenterCorrect;

        return $this;
    }

    /**
     * Remove medicalCenterCorrect
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $medicalCenterCorrect
     */
    public function removeMedicalCenterCorrect(\KreaLab\CommonBundle\Model\ServiceLog $medicalCenterCorrect)
    {
        $this->medical_center_corrects->removeElement($medicalCenterCorrect);
    }

    /**
     * Get medicalCenterCorrects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedicalCenterCorrects()
    {
        return $this->medical_center_corrects;
    }

    /**
     * Add child
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $child
     *
     * @return ServiceLog
     */
    public function addChild(\KreaLab\CommonBundle\Model\ServiceLog $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $child
     */
    public function removeChild(\KreaLab\CommonBundle\Model\ServiceLog $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set service
     *
     * @param \KreaLab\CommonBundle\Entity\Service $service
     *
     * @return ServiceLog
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
     * Set workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return ServiceLog
     */
    public function setWorkplace(\KreaLab\CommonBundle\Entity\Workplace $workplace)
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

    /**
     * Set operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     *
     * @return ServiceLog
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
     * Set cashbox
     *
     * @param \KreaLab\CommonBundle\Entity\Cashbox $cashbox
     *
     * @return ServiceLog
     */
    public function setCashbox(\KreaLab\CommonBundle\Entity\Cashbox $cashbox = null)
    {
        $this->cashbox = $cashbox;

        return $this;
    }

    /**
     * Get cashbox
     *
     * @return \KreaLab\CommonBundle\Entity\Cashbox
     */
    public function getCashbox()
    {
        return $this->cashbox;
    }

    /**
     * Set envelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $envelope
     *
     * @return ServiceLog
     */
    public function setEnvelope(\KreaLab\CommonBundle\Entity\Envelope $envelope = null)
    {
        $this->envelope = $envelope;

        return $this;
    }

    /**
     * Get envelope
     *
     * @return \KreaLab\CommonBundle\Entity\Envelope
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * Set parent
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $parent
     *
     * @return ServiceLog
     */
    public function setParent(\KreaLab\CommonBundle\Model\ServiceLog $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \KreaLab\CommonBundle\Model\ServiceLog
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set medicalCenterError
     *
     * @param \KreaLab\CommonBundle\Model\ServiceLog $medicalCenterError
     *
     * @return ServiceLog
     */
    public function setMedicalCenterError(\KreaLab\CommonBundle\Model\ServiceLog $medicalCenterError = null)
    {
        $this->medical_center_error = $medicalCenterError;

        return $this;
    }

    /**
     * Get medicalCenterError
     *
     * @return \KreaLab\CommonBundle\Model\ServiceLog
     */
    public function getMedicalCenterError()
    {
        return $this->medical_center_error;
    }

    /**
     * Add category
     *
     * @param \KreaLab\CommonBundle\Entity\Category $category
     *
     * @return ServiceLog
     */
    public function addCategory(\KreaLab\CommonBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \KreaLab\CommonBundle\Entity\Category $category
     */
    public function removeCategory(\KreaLab\CommonBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }
}

