# NavigationItemResource Redesign — Tabs, Inline-Editing, Modals

**Datum:** 2026-06-04
**Status:** Genehmigt

## Ziel

Die Verwaltung der Navigationspunkte im Filament-Admin vereinfachen: schnelleres
Editieren (Inline-Toggles, SlideOver-Modals) und intuitiveres Ordnen
(Drag&Drop pro Bereich via Tabs).

## Ausgangslage

`NavigationItemResource` nutzt aktuell:

- Tabelle mit `defaultGroup('location')` und Location-Spalte/-Filter
- Sortierung über ein numerisches `sort`-Feld im Formular plus `reorderable('sort')`
- Separate Create-/Edit-Seiten (`CreateNavigationItem`, `EditNavigationItem`)

Probleme: Drag&Drop mit Gruppierung ist unhandlich, Sichtbarkeit/CTA umschalten
erfordert einen Seitenwechsel, das Sort-Zahlenfeld ist redundant zur
Drag&Drop-Sortierung.

## Design

### Listenseite (`ListNavigationItems`)

- `getTabs()`: ein Tab pro `NavigationLocation` (Header, Footer – Navigation,
  Footer – Kontakt, Footer – Rechtliches), jeweils mit Badge (Anzahl Einträge).
  Kein „Alle“-Tab — Drag&Drop braucht einen eindeutigen Bereich.
- Jeder Tab filtert die Query per `modifyQueryUsing()` auf seine Location.
- `defaultGroup`, Location-Spalte und Location-`SelectFilter` entfallen.

### Tabelle

- `reorderable('sort')` bleibt; Sortierung per Drag-Handle innerhalb des Tabs.
- Spalten: Label (searchable), URL (limitiert), Anker (Badge), Bedingung (Badge),
  `ToggleColumn` Sichtbar, `ToggleColumn` CTA.
- Spalte „Sortierung“ entfällt.
- `defaultSort('sort')`.

### Modals statt Seiten

- Create/Edit als SlideOver-Modal direkt auf der Liste
  (`CreateAction`/`EditAction` mit `->slideOver()`).
- Die Seiten `CreateNavigationItem` und `EditNavigationItem` werden gelöscht;
  `getPages()` enthält nur noch `index`.
- `CreateAction` setzt `location` auf den aktiven Tab vor (Feld bleibt im
  Formular änderbar).
- Neue Einträge bekommen `sort = max(sort) + 1` innerhalb ihres Bereichs.
- `DeleteAction` als Row-Action in der Tabelle.

### Formular

- Sektionen „Allgemein“ und „Verhalten“ bleiben wie bisher.
- Das Feld „Sortierung“ entfällt (Drag&Drop ist die einzige Sortier-Methode).

## Fehlerbehandlung

- Toggle-Inline-Updates laufen über Eloquent → `ClearsResponseCache`
  invalidiert den Response-Cache wie bisher.
- `location`-Wechsel im Edit-Modal ist erlaubt; der Eintrag erscheint danach im
  anderen Tab (Sortierung behält den bisherigen `sort`-Wert).

## Tests

Bestehende Resource-Tests anpassen bzw. ergänzen:

- Tabs zeigen nur Einträge ihrer Location.
- Create via Modal: `location` ist mit aktivem Tab vorbelegt, `sort` wird auf
  max+1 gesetzt.
- Edit via Modal speichert (kein Redirect-Assert).
- ToggleColumn-Update ändert `is_visible`/`is_cta` in der DB.
- Reorder aktualisiert `sort`.
