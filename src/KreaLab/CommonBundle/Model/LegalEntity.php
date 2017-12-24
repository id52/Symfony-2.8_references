<?php

namespace KreaLab\CommonBundle\Model;

/**
 * LegalEntity
 */
abstract class LegalEntity
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
    protected $short_name;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $checking_account;

    /**
     * @var string
     */
    protected $bank_name;

    /**
     * @var string
     */
    protected $correspondent_account;

    /**
     * @var string
     */
    protected $bik;

    /**
     * @var string
     */
    protected $inn;

    /**
     * @var string
     */
    protected $ogrn;

    /**
     * @var string
     */
    protected $kpp;

    /**
     * @var string
     */
    protected $person;

    /**
     * @var string
     */
    protected $person_genitive;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blanks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $cashboxes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $workplaces;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $brigades;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_stockman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $operator_referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_operator_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $stockman_referenceman_envelopes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $blank_logs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $referenceman_archive_boxes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $guarantors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $executors;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->blanks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cashboxes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->workplaces = new \Doctrine\Common\Collections\ArrayCollection();
        $this->brigades = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_stockman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->operator_referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_operator_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stockman_referenceman_envelopes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blank_logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referenceman_archive_boxes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->guarantors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->executors = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return LegalEntity
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
     * @return LegalEntity
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
     * Set shortName
     *
     * @param string $shortName
     *
     * @return LegalEntity
     */
    public function setShortName($shortName)
    {
        $this->short_name = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->short_name;
    }

    /**
     * Set license
     *
     * @param string $license
     *
     * @return LegalEntity
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * Get license
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return LegalEntity
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
     * Set checkingAccount
     *
     * @param string $checkingAccount
     *
     * @return LegalEntity
     */
    public function setCheckingAccount($checkingAccount)
    {
        $this->checking_account = $checkingAccount;

        return $this;
    }

    /**
     * Get checkingAccount
     *
     * @return string
     */
    public function getCheckingAccount()
    {
        return $this->checking_account;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     *
     * @return LegalEntity
     */
    public function setBankName($bankName)
    {
        $this->bank_name = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bank_name;
    }

    /**
     * Set correspondentAccount
     *
     * @param string $correspondentAccount
     *
     * @return LegalEntity
     */
    public function setCorrespondentAccount($correspondentAccount)
    {
        $this->correspondent_account = $correspondentAccount;

        return $this;
    }

    /**
     * Get correspondentAccount
     *
     * @return string
     */
    public function getCorrespondentAccount()
    {
        return $this->correspondent_account;
    }

    /**
     * Set bik
     *
     * @param string $bik
     *
     * @return LegalEntity
     */
    public function setBik($bik)
    {
        $this->bik = $bik;

        return $this;
    }

    /**
     * Get bik
     *
     * @return string
     */
    public function getBik()
    {
        return $this->bik;
    }

    /**
     * Set inn
     *
     * @param string $inn
     *
     * @return LegalEntity
     */
    public function setInn($inn)
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * Get inn
     *
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * Set ogrn
     *
     * @param string $ogrn
     *
     * @return LegalEntity
     */
    public function setOgrn($ogrn)
    {
        $this->ogrn = $ogrn;

        return $this;
    }

    /**
     * Get ogrn
     *
     * @return string
     */
    public function getOgrn()
    {
        return $this->ogrn;
    }

    /**
     * Set kpp
     *
     * @param string $kpp
     *
     * @return LegalEntity
     */
    public function setKpp($kpp)
    {
        $this->kpp = $kpp;

        return $this;
    }

    /**
     * Get kpp
     *
     * @return string
     */
    public function getKpp()
    {
        return $this->kpp;
    }

    /**
     * Set person
     *
     * @param string $person
     *
     * @return LegalEntity
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return string
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set personGenitive
     *
     * @param string $personGenitive
     *
     * @return LegalEntity
     */
    public function setPersonGenitive($personGenitive)
    {
        $this->person_genitive = $personGenitive;

        return $this;
    }

    /**
     * Get personGenitive
     *
     * @return string
     */
    public function getPersonGenitive()
    {
        return $this->person_genitive;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return LegalEntity
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
     * Add blank
     *
     * @param \KreaLab\CommonBundle\Entity\Blank $blank
     *
     * @return LegalEntity
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

    /**
     * Add cashbox
     *
     * @param \KreaLab\CommonBundle\Entity\Cashbox $cashbox
     *
     * @return LegalEntity
     */
    public function addCashbox(\KreaLab\CommonBundle\Entity\Cashbox $cashbox)
    {
        $this->cashboxes[] = $cashbox;

        return $this;
    }

    /**
     * Remove cashbox
     *
     * @param \KreaLab\CommonBundle\Entity\Cashbox $cashbox
     */
    public function removeCashbox(\KreaLab\CommonBundle\Entity\Cashbox $cashbox)
    {
        $this->cashboxes->removeElement($cashbox);
    }

    /**
     * Get cashboxes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCashboxes()
    {
        return $this->cashboxes;
    }

    /**
     * Add workplace
     *
     * @param \KreaLab\CommonBundle\Entity\Workplace $workplace
     *
     * @return LegalEntity
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
     * Add brigade
     *
     * @param \KreaLab\CommonBundle\Entity\Brigade $brigade
     *
     * @return LegalEntity
     */
    public function addBrigade(\KreaLab\CommonBundle\Entity\Brigade $brigade)
    {
        $this->brigades[] = $brigade;

        return $this;
    }

    /**
     * Remove brigade
     *
     * @param \KreaLab\CommonBundle\Entity\Brigade $brigade
     */
    public function removeBrigade(\KreaLab\CommonBundle\Entity\Brigade $brigade)
    {
        $this->brigades->removeElement($brigade);
    }

    /**
     * Get brigades
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBrigades()
    {
        return $this->brigades;
    }

    /**
     * Add referencemanStockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $referencemanStockmanEnvelope
     *
     * @return LegalEntity
     */
    public function addReferencemanStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $referencemanStockmanEnvelope)
    {
        $this->referenceman_stockman_envelopes[] = $referencemanStockmanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanStockmanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $referencemanStockmanEnvelope
     */
    public function removeReferencemanStockmanEnvelope(\KreaLab\CommonBundle\Entity\BlankStockmanEnvelope $referencemanStockmanEnvelope)
    {
        $this->referenceman_stockman_envelopes->removeElement($referencemanStockmanEnvelope);
    }

    /**
     * Get referencemanStockmanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanStockmanEnvelopes()
    {
        return $this->referenceman_stockman_envelopes;
    }

    /**
     * Add operatorReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope
     *
     * @return LegalEntity
     */
    public function addOperatorReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope)
    {
        $this->operator_referenceman_envelopes[] = $operatorReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove operatorReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope
     */
    public function removeOperatorReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope $operatorReferencemanEnvelope)
    {
        $this->operator_referenceman_envelopes->removeElement($operatorReferencemanEnvelope);
    }

    /**
     * Get operatorReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatorReferencemanEnvelopes()
    {
        return $this->operator_referenceman_envelopes;
    }

    /**
     * Add referencemanReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope
     *
     * @return LegalEntity
     */
    public function addReferencemanReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelopes[] = $referencemanReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove referencemanReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope
     */
    public function removeReferencemanReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope $referencemanReferencemanEnvelope)
    {
        $this->referenceman_referenceman_envelopes->removeElement($referencemanReferencemanEnvelope);
    }

    /**
     * Get referencemanReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanReferencemanEnvelopes()
    {
        return $this->referenceman_referenceman_envelopes;
    }

    /**
     * Add referencemanOperatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $referencemanOperatorEnvelope
     *
     * @return LegalEntity
     */
    public function addReferencemanOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $referencemanOperatorEnvelope)
    {
        $this->referenceman_operator_envelopes[] = $referencemanOperatorEnvelope;

        return $this;
    }

    /**
     * Remove referencemanOperatorEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $referencemanOperatorEnvelope
     */
    public function removeReferencemanOperatorEnvelope(\KreaLab\CommonBundle\Entity\BlankOperatorEnvelope $referencemanOperatorEnvelope)
    {
        $this->referenceman_operator_envelopes->removeElement($referencemanOperatorEnvelope);
    }

    /**
     * Get referencemanOperatorEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanOperatorEnvelopes()
    {
        return $this->referenceman_operator_envelopes;
    }

    /**
     * Add stockmanReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $stockmanReferencemanEnvelope
     *
     * @return LegalEntity
     */
    public function addStockmanReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $stockmanReferencemanEnvelope)
    {
        $this->stockman_referenceman_envelopes[] = $stockmanReferencemanEnvelope;

        return $this;
    }

    /**
     * Remove stockmanReferencemanEnvelope
     *
     * @param \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $stockmanReferencemanEnvelope
     */
    public function removeStockmanReferencemanEnvelope(\KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope $stockmanReferencemanEnvelope)
    {
        $this->stockman_referenceman_envelopes->removeElement($stockmanReferencemanEnvelope);
    }

    /**
     * Get stockmanReferencemanEnvelopes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStockmanReferencemanEnvelopes()
    {
        return $this->stockman_referenceman_envelopes;
    }

    /**
     * Add blankLog
     *
     * @param \KreaLab\CommonBundle\Entity\BlankLog $blankLog
     *
     * @return LegalEntity
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
     * Add referencemanArchiveBox
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox
     *
     * @return LegalEntity
     */
    public function addReferencemanArchiveBox(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox)
    {
        $this->referenceman_archive_boxes[] = $referencemanArchiveBox;

        return $this;
    }

    /**
     * Remove referencemanArchiveBox
     *
     * @param \KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox
     */
    public function removeReferencemanArchiveBox(\KreaLab\CommonBundle\Entity\ReferencemanArchiveBox $referencemanArchiveBox)
    {
        $this->referenceman_archive_boxes->removeElement($referencemanArchiveBox);
    }

    /**
     * Get referencemanArchiveBoxes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencemanArchiveBoxes()
    {
        return $this->referenceman_archive_boxes;
    }

    /**
     * Add guarantor
     *
     * @param \KreaLab\CommonBundle\Entity\Agreement $guarantor
     *
     * @return LegalEntity
     */
    public function addGuarantor(\KreaLab\CommonBundle\Entity\Agreement $guarantor)
    {
        $this->guarantors[] = $guarantor;

        return $this;
    }

    /**
     * Remove guarantor
     *
     * @param \KreaLab\CommonBundle\Entity\Agreement $guarantor
     */
    public function removeGuarantor(\KreaLab\CommonBundle\Entity\Agreement $guarantor)
    {
        $this->guarantors->removeElement($guarantor);
    }

    /**
     * Get guarantors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGuarantors()
    {
        return $this->guarantors;
    }

    /**
     * Add executor
     *
     * @param \KreaLab\CommonBundle\Entity\Agreement $executor
     *
     * @return LegalEntity
     */
    public function addExecutor(\KreaLab\CommonBundle\Entity\Agreement $executor)
    {
        $this->executors[] = $executor;

        return $this;
    }

    /**
     * Remove executor
     *
     * @param \KreaLab\CommonBundle\Entity\Agreement $executor
     */
    public function removeExecutor(\KreaLab\CommonBundle\Entity\Agreement $executor)
    {
        $this->executors->removeElement($executor);
    }

    /**
     * Get executors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExecutors()
    {
        return $this->executors;
    }
}

