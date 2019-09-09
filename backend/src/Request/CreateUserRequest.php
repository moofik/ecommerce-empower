<?php

namespace App\Request;

use App\Entity\User;
use App\Facade\Doctrine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateUserRequest implements RequestDTOInterface
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string", message="Имя должно быть текстовой строкой")
     */
    private $firstName;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string", message="Фамилия должна быть текстовой строкой")
     */
    private $lastName;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string", message="Пароль должен быть текстовой строкой")
     */
    private $password;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var bool
     * @Assert\NotBlank()
     * @Assert\Type(type="bool")
     */
    private $hasAcceptedAgreement;

    /**
     * @param ExecutionContextInterface $context
     * @param $payload
     *
     * @Assert\Callback
     */
    public function validateUser(ExecutionContextInterface $context, $payload): void
    {
        $users = Doctrine::getManager()
            ->getRepository(User::class)
            ->findBy(['username' => $this->username]);

        if (0 !== count($users)) {
            $context->buildViolation('К сожалению пользователь с таким ником уже существует.')
                ->atPath('username')
                ->addViolation();
        }
    }

    /**
     * CreateUserRequest constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->firstName = $request->get('first_name');
        $this->lastName = $request->get('last_name');
        $this->username = $request->get('username');
        $this->password = $request->get('password');
        $this->phone = $request->get('phone');
        $this->email = $request->get('email');
        $this->city = $request->get('phone');
        $this->hasAcceptedAgreement = $request->get('agreement');
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return bool
     */
    public function hasAcceptedAgreement(): bool
    {
        return $this->hasAcceptedAgreement;
    }
}
