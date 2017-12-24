<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Agreement
 */
abstract class Agreement
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var \KreaLab\CommonBundle\Entity\LegalEntity
     */
    protected $guarantor;

    /**
     * @var \KreaLab\CommonBundle\Entity\LegalEntity
     */
    protected $executor;

    /**
     * @var \KreaLab\CommonBundle\Entity\Service
     */
    protected $service;

    /**
     * @var \KreaLab\CommonBundle\Entity\Workplace
     */
    protected $workplace;


    /**
     * Set type
     *
     * @param string $type
     *
     * @return Agreement
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set guarantor
     *
     * @param \KreaLab\CommonBundle\Entity\LegalEntity $guarantor
     *
     * @return Agreement
     */
    public function setGuarantor(\KreaLab\CommonBundle\Entity\LegalEntity $guarantor = null)
    {
        $this->guarantor = $guarantor;

        return $this;
    }

    /**
     * Get guarantor
     *
     * @return \KreaLab\CommonBundle\Entity\LegalEntity
     */
    public function getGuarantor()
    {
        return $this->guarantor;
    }

    /**
     * Set executor
     *
     * @param \KreaLab\CommonBundle\Entity\LegalEntity $executor
     *
     * @return Agreement
     */
    public function setExecutor(\KreaLab\CommonBundle\Entity\LegalEntity $executor = null)
    {
        $this->executor = $executor;

        return $this;
    }

    /**
     * Get executor
     *
     * @return \KreaLab\CommonBundle\Entity\LegalEntity
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * Set service
     *
     * @param \KreaLab\CommonBundle\Entity\Service $service
     *
     * @return Agreement
     */
    public function setService(\KreaLab\CommonBundle\Entity\Service $service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \KreaLab\CommonBundle\Entity\Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return Agreement
     */
    public function setWorkplace(\KreaLab\CommonBundle\Entity\Workplace $workplace)
    {
        $this->workplace = $workplace;

        return $this;
    }

    /**
     * Get workplace
     *
     * @return \KreaLab\CommonBundle\Entity\Workplace
     */
    public function getWorkplace()
    {
        return $this->workplace;
    }
}

