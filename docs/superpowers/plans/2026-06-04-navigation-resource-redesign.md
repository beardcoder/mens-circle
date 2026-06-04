# NavigationItemResource Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Navigationspunkte im Filament-Admin per Location-Tabs, Inline-Toggles und SlideOver-Modals verwalten — schnelleres Editieren, intuitiveres Drag&Drop-Ordnen.

**Architecture:** Die Resource wird zur "simple resource": Create-/Edit-Seiten werden gelöscht, Create/Edit laufen als SlideOver-Modals auf der Liste. Die List-Seite bekommt `getTabs()` mit einem Tab pro `NavigationLocation` (Tab-Key = Enum-Value), der die Query filtert. `is_visible`/`is_cta` werden `ToggleColumn`s; das `sort`-Zahlenfeld entfällt (nur noch Drag&Drop, neue Einträge bekommen `max(sort)+1`).

**Tech Stack:** Laravel 12, Filament v5, Mago QA. **Keine Tests** — das Projekt hat keine Test-Infrastruktur; Verifikation via `composer run qa` + manueller Browser-Check.

**Spec:** `docs/superpowers/specs/2026-06-04-navigation-resource-redesign-design.md`

---

## Hintergrund für den Implementierer

- Filament v5: List-Page-Tabs kommen aus `Filament\Schemas\Components\Tabs\Tab` mit `->modifyQueryUsing()`. Tab-Keys landen im URL-Query-String; `$livewire->activeTab` (auf `ListRecords`) hält den aktiven Key.
- Wenn eine Resource keine Create-/Edit-Seiten in `getPages()` hat, öffnen `CreateAction`/`EditAction` automatisch Modals. `->slideOver()` macht daraus SlideOver.
- `CreateAction::mutateDataUsing()` (v5-Name, nicht `mutateFormDataUsing`) mutiert Formulardaten vor dem Speichern.
- `ToggleColumn` (`Filament\Tables\Columns\ToggleColumn`) speichert direkt beim Umschalten. Es gibt nur einen Admin-User-Typ (GitHub-OAuth-Admins), keine Policies — kein zusätzlicher Auth-Check nötig.
- Das Model `NavigationItem` nutzt `ClearsResponseCache` — jede Eloquent-Änderung (auch ToggleColumn/Reorder) invalidiert den Response-Cache automatisch. Nichts zu tun.
- Deutsch ist Pflicht für alle Labels.
- QA: `composer run qa` (Mago format-check + lint + analyze). Auto-Fix: `composer run qa:fix`.

---

### Task 1: List-Seite — Tabs pro Location + SlideOver-CreateAction

**Files:**
- Modify: `app/Filament/Resources/NavigationItemResource/Pages/ListNavigationItems.php`

- [ ] **Step 1: Datei komplett ersetzen**

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\NavigationItemResource\Pages;

use App\Enums\NavigationLocation;
use App\Filament\Resources\NavigationItemResource;
use App\Models\NavigationItem;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ListNavigationItems extends ListRecords
{
    protected static string $resource = NavigationItemResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->mutateDataUsing(
                    fn(array $data): array => [
                        ...$data,
                        'sort' => ((int) NavigationItem::query()
                            ->where('location', $data['location'])
                            ->max('sort')) + 1,
                    ],
                ),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    #[Override]
    public function getTabs(): array
    {
        $tabs = [];

        foreach (NavigationLocation::cases() as $location) {
            $tabs[$location->value] = Tab::make($location->getLabel())
                ->badge(NavigationItem::query()->where('location', $location->value)->count())
                ->modifyQueryUsing(
                    fn(Builder $query): Builder => $query->where('location', $location->value),
                );
        }

        return $tabs;
    }
}
```

Hinweise:
- Tab-Key = `$location->value` (z.B. `header`) — wird später in der Form als Default für `location` wiederverwendet (Task 2).
- Kein „Alle“-Tab, damit Drag&Drop-Sortierung immer einen eindeutigen Bereich hat.
- Falls Mago bei `#[Override]` auf `getTabs()` meckert (Methode existiert evtl. als nicht-abstrakte Basis-Methode — Override ist korrekt), Attribut beibehalten; falls Analyze einen Fehler wirft, Attribut entfernen.

