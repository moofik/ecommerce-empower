<?php


namespace App\Serializer\Groups;


use JMS\Serializer\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupsResolver
{
    /**
     * @var string[]
     */
    private $requiredGroups = ['Default'];

    /**
     * @var Request
     */
    private $request;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * GroupsResolver constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param Context $context
     */
    public function resolveGroups(Context $context)
    {
        $this->request = $this->requestStack->getCurrentRequest();

        $groups = $this->getRequestGroups();

        $groups = array_merge($this->requiredGroups, $groups);

        if (!empty($groups)) {
            $context->setGroups($groups);
        }
    }

    /**
     * @return array
     */
    private function getRequestGroups(): array
    {
        if ($groups = $this->request->get('groups')) {
            $groups = explode(',', $groups);

            return $groups;
        }

        return [];
    }

    /**
     * @param string $group
     * @return GroupsResolver
     */
    public function addGroup(string $group): self
    {
        $this->requiredGroups[] = $group;

        return $this;
    }

    /**
     * Reset default required groups
     * @return GroupsResolver
     */
    public function resetDefaults(): self
    {
        $this->requiredGroups = [];

        return $this;
    }
}