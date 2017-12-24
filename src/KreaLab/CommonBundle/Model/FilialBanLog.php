<?php

namespace KreaLab\CommonBundle\Model;

/**
 * FilialBanLog
 */
abstract class FilialBanLog
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
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $user;

    /**
     * @var \KreaLab\CommonBundle\Entity\Filial
     */
    protected $filial;


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
     * @return FilialBanLog
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
     * @return FilialBanLog
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

    /**
     * Set filial
     *
     * @param \KreaLab\CommonBundle\Entity\Filial $filial
     *
     * @return FilialBanLog
     */
    public function setFilial(\KreaLab\CommonBundle\Entity\Filial $filial)
    {
        $this->filial = $filial;

        return $this;
    }

    /**
     * Get filial
     *
     * @return \KreaLab\CommonBundle\Entity\Filial
     */
    public function getFilial()
    {
        return $this->filial;
    }
}

