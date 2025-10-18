<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OAA;

#[ORM\Table(name: "categories")]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[OA\Schema(
    schema: 'Category',
    title: 'Category',
    description: 'Modèle de catégorie',
    required: ['name'],
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id', 
            type: 'integer', 
            description: 'Identifiant unique de la catégorie',
            example: 1
        ),
        new OA\Property(
            property: 'name', 
            type: 'string', 
            description: 'Nom de la catégorie',
            maxLength: 50,
            example: 'Action'
        ),
        new OA\Property(
            property: 'videoGames',
            type: 'array',
            description: 'Liste des jeux vidéo de cette catégorie',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Super Mario Bros')
                ]
            )
        )
    ]
)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["category", "video_game"])]
    #[OA\Property(
        description: 'Identifiant unique de la catégorie',
        type: 'integer',
        format: 'int64',
        example: 1
    )]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["category", "video_game"])]
    #[Assert\NotBlank(
        message: "Le champ nom ne doit pas être vide."
    )]
    #[Assert\Length(
        min:1,
        max:50,
        minMessage: 'Le champ nom ne doit pas être inférieur à {{ limit }} caractères',
        maxMessage: 'Le champ nom ne doit pas dépasser {{ limit }} caractères',
    )]
    #[OA\Property(
        description: 'Nom de la catégorie',
        type: 'string',
        maxLength: 50,
        example: 'Action'
    )]
    private string $name;

    /**
     * @var Collection<int, VideoGame>
     */
    #[ORM\ManyToMany(targetEntity: VideoGame::class, mappedBy: 'categories')]
    #[Groups(["category"])]
    #[MaxDepth(1)]
    #[OA\Property(
        description: 'Liste des jeux vidéo dans cette catégorie',
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/VideoGameSimple')
    )]
    private Collection $videoGames;

    public function __construct()
    {
        $this->videoGames = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, VideoGame>
     */
    public function getVideoGames(): Collection
    {
        return $this->videoGames;
    }

    public function addVideoGame(VideoGame $videoGame): static
    {
        if (!$this->videoGames->contains($videoGame)) {
            $this->videoGames->add($videoGame);
            $videoGame->addCategory($this);
        }

        return $this;
    }

    public function removeVideoGame(VideoGame $videoGame): static
    {
        if ($this->videoGames->removeElement($videoGame)) {
            $videoGame->removeCategory($this);
        }

        return $this;
    }
}
