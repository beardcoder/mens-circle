@props (['variants'])

<div
  class="fi-messenger-texts"
  x-data="{
    copied: null,
    async copy(key, text) {
      try {
        await navigator.clipboard.writeText(text);
      } catch (e) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
      }
      this.copied = key;
      setTimeout(() => {
        if (this.copied === key) this.copied = null;
      }, 2000);
    },
  }"
>
  <div class="grid gap-4">
    @foreach ($variants as $key => $variant)
      <div
        class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 overflow-hidden"
      >
        <div
          class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 dark:border-white/10"
        >
          <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
            {{ $variant['label'] }}
          </h3>

          <x-filament::button
            size="sm"
            color="gray"
            icon="heroicon-o-clipboard-document"
            x-on:click="copy(@js($key), @js($variant['text']))"
          >
            <span x-show="copied !== @js($key)">Kopieren</span>
            <span x-show="copied === @js($key)" x-cloak>Kopiert</span>
          </x-filament::button>
        </div>

        <pre
          class="px-4 py-3 text-sm leading-relaxed whitespace-pre-wrap break-words font-sans text-gray-800 dark:text-gray-200 m-0 bg-transparent"
          >{{ $variant['text'] }}</pre
        >
      </div>
    @endforeach
  </div>
</div>
