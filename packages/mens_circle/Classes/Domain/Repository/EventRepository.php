<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Repository;

use MarkusSommer\MensCircle\Domain\Model\Event;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class EventRepository extends Repository
{
    protected $defaultOrderings = [
        'eventDate' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * @return QueryResultInterface<Event>
     */
    public function findUpcomingPublished(int $limit = 50): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('isPublished', true),
                $query->greaterThanOrEqual('eventDate', new \DateTime('today'))
            )
        );
        $query->setOrderings(['eventDate' => QueryInterface::ORDER_ASCENDING]);
        $query->setLimit($limit);

        /** @var QueryResultInterface<Event> $result */
        $result = $query->execute();

        return $result;
    }

    public function findNextEvent(): ?Event
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('isPublished', true),
                $query->greaterThanOrEqual('eventDate', new \DateTime('today'))
            )
        );
        $query->setOrderings(['eventDate' => QueryInterface::ORDER_ASCENDING]);
        $query->setLimit(1);

        $result = $query->execute();

        return $result->getFirst();
    }

    public function findOneBySlug(string $slug): ?Event
    {
        $query = $this->createQuery();
        $query->matching($query->equals('slug', $slug));
        $query->setLimit(1);

        $result = $query->execute();

        return $result->getFirst();
    }
}
