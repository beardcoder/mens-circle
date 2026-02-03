<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Repository;

use BeardCoder\MensCircle\Domain\Model\Participant;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for Participant domain model
 */
class ParticipantRepository extends Repository
{
    /**
     * Find a participant by email address
     *
     * @param string $email
     * @return Participant|null
     */
    public function findOneByEmail(string $email): ?Participant
    {
        $query = $this->createQuery();
        $query->matching($query->equals('email', $email));

        /** @var Participant|null $result */
        $result = $query->execute()->getFirst();
        return $result;
    }

    /**
     * Find or create a participant by email
     * Useful for event registrations and newsletter subscriptions
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @return Participant
     */
    public function findOrCreateByEmail(
        string $email,
        string $firstName = '',
        string $lastName = '',
        string $phone = ''
    ): Participant {
        $participant = $this->findOneByEmail($email);

        if ($participant === null) {
            $participant = new Participant();
            $participant->setEmail($email);
            $participant->setFirstName($firstName);
            $participant->setLastName($lastName);
            $participant->setPhone($phone);
            $this->add($participant);
        } else {
            // Update participant data if provided
            if ($firstName !== '' && $participant->getFirstName() === '') {
                $participant->setFirstName($firstName);
            }
            if ($lastName !== '' && $participant->getLastName() === '') {
                $participant->setLastName($lastName);
            }
            if ($phone !== '' && $participant->getPhone() === '') {
                $participant->setPhone($phone);
            }
            $this->update($participant);
        }

        return $participant;
    }
}

