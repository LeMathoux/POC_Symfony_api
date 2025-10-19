<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Editor;
use App\Entity\VideoGame;
use App\Repository\VideoGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Video Games')]
final class VideoGameController extends AbstractController
{
    private VideoGameRepository $videoGameRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;
    private ValidatorInterface $validator;

    public function __construct(VideoGameRepository $videoGameRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator)
    {
        $this->videoGameRepository = $videoGameRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
        $this->validator = $validator;
    }

    #[Route('/api/video_game', name: 'app_video_game_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des jeux video')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\êtes pas autorisé à visualiser des jeux video')]
    #[OA\Get(
        path: '/api/video_game',
        summary: 'Liste tous les jeux vidéo',
        description: 'Récupère la liste des jeux vidéo avec pagination',
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Numéro de la page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Nombre d\'éléments par page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 5)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des jeux vidéo',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'title', type: 'string', example: 'Super Mario Bros'),
                            new OA\Property(property: 'releaseDate', type: 'string', format: 'date-time', example: '2023-10-20T00:00:00+00:00'),
                            new OA\Property(
                                property: 'editor',
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                                    new OA\Property(property: 'country', type: 'string', example: 'Japon')
                                ]
                            ),
                            new OA\Property(
                                property: 'categories',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Action')
                                    ]
                                )
                            ),
                            new OA\Property(property: 'coverImage', type: 'string', example: 'cover12345.jpg')
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function getVideoGames(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $videoGameList = $this->videoGameRepository->getAllWithPagination($page, $limit);

        return $this->json($videoGameList, Response::HTTP_OK, [], ['groups' => 'video_game:read']);
    }

    #[Route('/api/video_game/{id}', name: 'app_video_game_by_id', methods:['GET'], requirements:['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des jeux video')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\êtes pas autorisé à visualiser des jeux video')]
    #[OA\Get(
        path: '/api/video_game/{id}',
        summary: 'Détails d\'un jeu vidéo',
        description: 'Récupère les détails d\'un jeu vidéo spécifique',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du jeu vidéo',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails du jeu vidéo',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Super Mario Bros'),
                        new OA\Property(property: 'releaseDate', type: 'string', format: 'date-time', example: '2023-10-20T00:00:00+00:00'),
                        new OA\Property(
                            property: 'editor',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                                new OA\Property(property: 'country', type: 'string', example: 'Japon')
                            ]
                        ),
                        new OA\Property(
                            property: 'categories',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Action')
                                ]
                            )
                        ),
                        new OA\Property(property: 'coverImage', type: 'string', example: 'cover12345.jpg')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Jeu vidéo non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function getEditorById(VideoGame $videoGame, VideoGameRepository $editorRepository): JsonResponse
    {
        return $this->json($videoGame, Response::HTTP_OK, [], ['groups' => 'video_game:read']);
    }

    #[Route('/api/video_game/{id}/delete', name:'app_video_game_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à supprimer des jeux video')]
    #[OA\Delete(
        path: '/api/video_game/{id}/delete',
        summary: 'Supprimer un jeu vidéo',
        description: 'Supprime un jeu vidéo existant',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du jeu vidéo à supprimer',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Jeu vidéo supprimé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Jeu vidéo non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function deleteVideoGame(VideoGame $videoGame): JsonResponse
    {
        $this->entityManager->remove($videoGame);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/video_game/{id}/update', name:'app_video_game_update', methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à modifier des jeux video')]
    #[OA\Put(
        path: '/api/video_game/{id}/update',
        summary: 'Mettre à jour un jeu vidéo',
        description: 'Met à jour les informations d\'un jeu vidéo existant',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du jeu vidéo à modifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'releaseDate', 'editor', 'categories'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Super Mario Bros'),
                    new OA\Property(property: 'releaseDate', type: 'string', format: 'date-time', example: '2023-10-20T00:00:00+00:00'),
                    new OA\Property(property: 'editor', type: 'integer', description: 'ID de l\'éditeur', example: 1),
                    new OA\Property(
                        property: 'categories',
                        type: 'array',
                        items: new OA\Items(type: 'integer', description: 'ID des catégories'),
                        example: [1, 2]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Jeu vidéo mis à jour avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides'
            ),
            new OA\Response(
                response: 404,
                description: 'Jeu vidéo non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function updateVideoGame(Request $request, VideoGame $currentVideoGame): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $currentVideoGame->setTitle($data['title']);
        }

        if (isset($data['releaseDate'])) {
            $currentVideoGame->setReleaseDate(new \DateTimeImmutable($data['releaseDate']));
        }

        if (isset($data['editor'])) {
            $editor = $this->entityManager->getRepository(Editor::class)->find($data['editor']);
            if (!$editor) return $this->json(['error'=>'Éditeur introuvable'],400);
            $currentVideoGame->setEditor($editor);
        }

        if (!empty($data['categories']) && is_array($data['categories'])) {
            $currentVideoGame->clearCategories();
            foreach ($data['categories'] as $catId) {
                $category = $this->entityManager->getRepository(Category::class)->find($catId);
                if (!$category) return $this->json(['error'=>"Catégorie $catId introuvable"],400);
                $currentVideoGame->addCategory($category);
            }
        }

        $this->entityManager->flush();

        return $this->json(['status'=>'success']);
    }

    #[Route('api/video_game/new', name:'app_video_game_create', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à créer des jeux video')]
    #[OA\Post(
        path: '/api/video_game/new',
        summary: 'Créer un nouveau jeu vidéo',
        description: 'Crée un nouveau jeu vidéo avec les données fournies',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['title', 'releaseDate', 'editor', 'categories'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Super Mario Bros'),
                        new OA\Property(property: 'releaseDate', type: 'string', format: 'date-time', example: '2023-10-20T00:00:00+00:00'),
                        new OA\Property(property: 'editor', type: 'integer', description: 'ID de l\'éditeur', example: 1),
                        new OA\Property(
                            property: 'categories',
                            type: 'array',
                            items: new OA\Items(type: 'integer', description: 'ID des catégories'),
                            example: [1, 2]
                        ),
                        new OA\Property(
                            property: 'coverImage',
                            type: 'string',
                            format: 'binary',
                            description: 'Image de couverture du jeu vidéo'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Jeu vidéo créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function createVideoGame(Request $request): JsonResponse
    {
        $title = $request->request->get('title');
        $releaseDate = $request->request->get('releaseDate');
        $editorId = $request->request->get('editor');
        $categories = $request->request->get('categories');
        
        if (!is_array($categories)) {
            $categories = [];
        }

        $videoGame = new VideoGame();
        $videoGame->setTitle($title);
        $videoGame->setReleaseDate(new \DateTimeImmutable($releaseDate));

        $editor = $this->entityManager->getRepository(Editor::class)->find($editorId);
        if (!$editor) {
            return $this->json(['error' => 'Éditeur introuvable'], Response::HTTP_BAD_REQUEST);
        }
        $videoGame->setEditor($editor);

        foreach ($categories as $categoryId) {
        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            return $this->json(['error' => "Catégorie $categoryId introuvable"], Response::HTTP_BAD_REQUEST);
        }
        $videoGame->addCategory($category);
    }

        $coverImage = $request->files->get('coverImage');
        if ($coverImage) {
            $newFilename = uniqid().'.'.$coverImage->guessExtension();
            try {
                $coverImage->move(
                    $this->getParameter('covers_directory'),
                    $newFilename
                );
                $videoGame->setCoverImage($newFilename);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Impossible d\'enregistrer l\'image'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $errors = $this->validator->validate($videoGame);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json([
                'status' => 'error',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($videoGame);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_video_game_by_id',
            ['id' => $videoGame->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['status'=>'success'], Response::HTTP_CREATED, ['Location' => $location]);
    }
}
