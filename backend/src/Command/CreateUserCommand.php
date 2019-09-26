<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var JWTTokenManagerInterface
     */
    private $tokenManager;

    /**
     * CreateAdminUserCommand constructor.
     *
     * @param EntityManagerInterface       $em
     * @param UserRepository               $repository
     * @param UserPasswordEncoderInterface $encoder
     * @param JWTTokenManagerInterface     $tokenManager
     * @param string|null                  $name
     */
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $repository,
        UserPasswordEncoderInterface $encoder,
        JWTTokenManagerInterface $tokenManager,
        string $name = null
    ) {
        parent::__construct($name);
        $this->encoder = $encoder;
        $this->em = $em;
        $this->repository = $repository;
        $this->tokenManager = $tokenManager;
    }

    public function configure()
    {
        $this
            ->setName('app:create:user')
            ->setDescription('Create User');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $question = new Question('<question>Please, enter username</question> '.'(default: test):', 'test');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('<question>Please, enter email for the user</question> '.'(default: test@mail.com):', 'test@mail.com');
        $email = $helper->ask($input, $output, $question);

        $question = new Question('<question>Please, enter the password for the user</question> '.'(default: 111111):', '111111');
        $password = $helper->ask($input, $output, $question);

        $question = new ConfirmationQuestion(
            '<question>Do you want to grant admin access to the user?(y/n)</question> '.'(default: y):',
            true,
            '/^(y|j)/i'
        );
        $hasAdminAccess = $helper->ask($input, $output, $question);

        $user = $this->repository->findOneBy(['username' => $username]);

        if ($user === null) {
            $user = new User();
            $user->setUsername($username);
        }

        $user->setEmail($email);

        if ($hasAdminAccess) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $user->setPassword($this->encoder->encodePassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $token = $this->tokenManager->create($user);
        $io->section('I generated API token for this user: '.$token);
    }
}
