<?php

namespace KreaLab\CommonBundle\Model;

/**
 * OperatorReplacementLog
 */
abstract class OperatorReplacementLog
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
    protected $removed_at;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $predecessor;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $successor;


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
     * @return OperatorReplacementLog
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
     * Set removedAt
     *
     * @param \DateTime $removedAt
     *
     * @return OperatorReplacementLog
     */
    public function setRemovedAt($removedAt)
    {
        $this->removed_at = $removedAt;

        return $this;
    }

    /**
     * Get removedAt
     *
     * @return \DateTime
     */
    public function getRemovedAt()
    {
        return $this->removed_at;
    }

    /**
     * Set predecessor
     *
     * @param \KreaLab\CommonBundle\Entity\User $predecessor
     *
     * @return OperatorReplacementLog
     */
    public function setPredecessor(\KreaLab\CommonBundle\Entity\User $predecessor = null)
    {
        $this->predecessor = $predecessor;

        return $this;
    }

    /**
     * Get predecessor
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getPredecessor()
    {
        return $this->predecessor;
    }

    /**
     * Set successor
     *
     * @param \KreaLab\CommonBundle\Entity\User $successor
     *
     * @return OperatorReplacementLog
     */
    public function setSuccessor(\KreaLab\CommonBundle\Entity\User $successor = null)
    {
        $this->successor = $successor;

        return $this;
    }

    /**
     * Get successor
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getSuccessor()
    {
        return $this->successor;
    }
}

