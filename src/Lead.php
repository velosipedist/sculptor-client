<?php
namespace velosipedist\SculptorClient;
/**
 * Successful lead data container to be sent with Sculptor API client.
 */
class Lead
{
    /**
     * @var string
     */
    private $customerFullname;
    /**
     * @var string
     */
    private $customerCityName;
    /**
     * @var int
     */
    private $customerCityLocalId;
    /**
     * @var int Id of city in geonames.org database
     */
    private $customerCityGeonamesId;
    /**
     * @var string
     */
    private $customerEmail;
    /**
     * @var string
     */
    private $customerPhone;
    /**
     * Any serializable data
     * @var mixed
     */
    private $customData;
    /**
     * @var string One of lead types pre-configured slugs on Sculptor
     */
    private $leadType;

    /**
     * @param string $customerFullname
     * @param string $customerPhone
     * @param string $customerEmail
     * @param string $customerCityName
     * @param int $customerCityLocalId
     * @param int $customerCityGeoId
     * @param null $leadType
     * @param mixed $customData
     */
    function __construct(
        $customerFullname,
        $customerPhone,
        $customerEmail,
        $customerCityName = null,
        $customerCityLocalId = null,
        $customerCityGeoId = null,
        $leadType = null,
        $customData = null
    )
    {
        $this->customerFullname = $customerFullname;
        $this->customerCityName = $customerCityName;
        $this->customerCityLocalId = $customerCityLocalId;
        $this->customerCityGeonamesId = $customerCityGeoId;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
        $this->customData = $customData;
        $this->leadType = $leadType;
    }


    /**
     * @return string
     */
    public function getCustomerFullname()
    {
        return $this->customerFullname;
    }

    /**
     * @return null|string
     */
    public function getCustomerCityName()
    {
        return $this->customerCityName;
    }

    /**
     * @return int|null
     */
    public function getCustomerCityLocalId()
    {
        return $this->customerCityLocalId;
    }

    /**
     * @return int|null
     */
    public function getCustomerCityGeonamesId()
    {
        return $this->customerCityGeonamesId;
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    /**
     * @return string
     */
    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    /**
     * @return string
     */
    public function getLeadType()
    {
        return $this->leadType;
    }
}
