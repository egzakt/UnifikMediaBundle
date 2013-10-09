<?php

namespace Flexy\MediaBundle\Entity;

use Flexy\SystemBundle\Lib\BaseEntityRepository;

/**
 * MediaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MediaRepository extends BaseEntityRepository
{
    /**
     * Get All Uploaded Media
     *
     * @return array|mixed
     */
    public function findAll()
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.parentMedia IS NULL');

        return $this->processQuery($qb);
    }

    /**
     * Get Media by type
     *
     * @param $type
     * @return mixed
     */
    public function findByType($type)
    {
        $qb = $this->createQueryBuilder('m')

            ->andWhere('m.type = :type')
            ->andWhere('m.parentMedia IS NULL')

            ->setParameter('type', $type);

        return $this->processQuery($qb);
    }
}
