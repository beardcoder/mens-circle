@extends('layouts.app')

@section('title', 'System Health - ' . $siteName)
@section('robots', 'noindex, nofollow')

@section('content')
    <main id="main" class="main">
        <section class="section">
            <div class="container">
                <div class="section__header" style="text-align: center; max-width: 800px; margin: 0 auto var(--space-3xl);">
                    <h1 class="section__title">System Health</h1>
                    <p class="section__description" style="margin-block-start: var(--space-md);">
                        Übersicht über den Status aller Systemprüfungen.
                        @if($lastRanAt)
                            Letzte Prüfung: {{ $lastRanAt->format('d.m.Y H:i:s') }} Uhr
                        @endif
                    </p>
                </div>

                @if($checkResults && $checkResults->storedCheckResults->isNotEmpty())
                    <div style="max-width: 900px; margin: 0 auto;">
                        @foreach($checkResults->storedCheckResults as $result)
                            <div style="
                                background: var(--bg-elevated);
                                border-radius: var(--radius-md);
                                padding: var(--space-lg);
                                margin-block-end: var(--space-md);
                                border-inline-start: 4px solid {{ $result->status === 'ok' ? 'oklch(65% 0.20 145)' : ($result->status === 'warning' ? 'oklch(75% 0.15 85)' : 'oklch(60% 0.20 25)') }};
                            ">
                                <div style="display: flex; align-items: center; gap: var(--space-md); margin-block-end: var(--space-sm);">
                                    <span style="
                                        width: 12px;
                                        height: 12px;
                                        border-radius: 50%;
                                        background: {{ $result->status === 'ok' ? 'oklch(65% 0.20 145)' : ($result->status === 'warning' ? 'oklch(75% 0.15 85)' : 'oklch(60% 0.20 25)') }};
                                    "></span>
                                    <h3 style="font-size: var(--text-lg); font-weight: 600; margin: 0;">
                                        {{ $result->label }}
                                    </h3>
                                    <span style="
                                        margin-inline-start: auto;
                                        padding: var(--space-xs) var(--space-sm);
                                        border-radius: var(--radius-sm);
                                        font-size: var(--text-sm);
                                        font-weight: 600;
                                        text-transform: uppercase;
                                        background: {{ $result->status === 'ok' ? 'color-mix(in oklch, oklch(65% 0.20 145) 15%, transparent)' : ($result->status === 'warning' ? 'color-mix(in oklch, oklch(75% 0.15 85) 15%, transparent)' : 'color-mix(in oklch, oklch(60% 0.20 25) 15%, transparent)') }};
                                        color: {{ $result->status === 'ok' ? 'oklch(45% 0.20 145)' : ($result->status === 'warning' ? 'oklch(55% 0.15 85)' : 'oklch(40% 0.20 25)') }};
                                    ">
                                        {{ $result->status === 'ok' ? 'OK' : ($result->status === 'warning' ? 'Warnung' : 'Fehler') }}
                                    </span>
                                </div>

                                @if($result->notificationMessage || $result->shortSummary)
                                    <p style="margin: 0; color: var(--text-secondary); font-size: var(--text-base);">
                                        {{ $result->notificationMessage ?? $result->shortSummary }}
                                    </p>
                                @endif

                                @if($result->meta && count($result->meta) > 0)
                                    <details style="margin-block-start: var(--space-md);">
                                        <summary style="cursor: pointer; color: var(--text-secondary); font-size: var(--text-sm);">
                                            Details anzeigen
                                        </summary>
                                        <div style="margin-block-start: var(--space-sm); padding: var(--space-sm); background: color-mix(in oklch, var(--text-primary) 5%, transparent); border-radius: var(--radius-sm);">
                                            <pre style="margin: 0; font-size: var(--text-sm); overflow-x: auto;">{{ json_encode($result->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </details>
                                @endif
                            </div>
                        @endforeach

                        <div style="text-align: center; margin-block-start: var(--space-2xl);">
                            <button
                                onclick="runHealthChecks()"
                                class="btn btn--primary"
                                id="refreshButton"
                            >
                                Prüfungen erneut ausführen
                            </button>
                        </div>
                    </div>
                @else
                    <div style="text-align: center; padding: var(--space-3xl); background: var(--bg-elevated); border-radius: var(--radius-lg); max-width: 600px; margin: 0 auto;">
                        <p style="font-size: var(--text-lg); color: var(--text-secondary);">
                            Noch keine Health Checks durchgeführt.
                        </p>
                        <button
                            onclick="runHealthChecks()"
                            class="btn btn--primary"
                            style="margin-block-start: var(--space-lg);"
                            id="refreshButton"
                        >
                            Health Checks ausführen
                        </button>
                    </div>
                @endif
            </div>
        </section>
    </main>

    <script>
        async function runHealthChecks() {
            const button = document.getElementById('refreshButton');
            const originalText = button.textContent;
            button.textContent = 'Prüfungen werden ausgeführt...';
            button.disabled = true;

            try {
                const response = await fetch('{{ route("health.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Fehler beim Ausführen der Health Checks');
                    button.textContent = originalText;
                    button.disabled = false;
                }
            } catch (error) {
                alert('Fehler beim Ausführen der Health Checks');
                button.textContent = originalText;
                button.disabled = false;
            }
        }
    </script>
@endsection
