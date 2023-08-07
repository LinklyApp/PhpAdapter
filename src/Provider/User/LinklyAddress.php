<?php


namespace Linkly\OAuth2\Client\Provider\User;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class LinklyAddress
{
    use ArrayAccessorTrait;

    private $address;

    private $country;

    /**
     * Address constructor.
     * @param array $address
     */
    public function __construct(array $address = array())
    {
        $this->address = $address;

        $this->country = new LinklyCountry($this->getValueByKey($this->address, 'country'));
    }

    public function getId()
    {
        return $this->getValueByKey($this->address, 'id');
    }

    public function getVersion()
    {
        return $this->getValueByKey($this->address, 'version');
    }

    public function getFirstName()
    {
        return $this->getValueByKey($this->address, 'firstName');
    }

    public function getFamilyNameInfix()
    {
        return $this->getValueByKey($this->address, 'familyNameInfix');
    }

    public function getFamilyName()
    {
        return $this->getValueByKey($this->address, 'familyName');
    }

    public function getFamilyNameWithInfix()
    {
        $infix = '';

        if ($this->getValueByKey($this->address, 'familyNameInfix')) {
            $infix .= $this->getValueByKey($this->address, 'familyNameInfix') . ' ';
        }

        return $infix . $this->getValueByKey($this->address, 'familyName');
    }

    public function getFullName()
    {
        $fullName = $this->getValueByKey($this->address, 'firstName');
        $fullName .= ' ' . $this->getFamilyNameWithInfix();

        return $fullName;
    }

    public function getCompanyName()
    {
        return $this->getValueByKey($this->address, 'companyName');
    }

    public function getPhoneNumber()
    {
        return $this->getValueByKey($this->address, 'phoneNumber');
    }

    public function getStreetName()
    {
        return $this->getValueByKey($this->address, 'street');
    }

    public function getHouseNumberWithoutSuffix()
    {
        return $this->getValueByKey($this->address, 'houseNumber');
    }

    public function getHouseNumberSuffix()
    {
        return $this->getValueByKey($this->address, 'houseNumberSuffix');
    }

    public function getHouseNumberWithSuffix()
    {
        $houseNumberWithSuffix = $this->getHouseNumberWithoutSuffix();

        if ($houseNumberSuffix = $this->getHouseNumberSuffix()) {
            $houseNumberContainsNumbers = !!preg_match('~[0-9]~', $houseNumberSuffix);
            if ($houseNumberContainsNumbers) {
                $houseNumberWithSuffix .= '-';
            }
            $houseNumberWithSuffix .= $houseNumberSuffix;
        }

        return $houseNumberWithSuffix;
    }

    public function getStreetAddress()
    {
        return $this->getStreetName() . ' ' . $this->getHouseNumberWithSuffix();
    }

    public function getPostcode()
    {
        return $this->getValueByKey($this->address, 'postcode');
    }

    public function getPoBox()
    {
        return $this->getValueByKey($this->address, 'poBox');
    }

    public function getCity()
    {
        return $this->getValueByKey($this->address, 'city');
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function toArray()
    {
        return $this->address;
    }
}
