<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\ServiceLog as ServiceLogModel;

class ServiceLog extends ServiceLogModel
{
    protected $import = false;

    public function getEndTime()
    {
        $dateGiving = $this->getParent() ? $this->getParent()->getDateGiving() : $this->getDateGiving();
        if (!$dateGiving) {
            return null;
        }

        $dateGivingStr = date('Y-m-d', $dateGiving->getTimestamp());

        return date_create($dateGivingStr.' + '.$this->getService()->getLifetime().' months');
    }
}
