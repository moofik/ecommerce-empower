<?php

namespace App\Service\Pagination;

class PaginatedCollection
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $count;

    /**
     * @var array
     */
    private $_links;

    /**
     * PaginatedCollection constructor.
     *
     * @param array $items
     * @param int   $total
     */
    public function __construct(array $items, int $total)
    {
        $this->items = $items;
        $this->total = $total;
        $this->count = count($items);
    }

    /**
     * @param string $rel
     * @param string $url
     */
    public function addLink(string $rel, string $url)
    {
        $this->_links[$rel] = $url;
    }
}
