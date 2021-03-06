<?php

namespace BrainStrategist\ProjectBundle\Repository;

/**
 * Ticket_LogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Ticket_StatusRepository extends \Doctrine\ORM\EntityRepository
{

    public function findAllByProjectId($id)
    {
        $qb = $this->createQueryBuilder('ts');
        $qb->where('ts.project = :project_id')->setParameter('project_id', $id);
        return $qb;
    }

    public function findAllProjectStatusByUserID($userID)
    {

        if (!is_null($userID)) {
            $q = $this->createQueryBuilder('ts')
                ->leftJoin('ts.project', 'tp')
                ->leftJoin('tp.usersProject', 'tau')
                ->addSelect('ts')
                ->addSelect('tp')
                ->andWhere('tau.id = :user_id')
                ->setParameter('user_id',$userID)
                ->orderBy('tp.name', 'ASC');

            $query = $q->getQuery();
            $query->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
            return $query->getArrayResult();

        }else{
            return false;
        }

    }
}
