<?php

declare(strict_types=1);

namespace App\Enums;

enum MessengerTemplate: string
{
    case Short = 'short';
    case Personal = 'personal';
    case Detailed = 'detailed';
    case Reminder = 'reminder';
    case ForNewMen = 'for_new_men';
    case Status = 'status';

    public function getLabel(): string
    {
        return match ($this) {
            self::Short => 'Kurz & direkt',
            self::Personal => 'Persönlich & ruhig',
            self::Detailed => 'Etwas ausführlicher',
            self::Reminder => 'Letzte Plätze / Erinnerung',
            self::ForNewMen => 'Für neue Männer',
            self::Status => 'Status / Story-Text',
        };
    }

    public function getContent(): string
    {
        return match ($this) {
            self::Short => $this->shortContent(),
            self::Personal => $this->personalContent(),
            self::Detailed => $this->detailedContent(),
            self::Reminder => $this->reminderContent(),
            self::ForNewMen => $this->forNewMenContent(),
            self::Status => $this->statusContent(),
        };
    }

    public function isAvailableForSpots(int $availableSpots): bool
    {
        if ($this === self::Reminder) {
            return $availableSpots > 0;
        }

        return true;
    }

    /**
     * @return array<int, self>
     */
    public static function availableForSpots(int $availableSpots): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn(self $template): bool => $template->isAvailableForSpots($availableSpots),
        ));
    }

    private function shortContent(): string
    {
        return <<<'TXT'
            Am {event_date} findet wieder unser Männerkreis statt.

            {event_title}
            {event_time} Uhr
            {event_location}

            Ein Abend für Männer, die raus aus dem Kopf und wieder mehr bei sich selbst ankommen wollen.
            Mit Atem, Körper, ehrlichem Austausch und einem klaren Raum unter Männern.

            Teilnahme: {cost_basis}

            Anmeldung:
            {event_url}
            TXT;
    }

    private function personalContent(): string
    {
        return <<<'TXT'
            Ich lade dich zu unserem nächsten Männerkreis ein.

            Am {event_date} kommen wir wieder als Männer zusammen:
            nicht um etwas darzustellen,
            nicht um perfekt zu sein,
            sondern um ehrlich da zu sein.

            {event_title}
            {event_time} Uhr
            {event_location}

            Der Abend ist ein geschützter Raum für Austausch, Atem, Körper und Präsenz.

            Du musst nichts leisten.
            Du musst keine Erfahrung mitbringen.
            Du darfst einfach als Mann dazukommen.

            Teilnahme: {cost_basis}

            Hier kannst du dich anmelden:
            {event_url}
            TXT;
    }

    private function detailedContent(): string
    {
        return <<<'TXT'
            Unser nächster Männerkreis steht an.

            {event_title}
            am {event_date} um {event_time} Uhr
            in {event_location}

            Wir schaffen einen Raum, in dem Männer ehrlich zusammenkommen können.
            Ohne Maske.
            Ohne Selbstdarstellung.
            Ohne den Druck, funktionieren zu müssen.

            Es geht darum, wieder mehr bei sich selbst anzukommen:
            über den Körper,
            über den Atem,
            über ehrlichen Austausch
            und über die Erfahrung, mit anderen Männern in einem klaren Raum zu sitzen.

            Der Männerkreis ist offen für Männer jeden Alters.
            Du brauchst keine Vorerfahrung und musst auch nicht besonders spirituell sein.

            Teilnahme: {cost_basis}

            Anmeldung:
            {event_url}
            TXT;
    }

    private function reminderContent(): string
    {
        return <<<'TXT'
            Kurze Erinnerung:

            Am {event_date} findet unser nächster Männerkreis statt.

            {event_title}
            {event_time} Uhr
            {event_location}

            Aktuell sind noch {available_spots} Plätze frei.
            Wenn du dabei sein möchtest, melde dich bitte rechtzeitig an.

            Es wird ein Abend mit Atem, Körper, ehrlichem Austausch und einem klaren Raum unter Männern.

            Teilnahme: {cost_basis}

            Anmeldung:
            {event_url}
            TXT;
    }

    private function forNewMenContent(): string
    {
        return <<<'TXT'
            Falls du schon länger überlegst, einmal zu einem Männerkreis zu kommen:
            Du bist willkommen.

            Am {event_date} findet unser nächstes Treffen statt:

            {event_title}
            {event_time} Uhr
            {event_location}

            Du brauchst keine Vorerfahrung.
            Du musst nichts Besonderes können.
            Und du musst auch nicht genau wissen, was dich erwartet.

            Es reicht, wenn du offen bist, dir selbst und anderen Männern ehrlich zu begegnen.
            Wir arbeiten mit Atem, Körper, Präsenz und Austausch.

            Teilnahme: {cost_basis}

            Weitere Infos und Anmeldung:
            {event_url}
            TXT;
    }

    private function statusContent(): string
    {
        return <<<'TXT'
            Nächster Männerkreis:

            {event_title}
            {event_date} · {event_time} Uhr
            {event_location}

            Raus aus dem Kopf.
            Rein in den Körper.
            Ehrlich unter Männern.

            Anmeldung:
            {event_url}
            TXT;
    }
}
