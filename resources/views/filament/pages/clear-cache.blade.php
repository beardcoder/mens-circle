<x-filament-panels::page>
  @php
        $statusItems = $this->getStatusItems();
        $statusColors = [
            'success' => ['border' => 'border-success-200 dark:border-success-500/30', 'bg' => 'bg-success-50/60 dark:bg-success-500/5', 'dot' => 'bg-success-500', 'value' => 'text-success-700 dark:text-success-300'],
            'info' => ['border' => 'border-gray-200 dark:border-white/10', 'bg' => 'bg-white dark:bg-white/5', 'dot' => 'bg-primary-500', 'value' => 'text-gray-900 dark:text-white'],
            'gray' => ['border' => 'border-gray-200 dark:border-white/10', 'bg' => 'bg-gray-50 dark:bg-white/5', 'dot' => 'bg-gray-400', 'value' => 'text-gray-600 dark:text-gray-300'],
        ];
    @endphp

  <div class="fi-cache-page space-y-6">
    {{-- Status overview --}}
    <section
      aria-label="Cache-Status"
      class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
    >
      @foreach ($statusItems as $item)
        @php $colors = $statusColors[$item['color']] ?? $statusColors['info']; @endphp
        <div
          class="rounded-xl border {{ $colors['border'] }} {{ $colors['bg'] }} p-4 shadow-sm"
        >
          <div class="flex items-center gap-2">
            <span
              class="inline-block size-2 rounded-full {{ $colors['dot'] }}"
              aria-hidden="true"
            ></span>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
              {{ $item['label'] }}
            </p>
          </div>
          <p class="mt-2 text-2xl font-semibold {{ $colors['value'] }}">
            {{ $item['value'] }}
          </p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ $item['hint'] }}
          </p>
        </div>
      @endforeach
    </section>

    {{-- Inhalts-Caches --}}
    <x-filament::section icon="heroicon-o-globe-alt" icon-color="primary">
      <x-slot name="heading">
        Inhalts-Caches
      </x-slot>
      <x-slot name="description">
        Caches, die direkt mit der ausgelieferten Webseite zu tun haben. Werden
        bei Änderungen an Models automatisch geleert.
      </x-slot>

      <div class="grid gap-4 lg:grid-cols-2">
        <div
          class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <div>
            <div class="flex items-start justify-between gap-3">
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                Response-Cache
              </h3>
              <span
                class="rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-200 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/20"
              >
                Empfohlen
              </span>
            </div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Verwirft alle gecachten HTTP-Antworten von Spatie Response Cache. Inhalte werden beim nächsten Aufruf frisch generiert.</p>
          </div>
          <div>{{ $this->clearResponseCacheAction }}</div>
        </div>

        <div
          class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
              Anwendungs-Cache
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Leert den allgemeinen Cache (Sessions, Queues, manuelle Cache-Einträge).</p>
            <p class="mt-3 font-mono text-xs text-gray-500 dark:text-gray-400">php artisan cache:clear</p>
          </div>
          <div>{{ $this->clearApplicationCacheAction }}</div>
        </div>
      </div>
    </x-filament::section>

    {{-- System-Caches --}}
    <x-filament::section
      icon="heroicon-o-cog-6-tooth"
      icon-color="gray"
      collapsible
    >
      <x-slot name="heading">
        System-Caches
      </x-slot>
      <x-slot name="description">
        Technische Caches, die nach Code- oder Konfigurationsänderungen geleert
        werden müssen.
      </x-slot>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          class="flex flex-col justify-between gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
              Konfiguration
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Aktualisiert <code class="font-mono text-xs">config:*</code> Werte aus <code class="font-mono text-xs">.env</code>.</p>
          </div>
          <div>{{ $this->clearConfigCacheAction }}</div>
        </div>

        <div
          class="flex flex-col justify-between gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
              Routen
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Baut die Route-Tabelle neu auf, falls Routen geändert wurden.</p>
          </div>
          <div>{{ $this->clearRouteCacheAction }}</div>
        </div>

        <div
          class="flex flex-col justify-between gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
              Views
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Verwirft kompilierte Blade-Templates und kompiliert sie neu.</p>
          </div>
          <div>{{ $this->clearViewCacheAction }}</div>
        </div>
      </div>
    </x-filament::section>

    {{-- Optimierung --}}
    <x-filament::section
      icon="heroicon-o-rocket-launch"
      icon-color="success"
      collapsible
      collapsed
    >
      <x-slot name="heading">
        Optimierung
      </x-slot>
      <x-slot name="description">
        Bündelt Konfiguration, Routen, Views und Events in optimierte Caches –
        ideal für Deployments.
      </x-slot>

      <div class="grid gap-4 lg:grid-cols-2">
        <div
          class="flex flex-col justify-between gap-3 rounded-xl border border-success-200 bg-success-50/40 p-5 shadow-sm dark:border-success-500/30 dark:bg-success-500/5"
        >
          <div>
            <h3
              class="text-sm font-semibold text-success-900 dark:text-success-100"
            >
              Anwendung optimieren
            </h3>
            <p class="mt-2 text-sm text-success-800/90 dark:text-success-200/80">Führt <code class="font-mono text-xs">php artisan optimize</code> aus.</p>
          </div>
          <div>{{ $this->optimizeAction }}</div>
        </div>

        <div
          class="flex flex-col justify-between gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
              Optimierung zurücksetzen
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Hilfreich während der Entwicklung – führt <code class="font-mono text-xs">optimize:clear</code> aus.</p>
          </div>
          <div>{{ $this->clearOptimizationAction }}</div>
        </div>
      </div>
    </x-filament::section>

    {{-- Danger zone --}}
    <section
      aria-label="Alle Caches löschen"
      class="rounded-xl border border-danger-200 bg-danger-50/50 p-5 shadow-sm dark:border-danger-500/30 dark:bg-danger-500/5"
    >
      <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="max-w-2xl">
          <h3
            class="text-base font-semibold text-danger-900 dark:text-danger-100"
          >
            Komplett-Reset
          </h3>
          <p class="mt-1 text-sm text-danger-800/90 dark:text-danger-200/80">Leert alle Caches in einem Schritt und baut Routen sowie Views neu auf. Verwende das nur, wenn etwas eindeutig nicht stimmt.</p>
        </div>
        <div class="shrink-0">{{ $this->clearAllAction }}</div>
      </div>
    </section>
  </div>

  <x-filament-actions::modals />
</x-filament-panels::page>
