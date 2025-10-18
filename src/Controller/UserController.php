<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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

#[OA\Tag(name: 'Users')]
final class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;
    private ValidatorInterface $validator;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
        $this->validator = $validator;
    }

    #[Route('/api/user', name: 'app_user_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des utilisateurs')]
    #[OA\Get(
        path: '/api/user',
        summary: 'Liste tous les utilisateurs',
        description: 'Récupère la liste des utilisateurs avec pagination',
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
                description: 'Liste des utilisateurs',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                            new OA\Property(
                                property: 'roles',
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                                example: ['ROLE_USER']
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
    public function getUsers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $userList = $this->userRepository->getAllWithPagination($page, $limit);

        return $this->json($userList, Response::HTTP_OK, [], ['groups' => 'user']);
    }

    #[Route('/api/user/{id}', name: 'app_user_by_id', methods:['GET'], requirements:['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des utilisateurs')]
    #[OA\Get(
        path: '/api/user/{id}',
        summary: 'Détails d\'un utilisateur',
        description: 'Récupère les détails d\'un utilisateur spécifique',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'utilisateur',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails de l\'utilisateur',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_USER']
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function getUserById(User $user, UserRepository $userRepository): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user']);
    }

    #[Route('/api/user/{id}/delete', name:'app_user_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à supprimer des utilisateurs')]
    #[OA\Delete(
        path: '/api/user/{id}/delete',
        summary: 'Supprimer un utilisateur',
        description: 'Supprime un utilisateur existant',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'utilisateur à supprimer',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur supprimé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function deleteUser(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/user/{id}/update', name:'app_user_update', methods:['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à modifier des utilisateurs')]
    #[OA\Put(
        path: '/api/user/{id}/update',
        summary: 'Mettre à jour un utilisateur',
        description: 'Met à jour les informations d\'un utilisateur existant',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID de l\'utilisateur à modifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 64, example: 'password123'),
                    new OA\Property(
                        property: 'roles',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['ROLE_USER']
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur mis à jour avec succès',
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
                description: 'Utilisateur non trouvé'
            ),
            new OA\Response(
                response: 401,
                description: 'Non autorisé'
            )
        ]
    )]
    public function updateUser(Request $request, User $currentUser): JsonResponse
    {
        $updatedUser = $this->serializer->deserialize($request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );

        $errors = $this->validator->validate($updatedUser);

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
        
        $this->entityManager->persist($updatedUser);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_user_by_id',
            ['id' => $updatedUser->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('api/user/new', name:'app_user_create', methods:['POST'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à créer des utilisateurs')]
    #[OA\Post(
        path: '/api/user/new',
        summary: 'Créer un nouvel utilisateur',
        description: 'Crée un nouvel utilisateur avec les données fournies',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 64, example: 'password123'),
                    new OA\Property(
                        property: 'roles',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['ROLE_USER']
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utilisateur créé avec succès',
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
    public function createUser(Request $request): JsonResponse
    {
        $newUser = $this->serializer->deserialize($request->getContent(),
            User::class,
            'json',
        );

        $errors = $this->validator->validate($newUser);

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
        
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_user_by_id',
            ['id' => $newUser->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_OK, ['Location' => $location]);
    }
}
