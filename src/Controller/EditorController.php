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

final class EditorController extends AbstractController
{
    private EditorRepository $editorRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;

    public function __construct(EditorRepository $editorRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->editorRepository = $editorRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/api/editor', name: 'app_editor_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des éditeurs')]
    public function getEditors(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $editorList = $this->editorRepository->getAllWithPagination($page, $limit);

        return $this->json($editorList, Response::HTTP_OK, [], ['groups' => 'editor']);
    }

    #[Route('/api/editor/{id}', name: 'app_editor_by_id', methods:['GET'], requirements:['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des éditeurs')]
    public function getEditorById(Editor $editor, EditorRepository $editorRepository): JsonResponse
    {
        return $this->json($editor, Response::HTTP_OK, [], ['groups' => 'editor']);
    }

    #[Route('/api/editor/{id}/delete', name:'app_editor_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à supprimer des éditeurs')]
    public function deleteEditor(Editor $editor): JsonResponse
    {
        $this->entityManager->remove($editor);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/editor/{id}/update', name:'app_editor_update', methods:['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à modifier des éditeurs')]
    public function updateEditor(Request $request, Editor $currentEditor): JsonResponse
    {
        $updatedEditor = $this->serializer->deserialize($request->getContent(),
            Editor::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEditor]
        );
        
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
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à créer des éditeurs')]
    public function createEditor(Request $request): JsonResponse
    {
        $newEditor = $this->serializer->deserialize($request->getContent(),
            Editor::class,
            'json',
        );
        
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
