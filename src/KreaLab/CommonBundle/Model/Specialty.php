<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Specialty
 */
abstract class Specialty
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
     * @var boolean
     */
    protected $eeg = false;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $men;

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
     * @return Specialty
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
     * Set eeg
     *
     * @param boolean $eeg
     *
     * @return Specialty
     */
    public function setEeg($eeg)
    {
        $this->eeg = $eeg;

        return $this;
    }

    /**
     * Get eeg
     *
     * @return boolean
     */
    public function getEeg()
    {
        return $this->eeg;
    }

    /**
     * Add man
     *
     * @param \KreaLab\CommonBundle\Entity\Man $man
     *
     * @return Specialty
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
}

