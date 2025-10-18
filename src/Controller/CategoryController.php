<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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

#[OA\Tag(name: 'Catégories')]
final class CategoryController extends AbstractController
{
    private CategoryRepository $categoryRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;
    private ValidatorInterface $validator;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
        $this->validator = $validator;
    }

    #[Route('/api/category', name: 'app_category_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'êtes pas autorisé à visualiser les catégories')]
    #[OA\Get(
        path: '/api/category',
        summary: 'Liste toutes les catégories',
        description: 'Récupère la liste des catégories avec pagination',
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
                description: 'Liste des catégories',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Action'),
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
    public function getCategories(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $categoryList = $this->categoryRepository->getAllWithPagination($page, $limit);

        return $this->json($categoryList, Response::HTTP_OK, [], ['groups' => 'category']);
    }

    #[Route('/api/category/{id}', name: 'app_category_by_id', methods:['GET'], requirements :['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'êtes pas autorisé à visualiser les catégories')]
    #[OA\Get(
        path: '/api/category/{id}',
        summary: 'Récupère une catégorie par son ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de la catégorie',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails de la catégorie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Action'),
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
                description: 'Catégorie non trouvée'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function getCategory(Category $category, CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category']);
    }

    #[Route('/api/category/{id}/delete', name:'app_category_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à supprimer des catégories')]
    #[OA\Delete(
        path: '/api/category/{id}/delete',
        summary: 'Supprime une catégorie',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de la catégorie à supprimer',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Catégorie supprimée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Catégorie non trouvée'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function deleteCategory(Category $category): JsonResponse
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/category/{id}/update', name:'app_category_update', methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à modifier des catégories')]
    #[OA\Put(
        path: '/api/category/{id}/update',
        summary: 'Modifie une catégorie existante',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de la catégorie à modifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Action')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Catégorie modifiée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                ),
                headers: [
                    new OA\Header(
                        header: 'Location',
                        description: 'URL de la catégorie modifiée',
                        schema: new OA\Schema(type: 'string')
                    )
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'Catégorie non trouvée'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides'
            )
        ]
    )]
    public function updateCategory(Request $request, Category $currentCategory): JsonResponse
    {
        $updatedCategory = $this->serializer->deserialize($request->getContent(),
            Category::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategory]
        );

        $errors = $this->validator->validate($updatedCategory);

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
        
        $this->entityManager->persist($updatedCategory);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_category_by_id',
            ['id' => $updatedCategory->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_OK, ['Location' => $location], ['groups' => 'category']);
    }

    #[Route('api/category/new', name:'app_category_create', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas autorisé à créer des catégories')]
    #[OA\Post(
        path: '/api/category/new',
        summary: 'Crée une nouvelle catégorie',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Action')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Catégorie créée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                ),
                headers: [
                    new OA\Header(
                        header: 'Location',
                        description: 'URL de la nouvelle catégorie',
                        schema: new OA\Schema(type: 'string')
                    )
                ]
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides'
            )
        ]
    )]
    public function createCategory(Request $request): JsonResponse
    {
        $newCategory = $this->serializer->deserialize($request->getContent(),
            Category::class,
            'json',
        );

        $errors = $this->validator->validate($newCategory);

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
        
        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_category_by_id',
            ['id' => $newCategory->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_CREATED, ['Location' => $location], ['groups' => 'category']);
    }
}
