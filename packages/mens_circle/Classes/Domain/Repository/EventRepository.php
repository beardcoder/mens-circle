<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Repository;

use BeardCoder\MensCircle\Domain\Model\Event;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @template T of Event
 * @extends Repository<T>
 */
class EventRepository extends Repository
{
    /**
     * @return QueryResult<Event>
     */
    public function findUpcoming(): QueryResult
    {
        $query = $this->createQuery();
        $query->setConstraints([
            $query->greaterThan('eventDate', new \DateTime()),
            $query->equals('isPublished', true),
        ]);
        $query->setOrderings(['eventDate' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }

    public function findNextUpcoming(): ?Event
    {
        $query = $this->createQuery();
        $query->setConstraints([
            $query->greaterThan('eventDate', new \DateTime()),
            $query->equals('isPublished', true),
        ]);
        $query->setOrderings(['eventDate' => QueryInterface::ORDER_ASCENDING]);
        $query->setLimit(1);

        $results = $query->execute();

        return $results->current() ?: null;
    }

    /**
     * @return QueryResult<Event>
     */
    public function findPast(): QueryResult
    {
        $query = $this->createQuery();
        $query->setConstraints([
            $query->lessThan('eventDate', new \DateTime()),
            $query->equals('isPublished', true),
        ]);
        $query->setOrderings(['eventDate' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }

    public function findBySlug(string $slug): ?Event
    {
        $query = $this->createQuery();
        $query->setConstraints([
            $query->equals('slug', $slug),
        ]);

        return $query->execute()->current() ?: null;
    }
}
