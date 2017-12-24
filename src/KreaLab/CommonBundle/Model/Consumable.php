<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Consumable
 */
abstract class Consumable
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
     * @var string
     */
    protected $description;

    /**
     * @var integer
     */
    protected $sum;

    /**
     * @var \DateTime
     */
    protected $doc_date;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \DateTime
     */
    protected $updated_at;

    /**
     * @var \KreaLab\CommonBundle\Entity\Order
     */
    protected $order;

    /**
     * @var \KreaLab\CommonBundle\Entity\User
     */
    protected $orderman;

    /**
     * @var \KreaLab\CommonBundle\Model\ConsumableDocType
     */
    protected $consumable_doc_type;

    /**
     * @var \KreaLab\CommonBundle\Entity\OrdermanConsumableBox
     */
    protected $orderman_consumable_box;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $tags;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Consumable
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
     * Set description
     *
     * @param string $description
     *
     * @return Consumable
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sum
     *
     * @param integer $sum
     *
     * @return Consumable
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
     * Set docDate
     *
     * @param \DateTime $docDate
     *
     * @return Consumable
     */
    public function setDocDate($docDate)
    {
        $this->doc_date = $docDate;

        return $this;
    }

    /**
     * Get docDate
     *
     * @return \DateTime
     */
    public function getDocDate()
    {
        return $this->doc_date;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Consumable
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Consumable
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set order
     *
     * @param \KreaLab\CommonBundle\Entity\Order $order
     *
     * @return Consumable
     */
    public function setOrder(\KreaLab\CommonBundle\Entity\Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \KreaLab\CommonBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set orderman
     *
     * @param \KreaLab\CommonBundle\Entity\User $orderman
     *
     * @return Consumable
     */
    public function setOrderman(\KreaLab\CommonBundle\Entity\User $orderman = null)
    {
        $this->orderman = $orderman;

        return $this;
    }

    /**
     * Get orderman
     *
     * @return \KreaLab\CommonBundle\Entity\User
     */
    public function getOrderman()
    {
        return $this->orderman;
    }

    /**
     * Set consumableDocType
     *
     * @param \KreaLab\CommonBundle\Model\ConsumableDocType $consumableDocType
     *
     * @return Consumable
     */
    public function setConsumableDocType(\KreaLab\CommonBundle\Model\ConsumableDocType $consumableDocType = null)
    {
        $this->consumable_doc_type = $consumableDocType;

        return $this;
    }

    /**
     * Get consumableDocType
     *
     * @return \KreaLab\CommonBundle\Model\ConsumableDocType
     */
    public function getConsumableDocType()
    {
        return $this->consumable_doc_type;
    }

    /**
     * Set ordermanConsumableBox
     *
     * @param \KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBox
     *
     * @return Consumable
     */
    public function setOrdermanConsumableBox(\KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBox = null)
    {
        $this->orderman_consumable_box = $ordermanConsumableBox;

        return $this;
    }

    /**
     * Get ordermanConsumableBox
     *
     * @return \KreaLab\CommonBundle\Entity\OrdermanConsumableBox
     */
    public function getOrdermanConsumableBox()
    {
        return $this->orderman_consumable_box;
    }

    /**
     * Add tag
     *
     * @param \KreaLab\CommonBundle\Model\ConsumableTag $tag
     *
     * @return Consumable
     */
    public function addTag(\KreaLab\CommonBundle\Model\ConsumableTag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \KreaLab\CommonBundle\Model\ConsumableTag $tag
     */
    public function removeTag(\KreaLab\CommonBundle\Model\ConsumableTag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }
}

