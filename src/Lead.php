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
     * @var string
     */
    private $customerEmail;
    /**
     * @var string
     */
    private $customerPhone;
    /**
     * Any serializable data
     * @var array
     */
    private $customData;
    /**
     * @var string One of lead types pre-configured slugs on Sculptor
     */
    private $typeSlug;

    /**
     * @param string $customerFullname
     * @param string $customerPhone
     * @param string $customerEmail
     * @param string $customerCityName
     * @param int $customerCityLocalId
     * @param string $typeSlug
     * @param array $customData
     */
    function __construct(
        $customerFullname,
        $customerPhone,
        $customerEmail,
        $customerCityName = null,
        $customerCityLocalId = null,
        $typeSlug = 'default',
        array $customData = []
    )
    {
        $this->customerFullname = $customerFullname;
        $this->customerCityName = $customerCityName;
        $this->customerCityLocalId = $customerCityLocalId;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
        $this->typeSlug = $typeSlug;
        $this->customData = $customData;
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
    public function getTypeSlug()
    {
        return $this->typeSlug;
    }

    /**
     * @return array
     */
    public function getCustomData()
    {
        return $this->customData;
    }
}
