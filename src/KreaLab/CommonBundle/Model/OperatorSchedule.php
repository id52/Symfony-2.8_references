<?php

namespace KreaLab\CommonBundle\Model;

/**
 * OperatorSchedule
 */
abstract class OperatorSchedule
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $operator;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return OperatorSchedule
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set operator
     *
     * @param \KreaLab\CommonBundle\Entity\User $operator
     *
     * @return OperatorSchedule
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
}

