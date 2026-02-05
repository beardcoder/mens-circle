<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Repository;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\Registration;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @template T of Registration
 *
 * @extends Repository<T>
 */
class RegistrationRepository extends Repository
{
    /**
     * @return QueryResult<Registration>
     */
    public function findByEvent(Event $event): QueryResult
    {
        $query = $this->createQuery();
        $query->matching($query->equals('event', $event));
        $query->setOrderings(['createdAt' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }

    public function findByConfirmationToken(string $token): ?Registration
    {
        $query = $this->createQuery();
        $query->matching($query->equals('confirmationToken', $token));

        return $query->execute()->current() ?: null;
    }

    public function countByEvent(Event $event): int
    {
        $query = $this->createQuery();
        $query->matching($query->equals('event', $event));

        return $query->execute()->count();
    }

    public function findByEmail(string $email): ?Registration
    {
        $query = $this->createQuery();
        $query->matching($query->equals('participant.email', $email));
        $query->setOrderings(['createdAt' => QueryInterface::ORDER_DESCENDING]);
        $query->setLimit(1);

        return $query->execute()->current() ?: null;
    }
}
