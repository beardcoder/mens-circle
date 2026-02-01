<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Repository;

use BeardCoder\MensCircle\Domain\Model\Testimonial;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @template T of Testimonial
 * @extends Repository<T>
 */
class TestimonialRepository extends Repository
{
    /**
     * @return QueryResult<Testimonial>
     */
    public function findApproved(): QueryResult
    {
        $query = $this->createQuery();
        $query->setConstraints([
            $query->equals('isApproved', true),
        ]);
        $query->setOrderings(['createdAt' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }

    /**
     * @return QueryResult<Testimonial>
     */
    public function findPending(): QueryResult
    {
        $query = $this->createQuery();
        $query->setConstraints([
            $query->equals('isApproved', false),
        ]);
        $query->setOrderings(['createdAt' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }
}
