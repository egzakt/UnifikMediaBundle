<?php

namespace Egzakt\MediaBundle\Lib;

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


    public function __construct(QueryBuilder $queryBuilder, $currentPage = 1, $resultPerPage = 2)
    {
        if ($resultPerPage) {

            if ($resultPerPage < 1 || $currentPage < 1) {
                throw new \Exception('$resultPerPage and $currentPage must be superior to 1.');
            }

            $qb = clone $queryBuilder;

//            $resultCount = count($this->result = $qb->getQuery()->getScalarResult());

            $resultCount = $qb->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();

            $division = (int) ($resultCount / $resultPerPage);

            $this->pageTotal = (($resultCount % $resultPerPage) == 0) ? $division : $division + 1;

//            $this->result = array_slice($this->result, ($currentPage - 1) * $resultPerPage, $resultPerPage, true);

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