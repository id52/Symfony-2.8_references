<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\User as UserModel;
use KreaLab\CommonBundle\Util\BlankIntervals;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends UserModel implements UserInterface, \Serializable
{
    protected $active                 = true;
    protected $ips                    = [];
    protected $force_change_pass      = false;
    protected $intervals              = [];
    protected $referenceman_intervals = [];

    public function __toString()
    {
        return $this->getShortName();
    }

    public function getShortName()
    {
        return $this->getLastName()
            .' '.mb_substr($this->getFirstName(), 0, 1, 'utf8').'.'
            .' '.mb_substr($this->getPatronymic(), 0, 1, 'utf8').'.'
        ;
    }

    public function getFullName()
    {
        return $this->getLastName().' '.$this->getFirstName().' '.$this->getPatronymic();
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->roles,
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->roles,
        ) = unserialize($serialized);
    }

    public function getWorkplacesByIp($ip)
    {
        $workplaces = [];

        $all_workplaces = [];
        if ($this->isOperator()) {
            $all_workplaces[] = $this->getWorkplace();
        }

        foreach ($all_workplaces as $workplace) { /** @var $workplace \KreaLab\CommonBundle\Entity\Workplace */
            if ($workplace && $workplace->getActive()) {
                $filial = $workplace->getFilial();
                if ($filial->getActive() && in_array($ip, $filial->getIps())) {
                    $workplaces[] = $workplace;
                }
            }
        }

        return $workplaces;
    }

    public function getIps()
    {
        if (!$this->isOperator()) {
            return $this->ips;
        }

        $ips = [];
        $workplace = $this->getWorkplace();
        if ($workplace && $workplace->getActive()) {
            $filial = $workplace->getFilial();
            if ($filial->getActive()) {
                $ips = array_merge($ips, $filial->getIps());
            }
        }
        return $ips;
    }

    public function hasRole($role)
    {
        return $this->roles && (false !== array_search($role, $this->roles));
    }

    public function hasOnlyRole($role)
    {
        return count($this->roles) == 1 && $this->hasRole($role);
    }

    public function isOperator()
    {
        return $this->hasOnlyRole('ROLE_OPERATOR');
    }

    public function hasOneOfRoles($roles)
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        if (!is_array($roles)) {
            return false;
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isEditable(User $user)
    {
        if ($this->hasOneOfRoles([
                'ROLE_SUPERADMIN',
                'ROLE_ADMIN',
                'ROLE_ARCHIVARIUS',
            ]) && !$user->hasRole('ROLE_SUPERADMIN')
        ) {
            return false;
        }

        if ($this->hasOneOfRoles([
                'ROLE_MANAGE_FILIALS',
                'ROLE_MANAGE_WORKERS',
                'ROLE_CASHIER',
                'ROLE_SUPERVISOR',
            ])
            && !$user->hasOneOfRoles(['ROLE_SUPERADMIN', 'ROLE_ADMIN'])
        ) {
            return false;
        }

        return true;
    }

    public function getUserId()
    {
        return $this->id;
    }

    public function getOrdermanNameWithSum()
    {
        return $this->getFullName().' ('.$this->getOrdermanSum().' руб.)';
    }

    /**
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public function addInterval($legalEntity, $referenceType, $serie, $start, $count = 1, $leadingZeros = 0)
    {
        if (!$this->intervals) {
            $this->intervals = [];
        }

        BlankIntervals::add($this->intervals, $legalEntity, $referenceType, $serie, $start, $count, $leadingZeros);
    }

    /**
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public function removeInterval($legalEntity, $referenceType, $serie, $start, $count = 1, $leadingZeros = 0)
    {
        if (!$this->intervals) {
            $this->intervals = [];
        }

        BlankIntervals::remove($this->intervals, $legalEntity, $referenceType, $serie, $start, $count, $leadingZeros);
    }

    /**
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public function addReferencemanInterval($legalEntity, $referenceType, $serie, $start, $count = 1, $leadingZeros = 0)
    {
        if (!$this->referenceman_intervals) {
            $this->referenceman_intervals = [];
        }

        BlankIntervals::add($this->referenceman_intervals, $legalEntity, $referenceType, $serie, $start, $count, $leadingZeros);
    }

    /**
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public function removeReferencemanInterval($legalEntity, $referenceType, $serie, $start, $count = 1, $leadingZeros = 0)
    {
        if (!$this->referenceman_intervals) {
            $this->referenceman_intervals = [];
        }

        BlankIntervals::remove($this->referenceman_intervals, $legalEntity, $referenceType, $serie, $start, $count, $leadingZeros);
    }
}
