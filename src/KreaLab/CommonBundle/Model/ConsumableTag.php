<?php

namespace KreaLab\CommonBundle\Model;

/**
 * ConsumableTag
 */
abstract class ConsumableTag
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
     * @var \KreaLab\CommonBundle\Model\ConsumableTagCategory
     */
    protected $tag_category;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $consumables;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->consumables = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ConsumableTag
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
     * Set tagCategory
     *
     * @param \KreaLab\CommonBundle\Model\ConsumableTagCategory $tagCategory
     *
     * @return ConsumableTag
     */
    public function setTagCategory(\KreaLab\CommonBundle\Model\ConsumableTagCategory $tagCategory = null)
    {
        $this->tag_category = $tagCategory;

        return $this;
    }

    /**
     * Get tagCategory
     *
     * @return \KreaLab\CommonBundle\Model\ConsumableTagCategory
     */
    public function getTagCategory()
    {
        return $this->tag_category;
    }

    /**
     * Add consumable
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumable
     *
     * @return ConsumableTag
     */
    public function addConsumable(\KreaLab\CommonBundle\Entity\Consumable $consumable)
    {
        $this->consumables[] = $consumable;

        return $this;
    }

    /**
     * Remove consumable
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumable
     */
    public function removeConsumable(\KreaLab\CommonBundle\Entity\Consumable $consumable)
    {
        $this->consumables->removeElement($consumable);
    }

    /**
     * Get consumables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumables()
    {
        return $this->consumables;
    }
}

