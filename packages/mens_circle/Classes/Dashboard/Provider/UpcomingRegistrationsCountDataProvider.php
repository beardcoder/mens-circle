<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Dashboard\Provider;

use BeardCoder\MensCircle\Domain\Repository\RegistrationRepository;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

final readonly class UpcomingRegistrationsCountDataProvider implements NumberWithIconDataProviderInterface
{
    public function __construct(
        private RegistrationRepository $registrationRepository,
    ) {}

    public function getNumber(): int
    {
        return $this->registrationRepository->countActiveForUpcomingEvents();
    }
}
