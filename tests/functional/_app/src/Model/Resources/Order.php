<?php declare(strict_types=1);

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Resources;

class Order
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $petId;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var \DateTime
     */
    private $shipDate;

    /**
     * @var string
     */
    private $status;

    /**
     * @var bool
     */
    private $complete;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Order
     */
    public function setId(int $id): Order
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPetId(): int
    {
        return $this->petId;
    }

    /**
     * @param int $petId
     *
     * @return Order
     */
    public function setPetId(int $petId): Order
    {
        $this->petId = $petId;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Order
     */
    public function setQuantity(int $quantity): Order
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getShipDate(): \DateTime
    {
        return $this->shipDate;
    }

    /**
     * @param \DateTime $shipDate
     *
     * @return Order
     */
    public function setShipDate(\DateTime $shipDate): Order
    {
        $this->shipDate = $shipDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Order
     */
    public function setStatus(string $status): Order
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->complete;
    }

    /**
     * @param bool $complete
     *
     * @return Order
     */
    public function setComplete(bool $complete): Order
    {
        $this->complete = $complete;

        return $this;
    }
}
