<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Filial
 */
abstract class Filial
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var boolean
     */
    protected $active;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $name_short;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var array
     */
    protected $ips;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $workplaces;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $filial_ban_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $schedules;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $shift_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->workplaces = new \Doctrine\Common\Collections\ArrayCollection();
        $this->filial_ban_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->schedules = new \Doctrine\Common\Collections\ArrayCollection();
        $this->shift_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set active
     *
     * @param boolean $active
     *
     * @return Filial
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
     * Set name
     *
     * @param string $name
     *
     * @return Filial
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
     * Set nameShort
     *
     * @param string $nameShort
     *
     * @return Filial
     */
    public function setNameShort($nameShort)
    {
        $this->name_short = $nameShort;

        return $this;
    }

    /**
     * Get nameShort
     *
     * @return string
     */
    public function getNameShort()
    {
        return $this->name_short;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Filial
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set ips
     *
     * @param array $ips
     *
     * @return Filial
     */
    public function setIps($ips)
    {
        $this->ips = $ips;

        return $this;
    }

    /**
     * Get ips
     *
     * @return array
     */
    public function getIps()
    {
        return $this->ips;
    }

    /**
     * Add workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return Filial
     */
    public function addWorkplace(\KreaLab\CommonBundle\Entity\Workplace $workplace)
    {
        $this->workplaces[] = $workplace;

        return $this;
    }

    /**
     * Remove workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     */
    public function removeWorkplace(\KreaLab\CommonBundle\Entity\Workplace $workplace)
    {
        $this->workplaces->removeElement($workplace);
    }

    /**
     * Get workplaces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkplaces()
    {
        return $this->workplaces;
    }

    /**
     * Add filialBanLog
     *
     * @param \KreaLab\CommonBundle\Model\FilialBanLog $filialBanLog
     *
     * @return Filial
     */
    public function addFilialBanLog(\KreaLab\CommonBundle\Model\FilialBanLog $filialBanLog)
    {
        $this->filial_ban_logs[] = $filialBanLog;

        return $this;
    }

    /**
     * Remove filialBanLog
     *
     * @param \KreaLab\CommonBundle\Model\FilialBanLog $filialBanLog
     */
    public function removeFilialBanLog(\KreaLab\CommonBundle\Model\FilialBanLog $filialBanLog)
    {
        $this->filial_ban_logs->removeElement($filialBanLog);
    }

    /**
     * Get filialBanLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFilialBanLogs()
    {
        return $this->filial_ban_logs;
    }

    /**
     * Add schedule
     *
     * @param \KreaLab\CommonBundle\Entity\Schedule $schedule
     *
     * @return Filial
     */
    public function addSchedule(\KreaLab\CommonBundle\Entity\Schedule $schedule)
    {
        $this->schedules[] = $schedule;

        return $this;
    }

    /**
     * Remove schedule
     *
     * @param \KreaLab\CommonBundle\Entity\Schedule $schedule
     */
    public function removeSchedule(\KreaLab\CommonBundle\Entity\Schedule $schedule)
    {
        $this->schedules->removeElement($schedule);
    }

    /**
     * Get schedules
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * Add shiftLog
     *
     * @param \KreaLab\CommonBundle\Entity\ShiftLog $shiftLog
     *
     * @return Filial
     */
    public function addShiftLog(\KreaLab\CommonBundle\Entity\ShiftLog $shiftLog)
    {
        $this->shift_logs[] = $shiftLog;

        return $this;
    }

    /**
     * Remove shiftLog
     *
     * @param \KreaLab\CommonBundle\Entity\ShiftLog $shiftLog
     */
    public function removeShiftLog(\KreaLab\CommonBundle\Entity\ShiftLog $shiftLog)
    {
        $this->shift_logs->removeElement($shiftLog);
    }

    /**
     * Get shiftLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShiftLogs()
    {
        return $this->shift_logs;
    }

    /**
     * Add user
     *
     * @param \KreaLab\CommonBundle\Entity\User $user
     *
     * @return Filial
     */
    public function addUser(\KreaLab\CommonBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \KreaLab\CommonBundle\Entity\User $user
     */
    public function removeUser(\KreaLab\CommonBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}

