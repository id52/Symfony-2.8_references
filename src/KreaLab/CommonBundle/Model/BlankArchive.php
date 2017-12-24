<?php

namespace KreaLab\CommonBundle\Model;

/**
 * BlankArchive
 */
abstract class BlankArchive
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
    protected $updated_at;

    /**
     * @var string
     */
    protected $archive_number;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blanks;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->blanks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BlankArchive
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
     * @return BlankArchive
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
     * Set archiveNumber
     *
     * @param string $archiveNumber
     *
     * @return BlankArchive
     */
    public function setArchiveNumber($archiveNumber)
    {
        $this->archive_number = $archiveNumber;

        return $this;
    }

    /**
     * Get archiveNumber
     *
     * @return string
     */
    public function getArchiveNumber()
    {
        return $this->archive_number;
    }

    /**
     * Add blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     *
     * @return BlankArchive
     */
    public function addBlank(\KreaLab\CommonBundle\Entity\Blank $blank)
    {
        $this->blanks[] = $blank;

        return $this;
    }

    /**
     * Remove blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     */
    public function removeBlank(\KreaLab\CommonBundle\Entity\Blank $blank)
    {
        $this->blanks->removeElement($blank);
    }

    /**
     * Get blanks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlanks()
    {
        return $this->blanks;
    }
}

