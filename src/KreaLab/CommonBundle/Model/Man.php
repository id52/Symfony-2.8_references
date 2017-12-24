<?php

namespace KreaLab\CommonBundle\Model;

/**
 * Man
 */
abstract class Man
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $first_name;

    /**
     * @var string
     */
    protected $last_name;

    /**
     * @var string
     */
    protected $patronymic;

    /**
     * @var string
     */
    protected $first_name_genitive;

    /**
     * @var string
     */
    protected $last_name_genitive;

    /**
     * @var string
     */
    protected $patronymic_genitive;

    /**
     * @var \KreaLab\CommonBundle\Entity\Brigade
     */
    protected $brigade;

    /**
     * @var \KreaLab\CommonBundle\Entity\Specialty
     */
    protected $specialty;


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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Man
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
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Man
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
     * Set patronymic
     *
     * @param string $patronymic
     *
     * @return Man
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
     * Set firstNameGenitive
     *
     * @param string $firstNameGenitive
     *
     * @return Man
     */
    public function setFirstNameGenitive($firstNameGenitive)
    {
        $this->first_name_genitive = $firstNameGenitive;

        return $this;
    }

    /**
     * Get firstNameGenitive
     *
     * @return string
     */
    public function getFirstNameGenitive()
    {
        return $this->first_name_genitive;
    }

    /**
     * Set lastNameGenitive
     *
     * @param string $lastNameGenitive
     *
     * @return Man
     */
    public function setLastNameGenitive($lastNameGenitive)
    {
        $this->last_name_genitive = $lastNameGenitive;

        return $this;
    }

    /**
     * Get lastNameGenitive
     *
     * @return string
     */
    public function getLastNameGenitive()
    {
        return $this->last_name_genitive;
    }

    /**
     * Set patronymicGenitive
     *
     * @param string $patronymicGenitive
     *
     * @return Man
     */
    public function setPatronymicGenitive($patronymicGenitive)
    {
        $this->patronymic_genitive = $patronymicGenitive;

        return $this;
    }

    /**
     * Get patronymicGenitive
     *
     * @return string
     */
    public function getPatronymicGenitive()
    {
        return $this->patronymic_genitive;
    }

    /**
     * Set brigade
     *
     * @param \KreaLab\CommonBundle\Entity\Brigade $brigade
     *
     * @return Man
     */
    public function setBrigade(\KreaLab\CommonBundle\Entity\Brigade $brigade = null)
    {
        $this->brigade = $brigade;

        return $this;
    }

    /**
     * Get brigade
     *
     * @return \KreaLab\CommonBundle\Entity\Brigade
     */
    public function getBrigade()
    {
        return $this->brigade;
    }

    /**
     * Set specialty
     *
     * @param \KreaLab\CommonBundle\Entity\Specialty $specialty
     *
     * @return Man
     */
    public function setSpecialty(\KreaLab\CommonBundle\Entity\Specialty $specialty = null)
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * Get specialty
     *
     * @return \KreaLab\CommonBundle\Entity\Specialty
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }
}

