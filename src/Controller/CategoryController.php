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

final class CategoryController extends AbstractController
{
    private CategoryRepository $categoryRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/api/category', name: 'app_category_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser les catégories')]
    public function getCategories(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $categoryList = $this->categoryRepository->getAllWithPagination($page, $limit);

        return $this->json($categoryList, Response::HTTP_OK, [], ['groups' => 'category']);
    }

    #[Route('/api/category/{id}', name: 'app_category_by_id', methods:['GET'], requirements :['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser les catégories')]
    public function getCategory(Category $category, CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category']);
    }

    #[Route('/api/category/{id}/delete', name:'app_category_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à supprimer des catégories')]
    public function deleteCategory(Category $category): JsonResponse
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/category/{id}/update', name:'app_category_update', methods:['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à modifier des catégories')]
    public function updateCategory(Request $request, Category $currentCategory): JsonResponse
    {
        $updatedCategory = $this->serializer->deserialize($request->getContent(),
            Category::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategory]
        );
        
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
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à créer des catégories')]
    public function createCategory(Request $request): JsonResponse
    {
        $newCategory = $this->serializer->deserialize($request->getContent(),
            Category::class,
            'json',
        );
        
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
