<?php

namespace App\Entity;

use App\Repository\VideoGameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "video_games")]
#[ORM\Entity(repositoryClass: VideoGameRepository::class)]
#[OA\Schema(
    schema: 'VideoGame',
    title: 'VideoGame',
    description: 'Modèle de jeu vidéo',
    required: ['title', 'releaseDate', 'editor', 'categories'],
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id', 
            type: 'integer', 
            description: 'Identifiant unique du jeu vidéo',
            example: 1
        ),
        new OA\Property(
            property: 'title', 
            type: 'string', 
            description: 'Titre du jeu vidéo',
            maxLength: 50,
            example: 'Super Mario Bros'
        ),
        new OA\Property(
            property: 'releaseDate',
            type: 'string',
            format: 'date-time',
            description: 'Date de sortie du jeu',
            example: '2023-10-20T00:00:00+00:00'
        ),
        new OA\Property(
            property: 'editor',
            type: 'object',
            description: 'Éditeur du jeu',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                new OA\Property(property: 'country', type: 'string', example: 'Japon')
            ]
        ),
        new OA\Property(
            property: 'categories',
            type: 'array',
            description: 'Catégories du jeu',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Action')
                ]
            )
        )
    ]
)]
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

    #[ORM\ManyToOne(targetEntity: Editor::class, inversedBy: 'videoGames', cascade: ['persist'])]
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
