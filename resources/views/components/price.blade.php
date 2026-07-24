{{--
    One money figure, carrying every currency it can be shown in (#1636).

    The charge currency is rendered as the text, so a reader with no stored choice
    — or no JavaScript at all — sees exactly what they saw before this feature and
    exactly what Stripe will charge. x-currency-switcher's script rewrites the text
    from the data-* attributes when a currency has been chosen.

    Expects $amounts keyed by upper-case currency code, charge currency first —
    i.e. SubscriptionService::displayAmounts().
--}}
@props(['amounts'])

<span data-price @foreach ($amounts as $code => $formatted) data-{{ strtolower($code) }}="{{ $formatted }}" @endforeach {{ $attributes }}>{{ $amounts[array_key_first($amounts)] }}</span>
