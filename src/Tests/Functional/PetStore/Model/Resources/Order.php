<?php
namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Resources;

use JMS\Serializer\Annotation\Type;

/**
 * Generated resource DTO for 'Order'.
 */
class Order
{
    /**
     * @var integer
     * @Type("integer")
     */
    private $id;

    /**
     * @var integer
     * @Type("integer")
     */
    private $petId;

    /**
     * @var integer
     * @Type("integer")
     */
    private $quantity;

    /**
     * @var \DateTime
     * @Type("DateTime<'Y-m-d'>")
     */
    private $shipDate;

    /**
     * @var string
     * @Type("string")
     */
    private $status;

    /**
     * @var boolean
     * @Type("boolean")
     */
    private $complete;

    /**
     * @param integer
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param integer
     *
     * @return $this
     */
    public function setPetid($petId)
    {
        $this->petId = $petId;

        return $this;
    }

    /**
     * @param integer
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @param \DateTime
     *
     * @return $this
     */
    public function setShipdate($shipDate)
    {
        $this->shipDate = $shipDate;

        return $this;
    }

    /**
     * @param string
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param boolean
     *
     * @return $this
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getPetid()
    {
        return $this->petId;
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return \DateTime
     */

    public function getShipdate()
    {
        return $this->shipDate;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return boolean
     */
    public function getComplete()
    {
        return $this->complete;
    }
}
