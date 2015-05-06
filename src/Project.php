<?php
namespace velosipedist\SculptorClient;
class Project
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $guid;

    /**
     * @param string $name
     * @param string $guid
     */
    public function __construct($name, $guid)
    {

        $this->name = $name;
        $this->guid = $guid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }
}
