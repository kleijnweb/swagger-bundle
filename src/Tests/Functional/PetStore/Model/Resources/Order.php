<?php
declare(strict_types = 1);
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
     * @var \DateTimeImmutable
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
     * @return Order
     */
    public function setId(int $id): Order
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param integer
     *
     * @return Order
     */
    public function setPetid(int $petId): Order
    {
        $this->petId = $petId;

        return $this;
    }

    /**
     * @param integer
     *
     * @return Order
     */
    public function setQuantity(int $quantity): Order
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @param \DateTimeImmutable
     *
     * @return Order
     */
    public function setShipdate(\DateTimeImmutable $shipDate): Order
    {
        $this->shipDate = $shipDate;

        return $this;
    }

    /**
     * @param string
     *
     * @return Order
     */
    public function setStatus(string $status): Order
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param boolean
     *
     * @return Order
     */
    public function setComplete(bool $complete): Order
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getPetid(): int
    {
        return $this->petId;
    }

    /**
     * @return integer
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return \DateTimeImmutable
     */

    public function getShipdate(): \DateTimeImmutable
    {
        return $this->shipDate;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return boolean
     */
    public function getComplete(): bool
    {
        return $this->complete;
    }
}
