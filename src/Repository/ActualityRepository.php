<?php

namespace App\Repository;

use App\Entity\Actuality;
use App\Entity\Actuality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Actuality|null find($id, $lockMode = null, $lockVersion = null)
 * @method Actuality|null findOneBy(array $criteria, array $orderBy = null)
 * @method Actuality[]    findAll()
 * @method Actuality[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class ActualityRepository extends ServiceEntityRepository
{
    protected int $maxLength = 500;

    public function setMaxLength($maxLength)
    {
        $maxLength = intval($maxLength);
        if ($maxLength > 0 && $maxLength < 500) {
            $this->maxLength = $maxLength;
        }
    }

    /**
     * @param Actuality $article
     * @param User $user
     * @return Actuality
     */
    public function insert(Actuality $article)
    {
        $this->_em->persist($article);
        $this->_em->flush();
        return $article;
    }

    /**
     * @param Actuality $article
     */
    public function delete(Actuality $article)
    {
        $this->_em->remove($article);
        $this->_em->flush();
    }

    /**
     * @param string $keywords
     * @return Actuality[] Returns an array of Article objects
     */
    public function search(string $keywords, int $limit = null)
    {
        $keywords = trim($keywords);
        $statement = $this->createQueryBuilder('a')
            ->where('a.title LIKE :keyword');

        $statement->setParameter('keyword', "%$keywords%")
            ->orderBy('a.createdAt', 'DESC');

        if (!is_null($limit)) $statement->setMaxResults($limit);

        return $statement->getQuery()->getResult();
    }


    public function findOne($id)
    {
        return  $this->createQueryBuilder('a')
            ->addSelect(['tag')
            ->leftJoin("a.tags", 'tag')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Actuality[] Returns an array of Article objects
     */
    public function findAll()
    {
        return $this->createQueryBuilder('a')
            ->addSelect(['tag'])
            ->leftJoin("a.tags", 'tag')
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults($this->maxLength)
            ->getQuery()
            ->getResult();
    }


    public function findWithMax($max)
    {
        $ids = $this->createQueryBuilder('a')
            ->select('a.id')
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getArrayResult();
        
        if(empty($ids))
            return [];

        $ids = array_map(fn ($res) => $res['id'], $ids);
        
        return $this->createQueryBuilder('a')
        ->addSelect(['tag', 'u'])
        ->leftJoin('a.tags', 'tag')
        ->andWhere("a.id IN (" . implode(',', $ids) . ")")
        ->addOrderBy('a.createdAt', 'DESC')
        ->getQuery()->getResult();
    }

    public function findWithQuery(?array $tags = null, ?string $search = null)
    {
        $query = $this->createQueryBuilder('a')
            ->addSelect(['tag']);

        if (!is_null($tags)) {
            $query
                ->innerJoin('a.tags', 'tag')
                ->innerJoin('a.tags', 'filter_tag')
                ->andWhere("filter_tag.id in (:ids) ")
                ->setParameter("ids", $tags);
        } else {
            $query->leftJoin('a.tags', 'tag');
        }

        if (!is_null($search)) {
            $searches = explode(',', $search, 8);
            foreach ($searches as $i => $_search) {
                $_search = trim($_search);
                $query->andWhere(
                    "a.title LIKE :search$i"
                    . " OR a.shortDescription LIKE :search$i"
                )->setParameter("search$i", "%$_search%");
            }
        }

        $query->addOrderBy('a.createdAt', 'DESC');

        return $query->getQuery()->getResult();
    }
}
