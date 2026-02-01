<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Functional\Controller;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional tests for EventController.
 *
 * These tests require a TYPO3 test instance with the extension loaded.
 * Run via: vendor/bin/phpunit Tests/Functional/
 */
class EventControllerTest extends FunctionalTestCase
{
    protected array $typo3ExtensionsToLoad = ['mens_circle'];

    public function testListActionReturnsResponse(): void
    {
        // TODO: Set up test fixtures (events in database) and assert list page renders
        // This is a placeholder — implement after TYPO3 test instance is configured.
        self::assertTrue(true, 'Placeholder: Requires full TYPO3 test instance');
    }

    public function testShowActionReturnsResponse(): void
    {
        // TODO: Create test event, request show action, assert 200
        self::assertTrue(true, 'Placeholder: Requires full TYPO3 test instance');
    }

    public function testRegisterActionValidatesInput(): void
    {
        // TODO: POST invalid data, assert flash message with error
        self::assertTrue(true, 'Placeholder: Requires full TYPO3 test instance');
    }
}
