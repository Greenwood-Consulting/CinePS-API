<?php
namespace App\Command;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Attribut (Symfony 5.3+) ET fallback pour versions plus anciennes :
#[AsCommand(
    name: 'app:jwt:make-permanent',
    description: 'Génère un JWT sans expiration pour un utilisateur'
)]
class MakePermanentJwtCommand extends Command
{
    protected static $defaultName = 'app:jwt:make-permanent'; // fallback
    protected static $defaultDescription = 'Génère un JWT sans expiration pour un utilisateur';

    public function __construct(
        private UserRepository $userRepository,
        private JWTTokenManagerInterface $jwtManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email de l’utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string)$input->getArgument('email');
        $user  = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln("<error>Utilisateur $email introuvable.</error>");
            return Command::FAILURE;
        }

        // Payload SANS 'exp'
        $payload = [
            'username' => $user->getUserIdentifier(),
            'roles'    => $user->getRoles(),
            'iat'      => time(),
        ];

        $token = $this->jwtManager->createFromPayload($user, $payload);

        $output->writeln("<info>Token permanent généré pour $email :</info>");
        $output->writeln($token);
        $output->writeln("\n⚠️ Garde ce token secret (pas de date d’expiration).");
        return Command::SUCCESS;
    }
}
