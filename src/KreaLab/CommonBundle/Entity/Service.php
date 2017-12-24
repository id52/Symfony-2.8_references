<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\AppBundle\Controller\OperatorController;
use KreaLab\CommonBundle\Model\Service as ServiceModel;

class Service extends ServiceModel
{
    protected $agreement = null;
    protected $active = true;
    protected $driver_reference = true;
    protected $is_duplicate = false;
    protected $is_eeg_conclusion = false;
    protected $is_not_duplicate_price = false;
    protected $is_gnoch = false;
    protected $price = 0;
    protected $min_price = 0;
    protected $subjects = [];
    protected $medical_center_errors = [];

    public function __toString()
    {
        return $this->getName();
    }

    public function getFirstAgreement()
    {
        $agreements = $this->getAgreements();
        if (isset($agreements[0])) {
            return $agreements[0];
        } else {
            return null;
        }
    }

    public function getAgreementByWorkplace(Workplace $workplace)
    {
        $agreements = $this->getAgreements();
        foreach ($agreements as $agreement) { /** @var $agreement Agreement */
            if ($agreement->getWorkplace() === $workplace) {
                return $agreement;
            }
        }

        return null;
    }

    public function setPrice($price)
    {
        $this->price = max($price, 0);
        return $this;
    }

    public function setMinPrice($minPrice)
    {
        $this->min_price = max($minPrice, 0);
        return $this;
    }

    /**
     * Get isDuplicatePrice
     *
     * @return boolean
     */
    public function getIsDuplicatePrice()
    {
        return !$this->is_not_duplicate_price;
    }

    /**
     * Get isRevisitPrice
     *
     * @return boolean
     */
    public function getIsRevisitPrice()
    {
        return !$this->is_not_revisit_price;
    }
}
