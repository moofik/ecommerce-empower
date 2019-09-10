<?php


namespace App\Service\Pagination;


use App\Service\Pagination\Pagerfanta\Adapter\NonIterableDoctrineORMAdapter;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\RouterInterface;

class PaginatedCollectionFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * PaginatedCollectionFactory constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createCollection(
        QueryBuilder $queryBuilder,
        QueryBuilder $countQueryBuilder,
        string $route,
        array $params = [],
        int $currentPage = 1,
        int $maxPerPage = 10
    ): PaginatedCollection
    {
        $adapter = new NonIterableDoctrineORMAdapter($queryBuilder, $countQueryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($currentPage);

        $items = [];
        $iterator = $pagerfanta->getCurrentPageResults();

        foreach ($iterator as $item) {
            $items[] = $item;
        }

        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults());
        $paginatedCollection->addLink('first', $this->createLinkUrl($route, $params, 1));
        $paginatedCollection->addLink('last', $this->createLinkUrl($route, $params, $pagerfanta->getNbPages()));
        $paginatedCollection->addLink('self', $this->createLinkUrl($route, $params, $currentPage));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $this->createLinkUrl($route, $params, $pagerfanta->getNextPage()));
        }

        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $this->createLinkUrl($route, $params, $pagerfanta->getPreviousPage()));
        }

        return $paginatedCollection;
    }

    /**
     * @param string $route
     * @param array $params
     * @param int $targetPage
     * @return string
     */
    private function createLinkUrl(string $route, array $params, int $targetPage): string
    {
        $params = array_merge($params, ['page' => $targetPage]);

        return $this->router->generate($route, $params);
    }
}