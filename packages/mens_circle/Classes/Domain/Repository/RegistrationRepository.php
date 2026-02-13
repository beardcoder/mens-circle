<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Repository;

use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\Participant;
use BeardCoder\MensCircle\Domain\Model\Registration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class RegistrationRepository extends Repository
{
    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @return QueryResultInterface<Registration>
     */
    public function findActiveByEvent(Event $event): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('event', $event),
                $query->in('status', RegistrationStatus::activeValues()),
            ),
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
                $query->in('status', RegistrationStatus::activeValues()),
            ),
        );
        $query->setLimit(1);

        $result = $query->execute();

        /** @var Registration|null */
        return $result->getFirst();
    }
}
