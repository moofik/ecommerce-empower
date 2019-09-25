<?php


namespace App\Security\Voter;


use App\Entity\Shop;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ShopVoter extends Voter
{
    /**
     * @var Security
     */
    private $security;

    /**
     * ShopVoter constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['SHOP_MANAGE'])
            && $subject instanceof Shop;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var User $user */
        $userId = $user->getId();
        /** @var Shop $subject */
        $shopOwnerId = $subject->getUser()->getId();

        return $this->security->isGranted('ROLE_ADMIN') || $userId === $shopOwnerId;
    }
}