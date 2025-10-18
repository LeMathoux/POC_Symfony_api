<?php

namespace App\Controller;

use App\Entity\Editor;
use App\Repository\EditorRepository;
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

#[OA\Tag(name: 'Editors')]
final class EditorController extends AbstractController
{
    private EditorRepository $editorRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;
    private ValidatorInterface $validator;

    public function __construct(EditorRepository $editorRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator)
    {
        $this->editorRepository = $editorRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
        $this->validator = $validator;
    }

    #[Route('/api/editor', name: 'app_editor_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'êtes pas autorisé à visualiser des éditeurs')]
    #[OA\Get(
        path: '/api/editor',
        summary: 'Liste tous les éditeurs',
        description: 'Récupère la liste des éditeurs avec pagination',
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
                description: 'Liste des éditeurs',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                            new OA\Property(property: 'country', type: 'string', example: 'Japon'),
                            new OA\Property(
                                property: 'videoGames',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer'),
                                        new OA\Property(property: 'title', type: 'string')
                                    ]
                                )
                            )
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
    public function getEditors(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $editorList = $this->editorRepository->getAllWithPagination($page, $limit);

        return $this->json($editorList, Response::HTTP_OK, [], ['groups' => 'editor']);
    }

    #[Route('/api/editor/{id}', name: 'app_editor_by_id', methods:['GET'], requirements:['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'êtes pas autorisé à visualiser des éditeurs')]
    #[OA\Get(
        path: '/api/editor/{id}',
        summary: 'Détails d\'un éditeur',
        description: 'Récupère les détails d\'un éditeur spécifique',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'éditeur',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails de l\'éditeur',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                        new OA\Property(property: 'country', type: 'string', example: 'Japon'),
                        new OA\Property(
                            property: 'videoGames',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'title', type: 'string')
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Éditeur non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function getEditorById(Editor $editor, EditorRepository $editorRepository): JsonResponse
    {
        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor']);
    }

    #[Route('/api/editor/{id}/delete', name:'app_editor_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à supprimer des éditeurs')]
    #[OA\Delete(
        path: '/api/editor/{id}/delete',
        summary: 'Supprimer un éditeur',
        description: 'Supprime un éditeur existant',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'éditeur à supprimer',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Éditeur supprimé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Éditeur non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function deleteEditor(Editor $editor): JsonResponse
    {
        $this->entityManager->remove($editor);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/editor/{id}/update', name:'app_editor_update', methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à modifier des éditeurs')]
    #[OA\Put(
        path: '/api/editor/{id}/update',
        summary: 'Mettre à jour un éditeur',
        description: 'Met à jour les informations d\'un éditeur existant',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'éditeur à modifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                    new OA\Property(property: 'country', type: 'string', example: 'Japon')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Éditeur mis à jour avec succès',
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
                description: 'Éditeur non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function updateEditor(Request $request, Editor $currentEditor): JsonResponse
    {
        $updatedEditor = $this->serializer->deserialize($request->getContent(),
            Editor::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEditor]
        );

        $errors = $this->validator->validate($updatedEditor);

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
        
        $this->entityManager->persist($updatedEditor);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_editor_by_id',
            ['id' => $updatedEditor->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('api/editor/new', name:'app_editor_create', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à créer des éditeurs')]
    #[OA\Post(
        path: '/api/editor/new',
        summary: 'Créer un nouvel éditeur',
        description: 'Crée un nouvel éditeur avec les données fournies',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nintendo'),
                    new OA\Property(property: 'country', type: 'string', example: 'Japon')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Éditeur créé avec succès',
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
    public function createEditor(Request $request): JsonResponse
    {
        $newEditor = $this->serializer->deserialize($request->getContent(),
            Editor::class,
            'json',
        );

        $errors = $this->validator->validate($newEditor);

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
        
        $this->entityManager->persist($newEditor);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_editor_by_id',
            ['id' => $newEditor->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_OK, ['Location' => $location]);
    }
}
