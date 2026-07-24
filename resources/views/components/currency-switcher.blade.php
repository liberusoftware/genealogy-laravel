{{--
    Currency switcher + the attribution the ECB's terms require (#1636).

    Renders nothing at all when there is no estimate to offer — no rates, one
    currency, or the feature switched off — so every surface degrades to the
    charge-currency markup it had before.

    The choice is client-side only: no users.currency column, no cookie, no server
    round-trip. Figures for every currency are already in the page (x-price), so
    switching is a text swap.

    Expects $amounts (any displayAmounts() map — only its keys are used) and $date,
    the ECB rate date. The base currency is the first key.
--}}
@props(['amounts', 'date'])

@php
    $currencies = array_keys($amounts);
    $base = $currencies[0] ?? null;
@endphp

@if ($date && count($currencies) > 1)
    <div {{ $attributes->merge(['class' => 'text-label']) }}>
        <div class="inline-flex gap-0.5 rounded-md border border-rule bg-surface p-0.5" role="group" aria-label="Display currency">
            @foreach ($currencies as $code)
                <button type="button"
                        data-currency-option="{{ $code }}"
                        aria-pressed="{{ $code === $base ? 'true' : 'false' }}"
                        class="rounded px-2 py-1 text-xs tabular-nums text-ink-muted transition-colors duration-150 aria-pressed:bg-paper aria-pressed:font-semibold aria-pressed:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-registry-green">
                    {{ $code }}
                </button>
            @endforeach
        </div>

        {{-- Mandatory, not decorative: the ECB requires citation as source, and
             because the cross-rate out of the charge currency is computed here it
             also requires the figure be stated as modified. The date is required
             too — the feed is routinely a day or more old (weekends, TARGET
             holidays), and it is the rate's date, not today's. --}}
        <p class="mt-2 text-xs text-ink-muted">
            Charged in {{ $base }}. Other currencies are an estimate converted from
            {{ $base }} at the euro reference rate of {{ $date }} — source: European
            Central Bank, cross-rate calculated by us.
        </p>
    </div>

    <script>
        // Not bundled through Vite: this has to work on the two public Blade pages
        // and inside the Filament panel, which do not share an entrypoint.
        (() => {
            if (window.__currencySwitcher) return;
            window.__currencySwitcher = true;

            const KEY = 'display-currency';

            const apply = () => {
                const chosen = localStorage.getItem(KEY);
                if (!chosen) return;

                document.querySelectorAll('[data-price]').forEach((el) => {
                    const value = el.dataset[chosen.toLowerCase()];
                    // Only write on a real change — a MutationObserver is watching,
                    // and an unconditional assignment would re-trigger it forever.
                    if (value && el.textContent !== value) el.textContent = value;
                });

                document.querySelectorAll('[data-currency-option]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(button.dataset.currencyOption === chosen));
                });
            };

            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-currency-option]');
                if (!button) return;

                localStorage.setItem(KEY, button.dataset.currencyOption);
                apply();
            });

            // Livewire morphs the Filament page back to server HTML, which is always
            // the charge currency. Observing the DOM re-applies the choice without
            // binding to a particular Livewire version's hook names.
            let queued = false;
            new MutationObserver(() => {
                if (queued) return;
                queued = true;
                requestAnimationFrame(() => { queued = false; apply(); });
            }).observe(document.documentElement, { childList: true, subtree: true });

            apply();
        })();
    </script>
@endif
