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

final class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/api/user', name: 'app_user_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des utilisateurs')]
    public function getUsers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $userList = $this->userRepository->getAllWithPagination($page, $limit);

        return $this->json($userList, Response::HTTP_OK, [], ['groups' => 'user']);
    }

    #[Route('/api/user/{id}', name: 'app_user_by_id', methods:['GET'], requirements:['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des utilisateurs')]
    public function getUserById(User $user, UserRepository $userRepository): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user']);
    }

    #[Route('/api/user/{id}/delete', name:'app_user_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à supprimer des utilisateurs')]
    public function deleteUser(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/user/{id}/update', name:'app_user_update', methods:['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à modifier des utilisateurs')]
    public function updateUser(Request $request, User $currentUser): JsonResponse
    {
        $updatedUser = $this->serializer->deserialize($request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );
        
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
    public function createUser(Request $request): JsonResponse
    {
        $newUser = $this->serializer->deserialize($request->getContent(),
            User::class,
            'json',
        );
        
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
