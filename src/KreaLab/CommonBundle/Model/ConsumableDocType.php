<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ConsumableDocType
 */
abstract class ConsumableDocType
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
    protected $active = 1;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $consumable_doc_types;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->consumable_doc_types = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ConsumableDocType
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
     * Set active
     *
     * @param boolean $active
     *
     * @return ConsumableDocType
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add consumableDocType
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumableDocType
     *
     * @return ConsumableDocType
     */
    public function addConsumableDocType(\KreaLab\CommonBundle\Entity\Consumable $consumableDocType)
    {
        $this->consumable_doc_types[] = $consumableDocType;

        return $this;
    }

    /**
     * Remove consumableDocType
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumableDocType
     */
    public function removeConsumableDocType(\KreaLab\CommonBundle\Entity\Consumable $consumableDocType)
    {
        $this->consumable_doc_types->removeElement($consumableDocType);
    }

    /**
     * Get consumableDocTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumableDocTypes()
    {
        return $this->consumable_doc_types;
    }
}

