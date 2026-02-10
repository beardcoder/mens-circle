<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Repository;

use MarkusSommer\MensCircle\Domain\Model\NewsletterSubscription;
use MarkusSommer\MensCircle\Domain\Model\Participant;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class NewsletterSubscriptionRepository extends Repository
{
    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function findOneByToken(string $token): ?NewsletterSubscription
    {
        $query = $this->createQuery();
        $query->matching($query->equals('token', $token));
        $query->setLimit(1);

        $result = $query->execute();

        return $result->getFirst();
    }

    public function findOneByParticipant(Participant $participant): ?NewsletterSubscription
    {
        $query = $this->createQuery();
        $query->matching($query->equals('participant', $participant));
        $query->setLimit(1);

        $result = $query->execute();

        return $result->getFirst();
    }
}
