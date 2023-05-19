<?php

namespace App\Entity;


use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Abstracts\AbstractTag;
use App\Repository\ActualityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ActualityRepository::class)
 */
class Actuality
{
    // TODO use DateReferense

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:article", "read:list"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups({"read:article", "read:list"})
     * @Assert\Length(
     *    max = 255,
     *    maxMessage="Le titre peut faire au maximum 255 caractères."
     * )
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read:article"})
     * @Assert\Length(
     *    max = 50000,
     *    maxMessage="La description peut faire au maximum 20000 caractères."
     * )
     * @CustomAssert\HtmlTagConstraint(
     *    message="Le contenu de cette description n'est pas acceptable pour des contraintes de sécurité, car il contient les termes suivants : {{ banTags }}."
     * )
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read:article"})
     */
    protected $parsedDescription;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"read:article", "read:list"})
     * @Assert\Length(
     *    max = 150,
     *    maxMessage="La description peut faire au maximum 150 caractères."
     * )
     */
    protected $shortDescription;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"read:article", "read:list"})
     */
    protected $images = [];

    /**
     * @ORM\ManyToMany(targetEntity=ActualityTag::class)
     * @Groups({"read:article", "read:list", "read:list:article", "update:article", "insert:article"})
     */
    private $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return Collection|ActualityTag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * 
     * @param ActualityTag $tag 
     * @return Actuality 
     */
    public function addTag(AbstractTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getParsedDescription(): ?string
    {
        return $this->parsedDescription;
    }

    public function setParsedDescription(?string $parsedDescription): self
    {
        $this->parsedDescription = $parsedDescription;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;

        return $this;
    }
    
    /**
     * 
     * @param ActualityTag $tag 
     * @return Actuality 
     */
    public function removeTag(ActualityTag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }
}
