<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Repository;

use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @template T of NewsletterSubscription
 *
 * @extends Repository<T>
 */
class NewsletterSubscriptionRepository extends Repository
{
    public function findByEmail(string $email): ?NewsletterSubscription
    {
        $query = $this->createQuery();
        $query->matching($query->equals('email', $email));

        return $query->execute()->current() ?: null;
    }

    public function findByConfirmationToken(string $token): ?NewsletterSubscription
    {
        $query = $this->createQuery();
        $query->matching($query->equals('confirmationToken', $token));

        return $query->execute()->current() ?: null;
    }

    public function findByUnsubscribeToken(string $token): ?NewsletterSubscription
    {
        $query = $this->createQuery();
        $query->matching($query->equals('unsubscribeToken', $token));

        return $query->execute()->current() ?: null;
    }

    /**
     * @return QueryResult<NewsletterSubscription>
     */
    public function findAllConfirmed(): QueryResult
    {
        $query = $this->createQuery();
        $query->matching($query->equals('isConfirmed', true));

        return $query->execute();
    }

    public function countConfirmed(): int
    {
        return $this->findAllConfirmed()->count();
    }

    public function countByIsConfirmed(bool $isConfirmed): int
    {
        $query = $this->createQuery();
        $query->matching($query->equals('isConfirmed', $isConfirmed));

        return $query->execute()->count();
    }

    /**
     * @return QueryResult<NewsletterSubscription>
     */
    public function findLatestConfirmed(int $limit = 1): QueryResult
    {
        $query = $this->createQuery();
        $query->matching($query->equals('isConfirmed', true));
        $query->setOrderings(['crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
        $query->setLimit($limit);

        /** @var QueryResult<NewsletterSubscription> $result */
        $result = $query->execute();
        return $result;
    }
}
