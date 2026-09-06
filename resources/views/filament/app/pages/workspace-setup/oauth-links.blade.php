<div class="space-y-4">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Connect an account for quick sign-in, or manage existing connections from your protected profile.
    </p>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach (\JoelButcher\Socialstream\Socialstream::providers() as $provider)
            @php($configured = filled(config('services.'.$provider['id'].'.client_id')) && filled(config('services.'.$provider['id'].'.client_secret')))
            <div class="rounded-xl border border-gray-200 p-3 dark:border-white/10">
                <a class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-outlined w-full justify-center {{ $configured ? '' : 'pointer-events-none opacity-50' }}" href="{{ route('oauth.redirect', ['provider' => $provider['id']]) }}" @if (! $configured) aria-disabled="true" tabindex="-1" @endif>
                    <x-socialstream-icons.provider-icon :provider="$provider['id']" class="h-5 w-5" />
                    <span>{{ $provider['buttonLabel'] }}</span>
                </a>
                <p class="mt-2 text-center text-xs {{ $configured ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">
                    {{ $configured ? 'Ready to connect' : 'Not configured by administrator' }}
                </p>
            </div>
        @endforeach
    </div>

    <a class="fi-link" href="{{ route('profile.show') }}">
        Manage connected accounts and security settings
    </a>
</div>
