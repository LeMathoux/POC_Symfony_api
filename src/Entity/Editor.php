<?php

namespace App\Entity;

use App\Repository\EditorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "editors")]
#[ORM\Entity(repositoryClass: EditorRepository::class)]
#[OA\Schema(
    schema: 'Editor',
    title: 'Editor',
    description: 'Modèle d\'éditeur de jeux vidéo',
    required: ['name', 'country'],
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id', 
            type: 'integer', 
            description: 'Identifiant unique de l\'éditeur',
            example: 1
        ),
        new OA\Property(
            property: 'name', 
            type: 'string', 
            description: 'Nom de l\'éditeur',
            maxLength: 50,
            example: 'Nintendo'
        ),
        new OA\Property(
            property: 'country', 
            type: 'string', 
            description: 'Pays d\'origine de l\'éditeur',
            example: 'Japon'
        ),
        new OA\Property(
            property: 'videoGames',
            type: 'array',
            description: 'Liste des jeux vidéo de cet éditeur',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Super Mario Bros')
                ]
            )
        )
    ]
)]
class Editor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["editor", "video_game"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["editor", "video_game"])]
    #[Assert\Length(
        min:1,
        max:50,
        minMessage: 'Le champ nom ne doit pas être inférieur à {{ limit }} caractères',
        maxMessage: 'Le champ nom ne doit pas dépasser {{ limit }} caractères',
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["editor", "video_game"])]
    #[Assert\NotBlank(
        message: "Le champ pays ne doit pas être vide."
    )]
    private ?string $country = null;

    /**
     * @var Collection<int, VideoGame>
     */
    #[ORM\OneToMany(targetEntity: VideoGame::class, mappedBy: 'editor')]
    #[Groups("editor")]
    #[MaxDepth(1)]
    private Collection $videoGames;

    public function __construct()
    {
        $this->videoGames = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

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
            $videoGame->setEditor($this);
        }

        return $this;
    }

    public function removeVideoGame(VideoGame $videoGame): static
    {
        if ($this->videoGames->removeElement($videoGame)) {
            // set the owning side to null (unless already changed)
            if ($videoGame->getEditor() === $this) {
                $videoGame->setEditor(null);
            }
        }

        return $this;
    }
}
