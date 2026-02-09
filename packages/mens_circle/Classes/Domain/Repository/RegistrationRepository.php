<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Repository;

use MarkusSommer\MensCircle\Domain\Enum\RegistrationStatus;
use MarkusSommer\MensCircle\Domain\Model\Event;
use MarkusSommer\MensCircle\Domain\Model\Participant;
use MarkusSommer\MensCircle\Domain\Model\Registration;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class RegistrationRepository extends Repository
{
    /**
     * @return QueryResultInterface<Registration>
     */
    public function findActiveByEvent(Event $event): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('event', $event),
                $query->in('status', RegistrationStatus::activeValues())
            )
        );

        /** @var QueryResultInterface<Registration> $result */
        $result = $query->execute();

        return $result;
    }

    public function countActiveByEvent(Event $event): int
    {
        return $this->findActiveByEvent($event)->count();
    }

    public function findActiveByEventAndParticipant(Event $event, Participant $participant): ?Registration
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('event', $event),
                $query->equals('participant', $participant),
                $query->in('status', RegistrationStatus::activeValues())
            )
        );
        $query->setLimit(1);

        $result = $query->execute();

        return $result->getFirst();
    }
}
