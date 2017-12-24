<?php

namespace KreaLab\CommonBundle\Model;

/**
 * User
 */
abstract class User
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
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $last_name;

    /**
     * @var string
     */
    protected $first_name;

    /**
     * @var string
     */
    protected $patronymic;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $power_attorney;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var array
     */
    protected $ips;

    /**
     * @var array
     */
    protected $auth_failure_info;

    /**
     * @var boolean
     */
    protected $force_change_pass;

    /**
     * @var integer
     */
    protected $orderman_sum = 0;

    /**
     * @var integer
     */
    protected $supervisor_sum = 0;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var array
     */
    protected $intervals;

    /**
     * @var array
     */
    protected $referenceman_intervals;

    /**
     * @var \KreaLab\CommonBundle\Model\User
     */
    protected $successor;

    /**
     * @var \KreaLab\CommonBundle\Model\User
     */
    protected $predecessor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $sessions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $filial_ban_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $action_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $shift_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $service_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $courier_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $courier_sgls;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $supervisor_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $supervisor_sgls;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $supervisor_cgls;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $cashier_cgls;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $acquittanceman_orders;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_orders;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $treasurer_orders;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $supervisor_repayments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $consumable_ordermans;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $stockman_blanks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_blanks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_blanks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $old_referenceman_blanks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_envelope__operator_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_envelope__referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_referenceman_envelope__operator_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_referenceman_envelope__referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_referenceman_envelope__referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_referenceman_envelope__old_referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_envelope__stockman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_envelope__referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $stockman_envelope__stockman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $stockman_envelope__referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blank_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $life_logs_start_user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $life_logs_end_user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $admin_penalty_blanks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_archive_box_operators;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_archive_box_referencemen;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $orderman_consumable_box_ordermen;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $orderman_consumable_box_racquittancemen;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_replacement_log__predecessors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_replacement_log__successors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_schedules;

    /**
     * @var \KreaLab\CommonBundle\Entity\Workplace
     */
    protected $workplace;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $filials;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sessions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->filial_ban_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->action_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->shift_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->service_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->courier_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->courier_sgls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supervisor_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supervisor_sgls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supervisor_cgls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cashier_cgls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->acquittanceman_orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->treasurer_orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supervisor_repayments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->consumable_ordermans = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stockman_blanks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_blanks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_blanks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->old_referenceman_blanks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_envelope__operator_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_envelope__referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_referenceman_envelope__operator_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_referenceman_envelope__referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_referenceman_envelope__referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_referenceman_envelope__old_referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_envelope__stockman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_envelope__referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stockman_envelope__stockman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stockman_envelope__referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blank_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->life_logs_start_user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->life_logs_end_user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->admin_penalty_blanks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_archive_box_operators = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_archive_box_referencemen = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orderman_consumable_box_ordermen = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orderman_consumable_box_racquittancemen = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_replacement_log__predecessors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_replacement_log__successors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_schedules = new \Doctrine\Common\Collections\ArrayCollection();
        $this->filials = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return User
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set patronymic
     *
     * @param string $patronymic
     *
     * @return User
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * Get patronymic
     *
     * @return string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set powerAttorney
     *
     * @param string $powerAttorney
     *
     * @return User
     */
    public function setPowerAttorney($powerAttorney)
    {
        $this->power_attorney = $powerAttorney;

        return $this;
    }

    /**
     * Get powerAttorney
     *
     * @return string
     */
    public function getPowerAttorney()
    {
        return $this->power_attorney;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set ips
     *
     * @param array $ips
     *
     * @return User
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
     * Set authFailureInfo
     *
     * @param array $authFailureInfo
     *
     * @return User
     */
    public function setAuthFailureInfo($authFailureInfo)
    {
        $this->auth_failure_info = $authFailureInfo;

        return $this;
    }

    /**
     * Get authFailureInfo
     *
     * @return array
     */
    public function getAuthFailureInfo()
    {
        return $this->auth_failure_info;
    }

    /**
     * Set forceChangePass
     *
     * @param boolean $forceChangePass
     *
     * @return User
     */
    public function setForceChangePass($forceChangePass)
    {
        $this->force_change_pass = $forceChangePass;

        return $this;
    }

    /**
     * Get forceChangePass
     *
     * @return boolean
     */
    public function getForceChangePass()
    {
        return $this->force_change_pass;
    }

    /**
     * Set ordermanSum
     *
     * @param integer $ordermanSum
     *
     * @return User
     */
    public function setOrdermanSum($ordermanSum)
    {
        $this->orderman_sum = $ordermanSum;

        return $this;
    }

    /**
     * Get ordermanSum
     *
     * @return integer
     */
    public function getOrdermanSum()
    {
        return $this->orderman_sum;
    }

    /**
     * Set supervisorSum
     *
     * @param integer $supervisorSum
     *
     * @return User
     */
    public function setSupervisorSum($supervisorSum)
    {
        $this->supervisor_sum = $supervisorSum;

        return $this;
    }

    /**
     * Get supervisorSum
     *
     * @return integer
     */
    public function getSupervisorSum()
    {
        return $this->supervisor_sum;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
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
     * Set intervals
     *
     * @param array $intervals
     *
     * @return User
     */
    public function setIntervals($intervals)
    {
        $this->intervals = $intervals;

        return $this;
    }

    /**
     * Get intervals
     *
     * @return array
     */
    public function getIntervals()
    {
        return $this->intervals;
    }

    /**
     * Set referencemanIntervals
     *
     * @param array $referencemanIntervals
     *
     * @return User
     */
    public function setReferencemanIntervals($referencemanIntervals)
    {
        $this->referenceman_intervals = $referencemanIntervals;

        return $this;
    }

    /**
     * Get referencemanIntervals
     *
     * @return array
     */
    public function getReferencemanIntervals()
    {
        return $this->referenceman_intervals;
    }

    /**
     * Set successor
     *
     * @param \KreaLab\CommonBundle\Model\User $successor
     *
     * @return User
     */
    public function setSuccessor(\KreaLab\CommonBundle\Model\User $successor = null)
    {
        $this->successor = $successor;

        return $this;
    }

    /**
     * Get successor
     *
     * @return \KreaLab\CommonBundle\Model\User
     */
    public function getSuccessor()
    {
        return $this->successor;
    }

    /**
     * Set predecessor
     *
     * @param \KreaLab\CommonBundle\Model\User $predecessor
     *
     * @return User
     */
    public function setPredecessor(\KreaLab\CommonBundle\Model\User $predecessor = null)
    {
        $this->predecessor = $predecessor;

        return $this;
    }

    /**
     * Get predecessor
     *
     * @return \KreaLab\CommonBundle\Model\User
     */
    public function getPredecessor()
    {
        return $this->predecessor;
    }

    /**
     * Add session
     *
     * @param \KreaLab\CommonBundle\Entity\Session $session
     *
     * @return User
     */
    public function addSession(\KreaLab\CommonBundle\Entity\Session $session)
    {
        $this->sessions[] = $session;

        return $this;
    }

    /**
     * Remove session
     *
     * @param \KreaLab\CommonBundle\Entity\Session $session
     */
    public function removeSession(\KreaLab\CommonBundle\Entity\Session $session)
    {
        $this->sessions->removeElement($session);
    }

    /**
     * Get sessions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * Add filialBanLog
     *
     * @param \KreaLab\CommonBundle\Entity\FilialBanLog $filialBanLog
     *
     * @return User
     */
    public function addFilialBanLog(\KreaLab\CommonBundle\Entity\FilialBanLog $filialBanLog)
    {
        $this->filial_ban_logs[] = $filialBanLog;

        return $this;
    }

    /**
     * Remove filialBanLog
     *
     * @param \KreaLab\CommonBundle\Entity\FilialBanLog $filialBanLog
     */
    public function removeFilialBanLog(\KreaLab\CommonBundle\Entity\FilialBanLog $filialBanLog)
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
     * Add actionLog
     *
     * @param \KreaLab\CommonBundle\Entity\ActionLog $actionLog
     *
     * @return User
     */
    public function addActionLog(\KreaLab\CommonBundle\Entity\ActionLog $actionLog)
    {
        $this->action_logs[] = $actionLog;

        return $this;
    }

    /**
     * Remove actionLog
     *
     * @param \KreaLab\CommonBundle\Entity\ActionLog $actionLog
     */
    public function removeActionLog(\KreaLab\CommonBundle\Entity\ActionLog $actionLog)
    {
        $this->action_logs->removeElement($actionLog);
    }

    /**
     * Get actionLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionLogs()
    {
        return $this->action_logs;
    }

    /**
     * Add shiftLog
     *
     * @param \KreaLab\CommonBundle\Entity\ShiftLog $shiftLog
     *
     * @return User
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
     * Add serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     *
     * @return User
     */
    public function addServiceLog(\KreaLab\CommonBundle\Entity\ServiceLog $serviceLog)
    {
        $this->service_logs[] = $serviceLog;

        return $this;
    }

    /**
     * Remove serviceLog
     *
     * @param \KreaLab\CommonBundle\Entity\ServiceLog $serviceLog
     */
    public function removeServiceLog(\KreaLab\CommonBundle\Entity\ServiceLog $serviceLog)
    {
        $this->service_logs->removeElement($serviceLog);
    }

    /**
     * Get serviceLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServiceLogs()
    {
        return $this->service_logs;
    }

    /**
     * Add operatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $operatorEnvelope
     *
     * @return User
     */
    public function addOperatorEnvelope(\KreaLab\CommonBundle\Entity\Envelope $operatorEnvelope)
    {
        $this->operator_envelopes[] = $operatorEnvelope;

        return $this;
    }

    /**
     * Remove operatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $operatorEnvelope
     */
    public function removeOperatorEnvelope(\KreaLab\CommonBundle\Entity\Envelope $operatorEnvelope)
    {
        $this->operator_envelopes->removeElement($operatorEnvelope);
    }

    /**
     * Get operatorEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorEnvelopes()
    {
        return $this->operator_envelopes;
    }

    /**
     * Add courierEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $courierEnvelope
     *
     * @return User
     */
    public function addCourierEnvelope(\KreaLab\CommonBundle\Entity\Envelope $courierEnvelope)
    {
        $this->courier_envelopes[] = $courierEnvelope;

        return $this;
    }

    /**
     * Remove courierEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $courierEnvelope
     */
    public function removeCourierEnvelope(\KreaLab\CommonBundle\Entity\Envelope $courierEnvelope)
    {
        $this->courier_envelopes->removeElement($courierEnvelope);
    }

    /**
     * Get courierEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCourierEnvelopes()
    {
        return $this->courier_envelopes;
    }

    /**
     * Add courierSgl
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorGettingLog $courierSgl
     *
     * @return User
     */
    public function addCourierSgl(\KreaLab\CommonBundle\Entity\SupervisorGettingLog $courierSgl)
    {
        $this->courier_sgls[] = $courierSgl;

        return $this;
    }

    /**
     * Remove courierSgl
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorGettingLog $courierSgl
     */
    public function removeCourierSgl(\KreaLab\CommonBundle\Entity\SupervisorGettingLog $courierSgl)
    {
        $this->courier_sgls->removeElement($courierSgl);
    }

    /**
     * Get courierSgls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCourierSgls()
    {
        return $this->courier_sgls;
    }

    /**
     * Add supervisorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $supervisorEnvelope
     *
     * @return User
     */
    public function addSupervisorEnvelope(\KreaLab\CommonBundle\Entity\Envelope $supervisorEnvelope)
    {
        $this->supervisor_envelopes[] = $supervisorEnvelope;

        return $this;
    }

    /**
     * Remove supervisorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\Envelope $supervisorEnvelope
     */
    public function removeSupervisorEnvelope(\KreaLab\CommonBundle\Entity\Envelope $supervisorEnvelope)
    {
        $this->supervisor_envelopes->removeElement($supervisorEnvelope);
    }

    /**
     * Get supervisorEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupervisorEnvelopes()
    {
        return $this->supervisor_envelopes;
    }

    /**
     * Add supervisorSgl
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorGettingLog $supervisorSgl
     *
     * @return User
     */
    public function addSupervisorSgl(\KreaLab\CommonBundle\Entity\SupervisorGettingLog $supervisorSgl)
    {
        $this->supervisor_sgls[] = $supervisorSgl;

        return $this;
    }

    /**
     * Remove supervisorSgl
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorGettingLog $supervisorSgl
     */
    public function removeSupervisorSgl(\KreaLab\CommonBundle\Entity\SupervisorGettingLog $supervisorSgl)
    {
        $this->supervisor_sgls->removeElement($supervisorSgl);
    }

    /**
     * Get supervisorSgls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupervisorSgls()
    {
        return $this->supervisor_sgls;
    }

    /**
     * Add supervisorCgl
     *
     * @param \KreaLab\CommonBundle\Entity\CashierGettingLog $supervisorCgl
     *
     * @return User
     */
    public function addSupervisorCgl(\KreaLab\CommonBundle\Entity\CashierGettingLog $supervisorCgl)
    {
        $this->supervisor_cgls[] = $supervisorCgl;

        return $this;
    }

    /**
     * Remove supervisorCgl
     *
     * @param \KreaLab\CommonBundle\Entity\CashierGettingLog $supervisorCgl
     */
    public function removeSupervisorCgl(\KreaLab\CommonBundle\Entity\CashierGettingLog $supervisorCgl)
    {
        $this->supervisor_cgls->removeElement($supervisorCgl);
    }

    /**
     * Get supervisorCgls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupervisorCgls()
    {
        return $this->supervisor_cgls;
    }

    /**
     * Add cashierCgl
     *
     * @param \KreaLab\CommonBundle\Entity\CashierGettingLog $cashierCgl
     *
     * @return User
     */
    public function addCashierCgl(\KreaLab\CommonBundle\Entity\CashierGettingLog $cashierCgl)
    {
        $this->cashier_cgls[] = $cashierCgl;

        return $this;
    }

    /**
     * Remove cashierCgl
     *
     * @param \KreaLab\CommonBundle\Entity\CashierGettingLog $cashierCgl
     */
    public function removeCashierCgl(\KreaLab\CommonBundle\Entity\CashierGettingLog $cashierCgl)
    {
        $this->cashier_cgls->removeElement($cashierCgl);
    }

    /**
     * Get cashierCgls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCashierCgls()
    {
        return $this->cashier_cgls;
    }

    /**
     * Add acquittancemanOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $acquittancemanOrder
     *
     * @return User
     */
    public function addAcquittancemanOrder(\KreaLab\CommonBundle\Entity\Order $acquittancemanOrder)
    {
        $this->acquittanceman_orders[] = $acquittancemanOrder;

        return $this;
    }

    /**
     * Remove acquittancemanOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $acquittancemanOrder
     */
    public function removeAcquittancemanOrder(\KreaLab\CommonBundle\Entity\Order $acquittancemanOrder)
    {
        $this->acquittanceman_orders->removeElement($acquittancemanOrder);
    }

    /**
     * Get acquittancemanOrders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAcquittancemanOrders()
    {
        return $this->acquittanceman_orders;
    }

    /**
     * Add operatorOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $operatorOrder
     *
     * @return User
     */
    public function addOperatorOrder(\KreaLab\CommonBundle\Entity\Order $operatorOrder)
    {
        $this->operator_orders[] = $operatorOrder;

        return $this;
    }

    /**
     * Remove operatorOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $operatorOrder
     */
    public function removeOperatorOrder(\KreaLab\CommonBundle\Entity\Order $operatorOrder)
    {
        $this->operator_orders->removeElement($operatorOrder);
    }

    /**
     * Get operatorOrders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorOrders()
    {
        return $this->operator_orders;
    }

    /**
     * Add treasurerOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $treasurerOrder
     *
     * @return User
     */
    public function addTreasurerOrder(\KreaLab\CommonBundle\Entity\Order $treasurerOrder)
    {
        $this->treasurer_orders[] = $treasurerOrder;

        return $this;
    }

    /**
     * Remove treasurerOrder
     *
     * @param \KreaLab\CommonBundle\Entity\Order $treasurerOrder
     */
    public function removeTreasurerOrder(\KreaLab\CommonBundle\Entity\Order $treasurerOrder)
    {
        $this->treasurer_orders->removeElement($treasurerOrder);
    }

    /**
     * Get treasurerOrders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTreasurerOrders()
    {
        return $this->treasurer_orders;
    }

    /**
     * Add supervisorRepayment
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment
     *
     * @return User
     */
    public function addSupervisorRepayment(\KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment)
    {
        $this->supervisor_repayments[] = $supervisorRepayment;

        return $this;
    }

    /**
     * Remove supervisorRepayment
     *
     * @param \KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment
     */
    public function removeSupervisorRepayment(\KreaLab\CommonBundle\Entity\SupervisorRepayment $supervisorRepayment)
    {
        $this->supervisor_repayments->removeElement($supervisorRepayment);
    }

    /**
     * Get supervisorRepayments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupervisorRepayments()
    {
        return $this->supervisor_repayments;
    }

    /**
     * Add consumableOrderman
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumableOrderman
     *
     * @return User
     */
    public function addConsumableOrderman(\KreaLab\CommonBundle\Entity\Consumable $consumableOrderman)
    {
        $this->consumable_ordermans[] = $consumableOrderman;

        return $this;
    }

    /**
     * Remove consumableOrderman
     *
     * @param \KreaLab\CommonBundle\Entity\Consumable $consumableOrderman
     */
    public function removeConsumableOrderman(\KreaLab\CommonBundle\Entity\Consumable $consumableOrderman)
    {
        $this->consumable_ordermans->removeElement($consumableOrderman);
    }

    /**
     * Get consumableOrdermans
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsumableOrdermans()
    {
        return $this->consumable_ordermans;
    }

    /**
     * Add stockmanBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $stockmanBlank
     *
     * @return User
     */
    public function addStockmanBlank(\KreaLab\CommonBundle\Entity\Blank $stockmanBlank)
    {
        $this->stockman_blanks[] = $stockmanBlank;

        return $this;
    }

    /**
     * Remove stockmanBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $stockmanBlank
     */
    public function removeStockmanBlank(\KreaLab\CommonBundle\Entity\Blank $stockmanBlank)
    {
        $this->stockman_blanks->removeElement($stockmanBlank);
    }

    /**
     * Get stockmanBlanks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStockmanBlanks()
    {
        return $this->stockman_blanks;
    }

    /**
     * Add operatorBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $operatorBlank
     *
     * @return User
     */
    public function addOperatorBlank(\KreaLab\CommonBundle\Entity\Blank $operatorBlank)
    {
        $this->operator_blanks[] = $operatorBlank;

        return $this;
    }

    /**
     * Remove operatorBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $operatorBlank
     */
    public function removeOperatorBlank(\KreaLab\CommonBundle\Entity\Blank $operatorBlank)
    {
        $this->operator_blanks->removeElement($operatorBlank);
    }

    /**
     * Get operatorBlanks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorBlanks()
    {
        return $this->operator_blanks;
    }

    /**
     * Add referencemanBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $referencemanBlank
     *
     * @return User
     */
    public function addReferencemanBlank(\KreaLab\CommonBundle\Entity\Blank $referencemanBlank)
    {
        $this->referenceman_blanks[] = $referencemanBlank;

        return $this;
    }

    /**
     * Remove referencemanBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $referencemanBlank
     */
    public function removeReferencemanBlank(\KreaLab\CommonBundle\Entity\Blank $referencemanBlank)
    {
        $this->referenceman_blanks->removeElement($referencemanBlank);
    }

    /**
     * Get referencemanBlanks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanBlanks()
    {
        return $this->referenceman_blanks;
    }

    /**
     * Add oldReferencemanBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $oldReferencemanBlank
     *
     * @return User
     */
    public function addOldReferencemanBlank(\KreaLab\CommonBundle\Entity\Blank $oldReferencemanBlank)
    {
        $this->old_referenceman_blanks[] = $oldReferencemanBlank;

        return $this;
    }

    /**
     * Remove oldReferencemanBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $oldReferencemanBlank
     */
    public function removeOldReferencemanBlank(\KreaLab\CommonBundle\Entity\Blank $oldReferencemanBlank)
    {
        $this->old_referenceman_blanks->removeElement($oldReferencemanBlank);
    }

    /**
     * Get oldReferencemanBlanks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOldReferencemanBlanks()
    {
        return $this->old_referenceman_blanks;
    }

    /**
     * Add operatorEnvelopeOperatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeOperatorEnvelope
     *
     * @return User
     */
    public function addOperatorEnvelopeOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeOperatorEnvelope)
    {
        $this->operator_envelope__operator_envelopes[] = $operatorEnvelopeOperatorEnvelope;

        return $this;
    }

    /**
     * Remove operatorEnvelopeOperatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeOperatorEnvelope
     */
    public function removeOperatorEnvelopeOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeOperatorEnvelope)
    {
        $this->operator_envelope__operator_envelopes->removeElement($operatorEnvelopeOperatorEnvelope);
    }

    /**
     * Get operatorEnvelopeOperatorEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorEnvelopeOperatorEnvelopes()
    {
        return $this->operator_envelope__operator_envelopes;
    }

    /**
     * Add operatorEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeReferencemanEnvelope
     *
     * @return User
     */
    public function addOperatorEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeReferencemanEnvelope)
    {
        $this->operator_envelope__referenceman_envelopes[] = $operatorEnvelopeReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove operatorEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeReferencemanEnvelope
     */
    public function removeOperatorEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $operatorEnvelopeReferencemanEnvelope)
    {
        $this->operator_envelope__referenceman_envelopes->removeElement($operatorEnvelopeReferencemanEnvelope);
    }

    /**
     * Get operatorEnvelopeReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorEnvelopeReferencemanEnvelopes()
    {
        return $this->operator_envelope__referenceman_envelopes;
    }

    /**
     * Add operatorReferencemanEnvelopeOperatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeOperatorEnvelope
     *
     * @return User
     */
    public function addOperatorReferencemanEnvelopeOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeOperatorEnvelope)
    {
        $this->operator_referenceman_envelope__operator_envelopes[] = $operatorReferencemanEnvelopeOperatorEnvelope;

        return $this;
    }

    /**
     * Remove operatorReferencemanEnvelopeOperatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeOperatorEnvelope
     */
    public function removeOperatorReferencemanEnvelopeOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeOperatorEnvelope)
    {
        $this->operator_referenceman_envelope__operator_envelopes->removeElement($operatorReferencemanEnvelopeOperatorEnvelope);
    }

    /**
     * Get operatorReferencemanEnvelopeOperatorEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorReferencemanEnvelopeOperatorEnvelopes()
    {
        return $this->operator_referenceman_envelope__operator_envelopes;
    }

    /**
     * Add operatorReferencemanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeReferencemanEnvelope
     *
     * @return User
     */
    public function addOperatorReferencemanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeReferencemanEnvelope)
    {
        $this->operator_referenceman_envelope__referenceman_envelopes[] = $operatorReferencemanEnvelopeReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove operatorReferencemanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeReferencemanEnvelope
     */
    public function removeOperatorReferencemanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelopeReferencemanEnvelope)
    {
        $this->operator_referenceman_envelope__referenceman_envelopes->removeElement($operatorReferencemanEnvelopeReferencemanEnvelope);
    }

    /**
     * Get operatorReferencemanEnvelopeReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorReferencemanEnvelopeReferencemanEnvelopes()
    {
        return $this->operator_referenceman_envelope__referenceman_envelopes;
    }

    /**
     * Add referencemanReferencemanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeReferencemanEnvelope
     *
     * @return User
     */
    public function addReferencemanReferencemanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelope__referenceman_envelopes[] = $referencemanReferencemanEnvelopeReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanReferencemanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeReferencemanEnvelope
     */
    public function removeReferencemanReferencemanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelope__referenceman_envelopes->removeElement($referencemanReferencemanEnvelopeReferencemanEnvelope);
    }

    /**
     * Get referencemanReferencemanEnvelopeReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanReferencemanEnvelopeReferencemanEnvelopes()
    {
        return $this->referenceman_referenceman_envelope__referenceman_envelopes;
    }

    /**
     * Add referencemanReferencemanEnvelopeOldReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeOldReferencemanEnvelope
     *
     * @return User
     */
    public function addReferencemanReferencemanEnvelopeOldReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeOldReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelope__old_referenceman_envelopes[] = $referencemanReferencemanEnvelopeOldReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanReferencemanEnvelopeOldReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeOldReferencemanEnvelope
     */
    public function removeReferencemanReferencemanEnvelopeOldReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelopeOldReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelope__old_referenceman_envelopes->removeElement($referencemanReferencemanEnvelopeOldReferencemanEnvelope);
    }

    /**
     * Get referencemanReferencemanEnvelopeOldReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanReferencemanEnvelopeOldReferencemanEnvelopes()
    {
        return $this->referenceman_referenceman_envelope__old_referenceman_envelopes;
    }

    /**
     * Add referencemanEnvelopeStockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeStockmanEnvelope
     *
     * @return User
     */
    public function addReferencemanEnvelopeStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeStockmanEnvelope)
    {
        $this->referenceman_envelope__stockman_envelopes[] = $referencemanEnvelopeStockmanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanEnvelopeStockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeStockmanEnvelope
     */
    public function removeReferencemanEnvelopeStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeStockmanEnvelope)
    {
        $this->referenceman_envelope__stockman_envelopes->removeElement($referencemanEnvelopeStockmanEnvelope);
    }

    /**
     * Get referencemanEnvelopeStockmanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanEnvelopeStockmanEnvelopes()
    {
        return $this->referenceman_envelope__stockman_envelopes;
    }

    /**
     * Add referencemanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeReferencemanEnvelope
     *
     * @return User
     */
    public function addReferencemanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeReferencemanEnvelope)
    {
        $this->referenceman_envelope__referenceman_envelopes[] = $referencemanEnvelopeReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeReferencemanEnvelope
     */
    public function removeReferencemanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $referencemanEnvelopeReferencemanEnvelope)
    {
        $this->referenceman_envelope__referenceman_envelopes->removeElement($referencemanEnvelopeReferencemanEnvelope);
    }

    /**
     * Get referencemanEnvelopeReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanEnvelopeReferencemanEnvelopes()
    {
        return $this->referenceman_envelope__referenceman_envelopes;
    }

    /**
     * Add stockmanEnvelopeStockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeStockmanEnvelope
     *
     * @return User
     */
    public function addStockmanEnvelopeStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeStockmanEnvelope)
    {
        $this->stockman_envelope__stockman_envelopes[] = $stockmanEnvelopeStockmanEnvelope;

        return $this;
    }

    /**
     * Remove stockmanEnvelopeStockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeStockmanEnvelope
     */
    public function removeStockmanEnvelopeStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeStockmanEnvelope)
    {
        $this->stockman_envelope__stockman_envelopes->removeElement($stockmanEnvelopeStockmanEnvelope);
    }

    /**
     * Get stockmanEnvelopeStockmanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStockmanEnvelopeStockmanEnvelopes()
    {
        return $this->stockman_envelope__stockman_envelopes;
    }

    /**
     * Add stockmanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeReferencemanEnvelope
     *
     * @return User
     */
    public function addStockmanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeReferencemanEnvelope)
    {
        $this->stockman_envelope__referenceman_envelopes[] = $stockmanEnvelopeReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove stockmanEnvelopeReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeReferencemanEnvelope
     */
    public function removeStockmanEnvelopeReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $stockmanEnvelopeReferencemanEnvelope)
    {
        $this->stockman_envelope__referenceman_envelopes->removeElement($stockmanEnvelopeReferencemanEnvelope);
    }

    /**
     * Get stockmanEnvelopeReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStockmanEnvelopeReferencemanEnvelopes()
    {
        return $this->stockman_envelope__referenceman_envelopes;
    }

    /**
     * Add blankLog
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLog $blankLog
     *
     * @return User
     */
    public function addBlankLog(\KreaLab\CommonBundle\Entity\BlankLog $blankLog)
    {
        $this->blank_logs[] = $blankLog;

        return $this;
    }

    /**
     * Remove blankLog
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLog $blankLog
     */
    public function removeBlankLog(\KreaLab\CommonBundle\Entity\BlankLog $blankLog)
    {
        $this->blank_logs->removeElement($blankLog);
    }

    /**
     * Get blankLogs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlankLogs()
    {
        return $this->blank_logs;
    }

    /**
     * Add lifeLogsStartUser
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsStartUser
     *
     * @return User
     */
    public function addLifeLogsStartUser(\KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsStartUser)
    {
        $this->life_logs_start_user[] = $lifeLogsStartUser;

        return $this;
    }

    /**
     * Remove lifeLogsStartUser
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsStartUser
     */
    public function removeLifeLogsStartUser(\KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsStartUser)
    {
        $this->life_logs_start_user->removeElement($lifeLogsStartUser);
    }

    /**
     * Get lifeLogsStartUser
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLifeLogsStartUser()
    {
        return $this->life_logs_start_user;
    }

    /**
     * Add lifeLogsEndUser
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsEndUser
     *
     * @return User
     */
    public function addLifeLogsEndUser(\KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsEndUser)
    {
        $this->life_logs_end_user[] = $lifeLogsEndUser;

        return $this;
    }

    /**
     * Remove lifeLogsEndUser
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsEndUser
     */
    public function removeLifeLogsEndUser(\KreaLab\CommonBundle\Entity\BlankLifeLog $lifeLogsEndUser)
    {
        $this->life_logs_end_user->removeElement($lifeLogsEndUser);
    }

    /**
     * Get lifeLogsEndUser
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLifeLogsEndUser()
    {
        return $this->life_logs_end_user;
    }

    /**
     * Add adminPenaltyBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $adminPenaltyBlank
     *
     * @return User
     */
    public function addAdminPenaltyBlank(\KreaLab\CommonBundle\Entity\Blank $adminPenaltyBlank)
    {
        $this->admin_penalty_blanks[] = $adminPenaltyBlank;

        return $this;
    }

    /**
     * Remove adminPenaltyBlank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $adminPenaltyBlank
     */
    public function removeAdminPenaltyBlank(\KreaLab\CommonBundle\Entity\Blank $adminPenaltyBlank)
    {
        $this->admin_penalty_blanks->removeElement($adminPenaltyBlank);
    }

    /**
     * Get adminPenaltyBlanks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdminPenaltyBlanks()
    {
        return $this->admin_penalty_blanks;
    }

    /**
     * Add referencemanArchiveBoxOperator
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxOperator
     *
     * @return User
     */
    public function addReferencemanArchiveBoxOperator(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxOperator)
    {
        $this->referenceman_archive_box_operators[] = $referencemanArchiveBoxOperator;

        return $this;
    }

    /**
     * Remove referencemanArchiveBoxOperator
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxOperator
     */
    public function removeReferencemanArchiveBoxOperator(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxOperator)
    {
        $this->referenceman_archive_box_operators->removeElement($referencemanArchiveBoxOperator);
    }

    /**
     * Get referencemanArchiveBoxOperators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanArchiveBoxOperators()
    {
        return $this->referenceman_archive_box_operators;
    }

    /**
     * Add referencemanArchiveBoxReferenceman
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxReferenceman
     *
     * @return User
     */
    public function addReferencemanArchiveBoxReferenceman(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxReferenceman)
    {
        $this->referenceman_archive_box_referencemen[] = $referencemanArchiveBoxReferenceman;

        return $this;
    }

    /**
     * Remove referencemanArchiveBoxReferenceman
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxReferenceman
     */
    public function removeReferencemanArchiveBoxReferenceman(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBoxReferenceman)
    {
        $this->referenceman_archive_box_referencemen->removeElement($referencemanArchiveBoxReferenceman);
    }

    /**
     * Get referencemanArchiveBoxReferencemen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanArchiveBoxReferencemen()
    {
        return $this->referenceman_archive_box_referencemen;
    }

    /**
     * Add ordermanConsumableBoxOrderman
     *
     * @param \KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxOrderman
     *
     * @return User
     */
    public function addOrdermanConsumableBoxOrderman(\KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxOrderman)
    {
        $this->orderman_consumable_box_ordermen[] = $ordermanConsumableBoxOrderman;

        return $this;
    }

    /**
     * Remove ordermanConsumableBoxOrderman
     *
     * @param \KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxOrderman
     */
    public function removeOrdermanConsumableBoxOrderman(\KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxOrderman)
    {
        $this->orderman_consumable_box_ordermen->removeElement($ordermanConsumableBoxOrderman);
    }

    /**
     * Get ordermanConsumableBoxOrdermen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrdermanConsumableBoxOrdermen()
    {
        return $this->orderman_consumable_box_ordermen;
    }

    /**
     * Add ordermanConsumableBoxRacquittanceman
     *
     * @param \KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxRacquittanceman
     *
     * @return User
     */
    public function addOrdermanConsumableBoxRacquittanceman(\KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxRacquittanceman)
    {
        $this->orderman_consumable_box_racquittancemen[] = $ordermanConsumableBoxRacquittanceman;

        return $this;
    }

    /**
     * Remove ordermanConsumableBoxRacquittanceman
     *
     * @param \KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxRacquittanceman
     */
    public function removeOrdermanConsumableBoxRacquittanceman(\KreaLab\CommonBundle\Entity\OrdermanConsumableBox $ordermanConsumableBoxRacquittanceman)
    {
        $this->orderman_consumable_box_racquittancemen->removeElement($ordermanConsumableBoxRacquittanceman);
    }

    /**
     * Get ordermanConsumableBoxRacquittancemen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrdermanConsumableBoxRacquittancemen()
    {
        return $this->orderman_consumable_box_racquittancemen;
    }

    /**
     * Add operatorReplacementLogPredecessor
     *
     * @param \KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogPredecessor
     *
     * @return User
     */
    public function addOperatorReplacementLogPredecessor(\KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogPredecessor)
    {
        $this->operator_replacement_log__predecessors[] = $operatorReplacementLogPredecessor;

        return $this;
    }

    /**
     * Remove operatorReplacementLogPredecessor
     *
     * @param \KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogPredecessor
     */
    public function removeOperatorReplacementLogPredecessor(\KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogPredecessor)
    {
        $this->operator_replacement_log__predecessors->removeElement($operatorReplacementLogPredecessor);
    }

    /**
     * Get operatorReplacementLogPredecessors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorReplacementLogPredecessors()
    {
        return $this->operator_replacement_log__predecessors;
    }

    /**
     * Add operatorReplacementLogSuccessor
     *
     * @param \KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogSuccessor
     *
     * @return User
     */
    public function addOperatorReplacementLogSuccessor(\KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogSuccessor)
    {
        $this->operator_replacement_log__successors[] = $operatorReplacementLogSuccessor;

        return $this;
    }

    /**
     * Remove operatorReplacementLogSuccessor
     *
     * @param \KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogSuccessor
     */
    public function removeOperatorReplacementLogSuccessor(\KreaLab\CommonBundle\Entity\OperatorReplacementLog $operatorReplacementLogSuccessor)
    {
        $this->operator_replacement_log__successors->removeElement($operatorReplacementLogSuccessor);
    }

    /**
     * Get operatorReplacementLogSuccessors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorReplacementLogSuccessors()
    {
        return $this->operator_replacement_log__successors;
    }

    /**
     * Add operatorSchedule
     *
     * @param \KreaLab\CommonBundle\Entity\OperatorSchedule $operatorSchedule
     *
     * @return User
     */
    public function addOperatorSchedule(\KreaLab\CommonBundle\Entity\OperatorSchedule $operatorSchedule)
    {
        $this->operator_schedules[] = $operatorSchedule;

        return $this;
    }

    /**
     * Remove operatorSchedule
     *
     * @param \KreaLab\CommonBundle\Entity\OperatorSchedule $operatorSchedule
     */
    public function removeOperatorSchedule(\KreaLab\CommonBundle\Entity\OperatorSchedule $operatorSchedule)
    {
        $this->operator_schedules->removeElement($operatorSchedule);
    }

    /**
     * Get operatorSchedules
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorSchedules()
    {
        return $this->operator_schedules;
    }

    /**
     * Set workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return User
     */
    public function setWorkplace(\KreaLab\CommonBundle\Entity\Workplace $workplace = null)
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

    /**
     * Add filial
     *
     * @param \KreaLab\CommonBundle\Entity\Filial $filial
     *
     * @return User
     */
    public function addFilial(\KreaLab\CommonBundle\Entity\Filial $filial)
    {
        $this->filials[] = $filial;

        return $this;
    }

    /**
     * Remove filial
     *
     * @param \KreaLab\CommonBundle\Entity\Filial $filial
     */
    public function removeFilial(\KreaLab\CommonBundle\Entity\Filial $filial)
    {
        $this->filials->removeElement($filial);
    }

    /**
     * Get filials
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFilials()
    {
        return $this->filials;
    }
}

