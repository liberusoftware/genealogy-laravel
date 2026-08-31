<div class="space-y-4">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Connect an account for quick sign-in, or manage existing connections from your protected profile.
    </p>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach (\JoelButcher\Socialstream\Socialstream::providers() as $provider)
            <a class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-outlined justify-center" href="{{ route('oauth.redirect', ['provider' => $provider['id']]) }}">
                <x-socialstream-icons.provider-icon :provider="$provider['id']" class="h-5 w-5" />
                <span>{{ $provider['buttonLabel'] }}</span>
            </a>
        @endforeach
    </div>

    <a class="fi-link" href="{{ route('profile.show') }}">
        Manage connected accounts and security settings
    </a>
</div>
