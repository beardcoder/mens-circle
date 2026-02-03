<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Service;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use BeardCoder\MensCircle\Domain\Model\Participant;
use BeardCoder\MensCircle\Domain\Model\Registration;
use BeardCoder\MensCircle\Service\EmailService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

class EmailServiceTest extends TestCase
{

    public function testSendRegistrationConfirmation(): void
    {
        self::markTestSkipped('EmailService requires full TYPO3 environment for proper testing');
    }

    public function testSendRegistrationConfirmationDoesNothingWhenEventIsNull(): void
    {
        self::markTestSkipped('EmailService requires full TYPO3 environment for proper testing');
    }

    public function testSendNewsletterConfirmation(): void
    {
        self::markTestSkipped('EmailService requires full TYPO3 environment for proper testing');
    }
}

