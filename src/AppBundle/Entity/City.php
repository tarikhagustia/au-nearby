<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="postcodes_geo")
 */
class City
{

  /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
       * @ORM\Column(type="string")
       */
    private $latitude;

    /**
       * @ORM\Column(type="string")
       */
    private $longitude;
    /**
      * @ORM\Column(type="string", length=100)
      */
    private $name;

    /**
      * @ORM\Column(type="string", length=100)
      */
    private $postcode;

    public function getName()
    {
      return $this->name;
    }

    public function getid()
    {
      return $this->id;
    }

    public function getLatitude()
    {
      return $this->latitude;
    }

    public function getLongitude()
    {
      return $this->longitude;
    }

    public function getPostcode()
    {
      return $this->postcode;
    }
}
