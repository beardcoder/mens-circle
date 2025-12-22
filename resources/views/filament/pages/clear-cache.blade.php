<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Cache-Verwaltung</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            Verwenden Sie die Schaltflächen oben, um verschiedene Cache-Typen zu löschen.
        </p>

        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
            <div>
                <strong class="text-gray-900 dark:text-white">Anwendungs-Cache:</strong> Löscht den allgemeinen Anwendungs-Cache (cache:clear)
            </div>
            <div>
                <strong class="text-gray-900 dark:text-white">Konfigurations-Cache:</strong> Löscht den Cache für Konfigurationsdateien (config:clear)
            </div>
            <div>
                <strong class="text-gray-900 dark:text-white">Routen-Cache:</strong> Löscht den Cache für Routen (route:clear)
            </div>
            <div>
                <strong class="text-gray-900 dark:text-white">View-Cache:</strong> Löscht den Cache für Blade-Templates (view:clear)
            </div>
            <div>
                <strong class="text-gray-900 dark:text-white">Alle Caches:</strong> Löscht alle oben genannten Caches auf einmal
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
