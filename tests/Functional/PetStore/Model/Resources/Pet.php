<?php declare(strict_types = 1);

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Resources;

class Pet
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
     * @var string[]
     */
    private $photoUrls;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Tag[]
     */
    private $tags = [];

    /**
     * Pet constructor.
     *
     * @param string   $name
     * @param string[] $photoUrls
     * @param Category $category
     * @param Tag[]    $tags
     */
    public function __construct(string $name, array $photoUrls, Category $category, array $tags)
    {
        $this->name      = $name;
        $this->photoUrls = $photoUrls;
        $this->category  = $category;
        $this->tags      = $tags;
    }

    /**
     * @param int $id
     *
     * @return Pet
     */
    public function setId(int $id): Pet
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
