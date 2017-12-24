<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ActionLog
 */
abstract class ActionLog
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $action_type;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $user;


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
     * Set actionType
     *
     * @param string $actionType
     *
     * @return ActionLog
     */
    public function setActionType($actionType)
    {
        $this->action_type = $actionType;

        return $this;
    }

    /**
     * Get actionType
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->action_type;
    }

    /**
     * Set params
     *
     * @param array $params
     *
     * @return ActionLog
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ActionLog
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
     * Set user
     *
     * @param \KreaLab\CommonBundle\Entity\User $user
     *
     * @return ActionLog
     */
    public function setUser(\KreaLab\CommonBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}

