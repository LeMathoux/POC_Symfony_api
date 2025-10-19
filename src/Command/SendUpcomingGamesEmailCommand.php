<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\VideoGameRepository;
use App\Service\EmailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

#[AsCommand(
    name: 'app:send-upcoming-games-email',
    description: 'Envoie un email avec les jeux qui sortent dans les 7 prochains jours aux abonnés.'
)]
class SendUpcomingGamesEmailCommand extends Command
{
    private VideoGameRepository $videoGameRepository;
    private UserRepository $userRepository;
    private EmailService $emailService;
    private Environment $twig;

    public function __construct(VideoGameRepository $videoGameRepository, UserRepository $userRepository, EmailService $emailService, Environment $twig)
    {
        parent::__construct();
        $this->videoGameRepository = $videoGameRepository;
        $this->emailService = $emailService;
        $this->userRepository = $userRepository;
        $this->twig = $twig;
    }

    protected function configure(): void
    {
        $this->setDescription('Envoie un email avec les jeux qui sortent dans les 7 prochains jours aux abonnés.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $videoGames = $this->videoGameRepository->findUpcomingGames();
        $subscribers = $this->userRepository->findUsersSubscribedToNewsletter();

        if (empty($videoGames)) {
            $io->info('Aucun jeu à sortir dans les 7 prochains jours.');
            return Command::SUCCESS;
        }

        if (empty($subscribers)) {
            $io->info('Aucun Abonné à la newsletter trouvé.');
            return Command::SUCCESS;
        }

        $content = $this->twig->render('email/newsletter.html.twig', [
            'videoGames' => $videoGames,
        ]);

        foreach ($subscribers as $subscriber) {
            try {
                $this->emailService->sendGamesEmail(
                    $subscriber->getEmail(),
                    'Jeux à sortir dans les 7 jours',
                    $content
                );
                $io->info("Email envoyé à {$subscriber->getEmail()}");
            } catch (\Exception $e) {
                $io->warning("Impossible d'envoyer l'email à {$subscriber->getEmail()} : " . $e->getMessage());
            }
        }

        $io->success('Emails envoyés avec succès !');

        return Command::SUCCESS;
    }
}