- [ ] **Step 2: QA laufen lassen**

Run: `composer run qa`
Expected: keine Fehler in `ListNavigationItems.php`. (Fehler in der Resource selbst sind hier noch OK, falls Task 2 noch nicht erledigt — beide Tasks bauen aufeinander auf, im Zweifel Task 2 zuerst fertigstellen und gemeinsam prüfen.)

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Resources/NavigationItemResource/Pages/ListNavigationItems.php
git commit -m "feat(admin): filter navigation items with location tabs"
```

---

### Task 2: Resource — Tabelle mit Inline-Toggles, SlideOver-Edit, Form ohne Sort-Feld

**Files:**
- Modify: `app/Filament/Resources/NavigationItemResource.php`

- [ ] **Step 1: Datei komplett ersetzen**

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Filament\Resources\NavigationItemResource\Pages\ListNavigationItems;
use App\Filament\Support\Anchor;
use App\Models\NavigationItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Override;
use UnitEnum;

class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;

    protected static ?string $navigationLabel = 'Navigation';

    protected static ?string $modelLabel = 'Navigationspunkt';

    protected static ?string $pluralModelLabel = 'Navigationspunkte';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bars3;

    protected static UnitEnum|string|null $navigationGroup = 'Inhalte';

    protected static ?int $navigationSort = 45;

    protected static ?string $recordTitleAttribute = 'label';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Allgemein')
                ->schema([
                    Select::make('location')
                        ->label('Bereich')
                        ->options(NavigationLocation::class)
                        ->required()
                        ->native(false)
                        ->default(
                            fn(mixed $livewire): ?string => $livewire instanceof ListNavigationItems
                                && is_string($livewire->activeTab)
                                ? $livewire->activeTab
                                : null,
                        )
                        ->helperText('In welchem Navigationsbereich der Link erscheint.'),

                    TextInput::make('label')->label('Beschriftung')->required()->maxLength(255),

                    TextInput::make('url')
                        ->label('URL / Pfad')
                        ->maxLength(2048)
                        ->placeholder('/atemuebung oder https://...')
                        ->helperText(
                            'Beginnt mit "/": interner Pfad. Sonst absolute URL. Leer lassen für die Startseite. Bei Bedingung "Nächster Termin" wird die URL automatisch gesetzt.',
                        ),

                    TextInput::make('anchor')
                        ->label('Anker')
                        ->maxLength(255)
                        ->placeholder('ueber, stimmen, faq, ...')
                        ->dehydrateStateUsing(Anchor::normalise(...))
                        ->helperText(
                            'Optionaler Anker (z.B. "ueber"). Wird an die URL angehängt: /pfad#anker. Ohne führendes "#" eingeben.',
                        ),

                    Select::make('condition')
                        ->label('Bedingung')
                        ->options(NavigationCondition::class)
                        ->native(false)
                        ->placeholder('Keine')
                        ->helperText('Optional: Sichtbarkeit & URL dynamisch steuern.'),
                ])
                ->columns(2),

            Section::make('Verhalten')
                ->schema([
                    Toggle::make('is_visible')->label('Sichtbar')->default(true)->inline(false),
                    Toggle::make('is_cta')->label('Als Button (CTA) darstellen')->default(false)->inline(false),
                    Toggle::make('open_in_new_tab')->label('In neuem Tab öffnen')->default(false)->inline(false),
                    TextInput::make('umami_event_target')
                        ->label('Umami Event Target')
                        ->maxLength(255)
                        ->helperText('Optional: Wert für data-umami-event-target. Leer lassen für keinen Tracking-Wert.'),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Beschriftung')->searchable(),
                TextColumn::make('url')->label('URL')->limit(40)->toggleable(),
                TextColumn::make('anchor')->label('Anker')->badge()->toggleable(),
                TextColumn::make('condition')->label('Bedingung')->badge()->toggleable(),
                ToggleColumn::make('is_visible')->label('Sichtbar'),
                ToggleColumn::make('is_cta')->label('CTA'),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->defaultPaginationPageOption(50)
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListNavigationItems::route('/'),
        ];
    }
}
```

