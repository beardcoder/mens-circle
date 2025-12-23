<x-filament-panels::page>
    <div class="grid gap-6">
        <div class="relative overflow-hidden rounded-2xl border border-gray-200/80 bg-gradient-to-br from-amber-50 via-white to-amber-100/60 p-6 shadow-sm dark:border-gray-800/70 dark:from-gray-950 dark:via-gray-900 dark:to-amber-900/10 sm:p-8">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-700 dark:text-amber-400">Systempflege</p>
                <h2 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white sm:text-3xl">Cache-Verwaltung</h2>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Verwenden Sie die Aktionen oben, um einzelne Cache-Typen zu löschen. Routen- und View-Cache werden danach automatisch neu aufgebaut.
                </p>
            </div>
            <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-amber-200/70 blur-3xl dark:bg-amber-500/10"></div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800/70 dark:bg-gray-900">
                <div class="flex gap-3">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-500"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Anwendungs-Cache</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Löscht den allgemeinen Anwendungs-Cache und zwingt die App, Daten neu zu laden.
                        </p>
                        <p class="mt-3 text-xs font-mono text-gray-500 dark:text-gray-500">cache:clear</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800/70 dark:bg-gray-900">
                <div class="flex gap-3">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-500"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Konfigurations-Cache</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Entfernt den Config-Cache, damit Änderungen an Konfigurationsdateien greifen.
                        </p>
                        <p class="mt-3 text-xs font-mono text-gray-500 dark:text-gray-500">config:clear</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800/70 dark:bg-gray-900">
                <div class="flex gap-3">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-violet-500"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Routen-Cache</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Baut die Routen neu auf, damit Anpassungen sofort wirksam sind.
                        </p>
                        <p class="mt-3 text-xs font-mono text-gray-500 dark:text-gray-500">route:clear → route:cache</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800/70 dark:bg-gray-900">
                <div class="flex gap-3">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-rose-500"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">View-Cache</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Löscht kompilierten Blade-Cache und erzeugt ihn neu.
                        </p>
                        <p class="mt-3 text-xs font-mono text-gray-500 dark:text-gray-500">view:clear → view:cache</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-amber-200/80 bg-amber-50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10 sm:col-span-2 xl:col-span-2">
                <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Alle Caches auf einmal</p>
                <p class="mt-2 text-sm text-amber-800/80 dark:text-amber-200/80">
                    Führt eine komplette Bereinigung durch und baut Routen- sowie View-Cache direkt neu auf.
                </p>
                <p class="mt-3 text-xs font-mono text-amber-900/70 dark:text-amber-200/70">
                    cache:clear • config:clear • route:clear → route:cache • view:clear → view:cache
                </p>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
