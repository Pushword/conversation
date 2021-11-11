<?php

namespace Pushword\Conversation\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Pushword\Conversation\Entity\MessageInterface as Message;

/*
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function getMessagesPublishedByReferring(string $referring, $orderBy = 'createdAt DESC', $limit = 0)
    {
        $orderBy = explode(' ', $orderBy);

        $queryBuilder = $this->createQueryBuilder('m')
            ->andWhere('m.publishedAt is NOT NULL')
            ->andWhere('m.referring =  :referring')
            ->setParameter('referring', $referring)
            ->orderBy('m.'.$orderBy[0], $orderBy[1]);
        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