Änderungen gegenüber vorher (zur Selbstkontrolle):
- **Entfernt:** `defaultGroup('location')`, `defaultSort('location')`, `TextColumn location`, `TextColumn sort`, `IconColumn is_cta`, `IconColumn is_visible`, `SelectFilter location` samt `->filters([...])`, `TextInput sort` im Formular, `getRelations()` (Basis-Implementierung gibt bereits `[]` zurück), Pages `create`/`edit` aus `getPages()`, Imports `IconColumn`, `SelectFilter`, `CreateNavigationItem`, `EditNavigationItem`.
- **Neu:** `ToggleColumn` für `is_visible`/`is_cta`, `EditAction->slideOver()`, `DeleteAction` als Row-Action, `defaultSort('sort')`, `location`-Default aus aktivem Tab, Imports `ToggleColumn`, `DeleteAction`, `ListNavigationItems`.
- `->filters([])` komplett weglassen (kein leerer Aufruf).

- [ ] **Step 2: Create-/Edit-Page-Klassen löschen**

```bash
rm app/Filament/Resources/NavigationItemResource/Pages/CreateNavigationItem.php
rm app/Filament/Resources/NavigationItemResource/Pages/EditNavigationItem.php
```

- [ ] **Step 3: Prüfen, dass nichts mehr auf die gelöschten Klassen verweist**

Run: `grep -rn "CreateNavigationItem\|EditNavigationItem" app/ database/ routes/ resources/`
Expected: keine Treffer.

- [ ] **Step 4: QA laufen lassen**

Run: `composer run qa`
Expected: PASS (format, lint, analyze ohne Fehler).
Falls nur Formatfehler: `composer run qa:fix`, dann erneut `composer run qa`.

- [ ] **Step 5: Routen verifizieren**

Run: `php artisan route:list --path=navigation-items`
Expected: nur noch die Index-Route (keine `/create`- und `/{record}/edit`-Routen mehr).

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/NavigationItemResource.php app/Filament/Resources/NavigationItemResource/Pages/
git commit -m "feat(admin): manage navigation items inline with toggles and slide-overs"
```

---

### Task 3: Manuelle Verifikation im Browser

**Files:** keine Änderungen — reine Verifikation.

- [ ] **Step 1: Admin-Panel öffnen**

Der Admin-Login läuft über GitHub OAuth — die Verifikation macht der Nutzer selbst im Browser, oder via Chrome-DevTools-MCP, falls eine eingeloggte Session existiert. URL via Boost-Tool `get-absolute-url` auflösen (Pfad: `/admin/navigation-items`).

- [ ] **Step 2: Checkliste durchgehen**

1. Vier Tabs sichtbar (Header, Footer – Navigation, Footer – Kontakt, Footer – Rechtliches) mit korrekten Badge-Zahlen; erster Tab aktiv.
2. Tab-Wechsel zeigt nur Einträge des Bereichs, sortiert nach `sort`.
3. Toggle „Sichtbar“/„CTA“ schaltet direkt in der Zeile und persistiert nach Reload.
4. „Erstellen“ öffnet SlideOver; „Bereich“ ist mit dem aktiven Tab vorbelegt; neuer Eintrag erscheint am Ende des Tabs (sort = max+1).
5. Stift-Action öffnet Edit-SlideOver; Speichern aktualisiert die Zeile ohne Seitenwechsel.
6. Bereich-Wechsel im Edit-SlideOver verschiebt den Eintrag in den anderen Tab (Badge-Zahlen aktualisieren sich nach Reload).
7. Reorder-Button aktiviert Drag&Drop; neue Reihenfolge persistiert und entspricht der Frontend-Navigation.
8. Löschen-Action (Papierkorb) funktioniert mit Bestätigungs-Modal.
9. Frontend-Check: Header-/Footer-Navigation der Website spiegelt Sichtbarkeits-/Reihenfolge-Änderungen wider (Response-Cache wird durch `ClearsResponseCache` invalidiert).

- [ ] **Step 3: Bei Problemen** systematic-debugging-Skill nutzen, Fixes als eigene Commits.
