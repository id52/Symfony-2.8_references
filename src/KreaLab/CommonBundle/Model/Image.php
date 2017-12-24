<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Image
 */
abstract class Image extends \KreaLab\AdminSkeletonBundle\Entity\Image
{
    /**
     * @var \KreaLab\CommonBundle\Entity\ServiceLog
     */
    protected $service_log;


    /**
     * Set serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     *
     * @return Image
     */
    public function setServiceLog(\KreaLab\CommonBundle\Entity\ServiceLog $serviceLog = null)
    {
        $this->service_log = $serviceLog;

        return $this;
    }

    /**
     * Get serviceLog
     *
     * @return \KreaLab\CommonBundle\Entity\ServiceLog
     */
    public function getServiceLog()
    {
        return $this->service_log;
    }
}

