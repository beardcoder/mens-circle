<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\ViewHelpers;

use BeardCoder\MensCircle\Domain\Model\Event;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class EventStatusViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('event', Event::class, 'The event to display status for', true);
    }

    public function render(): string
    {
        /** @var Event $event */
        $event = $this->arguments['event'];

        if ($event->isPast()) {
            return '<span class="badge badge--past">Vergangen</span>';
        }

        if ($event->isFull()) {
            return '<span class="badge badge--full">Ausgebucht</span>';
        }

        $remaining = $event->getRemainingSpots();
        if ($remaining !== PHP_INT_MAX && $remaining <= 3) {
            return \sprintf(
                '<span class="badge badge--limited">Nur noch %d Plätze frei</span>',
                $remaining,
            );
        }

        return '<span class="badge badge--available">Plätze verfügbar</span>';
    }
}
