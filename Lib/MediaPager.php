<?php

namespace Flexy\MediaBundle\Lib;

use Doctrine\ORM\QueryBuilder;

class MediaPager
{
    /**
     * @var array
     */
    private $result;

    /**
     * @var int
     */
    private $pageTotal;


    public function __construct(QueryBuilder $queryBuilder, $currentPage = 1, $resultPerPage = 20)
    {
        if ($resultPerPage) {

            if ($resultPerPage < 1 || $currentPage < 1) {
                throw new \Exception('$resultPerPage and $currentPage must be superior to 1.');
            }

            $qb = clone $queryBuilder;

            $resultCount = $qb->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();

            $division = (int) ($resultCount / $resultPerPage);

            unset($qb);

            $this->pageTotal = (($resultCount % $resultPerPage) == 0) ? $division : $division + 1;

            $this->result = $queryBuilder
                ->setFirstResult(($currentPage - 1) * $resultPerPage)
                ->setMaxResults($resultPerPage)
                ->getQuery()
                ->getResult();
        }
    }

    /**
     * Get result
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get pageTotal
     *
     * @return int
     */
    public function getPageTotal()
    {
        return $this->pageTotal;
    }
}