<?php declare(strict_types=1);

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Resources;

class Tag
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Tag constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }


    /**
     * @param int $id
     *
     * @return Tag
     */
    public function setId(int $id): Tag
    {
        $this->id = $id;

        return $this;
    }
}
