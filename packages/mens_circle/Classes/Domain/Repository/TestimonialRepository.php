<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Repository;

use MarkusSommer\MensCircle\Domain\Model\Testimonial;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class TestimonialRepository extends Repository
{
    protected $defaultOrderings = [
        'sortOrder' => QueryInterface::ORDER_ASCENDING,
        'crdate' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @return QueryResultInterface<Testimonial>
     */
    public function findPublished(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('isPublished', true));

        /** @var QueryResultInterface<Testimonial> $result */
        $result = $query->execute();

        return $result;
    }
}
