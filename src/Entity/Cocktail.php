<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CocktailRepository")
 */
class Cocktail
{
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $alcoholic;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $thumbnail;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $forPeople;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $ingredients;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $measures;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $units;


    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAlcoholic(): ?bool
    {
        return $this->alcoholic;
    }

    public function setAlcoholic(?bool $alcoholic): self
    {
        $this->alcoholic = $alcoholic;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getForPeople(): ?int
    {
        return $this->forPeople;
    }

    public function setForPeople(?int $forPeople): self
    {
        $this->forPeople = $forPeople;

        return $this;
    }

    public function getIngredients(): ?array
    {
        return $this->ingredients;
    }

    public function setIngredients(?array $ingredients): self
    {
        $this->ingredients = $ingredients;

        return $this;
    }

    public function getMeasures(): ?array
    {
        return $this->measures;
    }

    public function setMeasures(?array $measures): self
    {
        $this->measures = $measures;

        return $this;
    }

    public function getUnits(): ?array
    {
        return $this->units;
    }

    public function setUnits(?array $units): self
    {
        $this->units = $units;

        return $this;
    }

    public function __construct(array $datas)
    {
        $this->hydrate($datas);
    }

    public function hydrate(array $datas)
    {
        foreach ($datas as $data => $value) {
            $method = 'set' . ucfirst($data);
            $this->$method($value);
        }
    }

}
