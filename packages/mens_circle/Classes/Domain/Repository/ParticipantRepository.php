<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Repository;

use BeardCoder\MensCircle\Domain\Model\Participant;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Participant>
 */
final class ParticipantRepository extends Repository
{
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function findOneByEmail(string $email): ?Participant
    {
        $normalizedEmail = strtolower(trim($email));

        $query = $this->createQuery();
        $query->matching($query->equals('email', $normalizedEmail));
        $query->setLimit(1);

        $result = $query->execute();

        /** @var Participant|null */
        return $result->getFirst();
    }
}
