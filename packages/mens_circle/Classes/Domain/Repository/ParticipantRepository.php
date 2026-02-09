<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Repository;

use MarkusSommer\MensCircle\Domain\Model\Participant;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class ParticipantRepository extends Repository
{
    public function findOneByEmail(string $email): ?Participant
    {
        $normalizedEmail = strtolower(trim($email));

        $query = $this->createQuery();
        $query->matching($query->equals('email', $normalizedEmail));
        $query->setLimit(1);

        $result = $query->execute();

        return $result->getFirst();
    }
}
