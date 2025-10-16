<?php

namespace App\Entity;

use App\Repository\VideoGameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "video_games")]
#[ORM\Entity(repositoryClass: VideoGameRepository::class)]
class VideoGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["video_game", "editor", "category"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["video_game", "editor", "category"])]
    #[Assert\NotBlank(
        message: "Le champ titre ne doit pas être vide."
    )]
    #[Assert\Length(
        min:1,
        max:50,
        minMessage: 'Le champ titre ne doit pas être inférieur à {{ limit }} caractères',
        maxMessage: 'Le champ titre ne doit pas dépasser {{ limit }} caractères',
    )]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(["video_game", "editor", "category"])]
    #[Assert\NotBlank(
        message: "Le champ date de sortie ne doit pas être vide."
    )]
    private ?\DateTimeImmutable $releaseDate = null;

    #[ORM\ManyToOne(inversedBy: 'videoGames')]
    #[Groups(["video_game"])]
    #[MaxDepth(1)]
    #[Assert\NotBlank(
        message: "Le champ editeur ne doit pas être vide."
    )]
    private ?Editor $editor = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'videoGames')]
    #[Groups("video_game")]
    #[MaxDepth(1)]
    #[Assert\NotBlank(
        message: "Le champ catégorie ne doit pas être vide."
    )]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeImmutable $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getEditor(): ?Editor
    {
        return $this->editor;
    }

    public function setEditor(?Editor $editor): static
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
