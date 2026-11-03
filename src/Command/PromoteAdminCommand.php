<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:super-admin',
    description: 'Donne le rôle ROLE_SUPER_ADMIN à un utilisateur (un seul super admin)',
)]
class PromoteAdminCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, "Email de l'utilisateur");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error("Aucun utilisateur avec l'email $email");
            return Command::FAILURE;
        }

        foreach ($this->userRepository->findAll() as $autre) {
            if ($autre !== $user && in_array('ROLE_SUPER_ADMIN', $autre->getRoles())) {
                $autre->setRoles([]);
                $io->note($autre->getEmail() . " n'est plus super admin");
            }
        }

        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $this->em->flush();

        $io->success("$email est maintenant super admin");
        return Command::SUCCESS;
    }
}