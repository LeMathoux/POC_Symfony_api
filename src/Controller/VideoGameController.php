<?php

namespace App\Controller;

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
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class VideoGameController extends AbstractController
{
    private VideoGameRepository $videoGameRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private  UrlGeneratorInterface $urlGenerator;

    public function __construct(VideoGameRepository $videoGameRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->videoGameRepository = $videoGameRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/api/video_game', name: 'app_video_game_list', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des jeux video')]
    public function getVideoGames(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $videoGameList = $this->videoGameRepository->getAllWithPagination($page, $limit);

        return $this->json($videoGameList, Response::HTTP_OK, [], ['groups' => 'video_game']);
    }

    #[Route('/api/video_game/{id}', name: 'app_video_game_by_id', methods:['GET'], requirements:['id' => Requirement::DIGITS])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à visualiser des jeux video')]
    public function getEditorById(VideoGame $videoGame, VideoGameRepository $editorRepository): JsonResponse
    {
        return $this->json($videoGame, Response::HTTP_OK, [], ['groups' => 'video_game']);
    }

    #[Route('/api/video_game/{id}/delete', name:'app_video_game_delete', methods:['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à supprimer des jeux video')]
    public function deleteVideoGame(VideoGame $videoGame): JsonResponse
    {
        $this->entityManager->remove($videoGame);
        $this->entityManager->flush();

        return $this->json(['statut'=>'success'], Response::HTTP_OK);
    }

    #[Route('api/video_game/{id}/update', name:'app_video_game_update', methods:['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à modifier des jeux video')]
    public function updateVideoGame(Request $request, VideoGame $currentVideoGame): JsonResponse
    {
        $updatedVideoGame = $this->serializer->deserialize($request->getContent(),
            VideoGame::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentVideoGame]
        );
        
        $this->entityManager->persist($updatedVideoGame);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_video_game_by_id',
            ['id' => $updatedVideoGame->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_OK, ['Location' => $location]);
    }

    #[Route('api/video_game/new', name:'app_video_game_create', methods:['POST'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\êtes pas autorisé à créer des jeux video')]
    public function createVideoGame(Request $request): JsonResponse
    {
        $newVideoGame = $this->serializer->deserialize($request->getContent(),
            VideoGame::class,
            'json',
        );
        
        $this->entityManager->persist($newVideoGame);
        $this->entityManager->flush();

        $location = $this->urlGenerator->generate(
            'app_video_game_by_id',
            ['id' => $newVideoGame->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(['statut'=>'success'], Response::HTTP_OK, ['Location' => $location]);
    }
}
