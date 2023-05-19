<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractTag;
use App\Repository\ActualityTagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActualityTagRepository::class)
 */
class ActualityTag extends AbstractTag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read:list","read:list:team","read:list:article", "read:video","read:article", "read:team", "insert:team", "update:team"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=25)
     * @Groups({"read:list","read:list:team", "read:team","read:list:article","read:video","read:article"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"read:list","read:list:team", "read:team","read:list:article","read:video", "read:article"})
     */
    protected $shortName;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:list", "read:list:team", "read:team", "read:list:article", "read:video", "read:article"})
     */
    protected $sortOrder;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
