<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Brigade
 */
abstract class Brigade
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
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $men;

    /**
     * @var \KreaLab\CommonBundle\Entity\LegalEntity
     */
    protected $legal_entity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->men = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Brigade
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
     * Add man
     *
     * @param \KreaLab\CommonBundle\Entity\Man $man
     *
     * @return Brigade
     */
    public function addMan(\KreaLab\CommonBundle\Entity\Man $man)
    {
        $this->men[] = $man;

        return $this;
    }

    /**
     * Remove man
     *
     * @param \KreaLab\CommonBundle\Entity\Man $man
     */
    public function removeMan(\KreaLab\CommonBundle\Entity\Man $man)
    {
        $this->men->removeElement($man);
    }

    /**
     * Get men
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMen()
    {
        return $this->men;
    }

    /**
     * Set legalEntity
     *
     * @param \KreaLab\CommonBundle\Entity\LegalEntity $legalEntity
     *
     * @return Brigade
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

