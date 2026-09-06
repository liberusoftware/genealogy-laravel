<x-filament-panels::page>
    <div class="space-y-8">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-6 py-8 text-white shadow-lg sm:px-10">
            <div class="relative z-10 max-w-2xl space-y-4">
                <p class="text-sm font-medium uppercase tracking-[0.18em] text-primary-100">Your family story</p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    Start with one person, then follow the clues.
                </h1>
                <p class="max-w-xl text-base leading-7 text-primary-100">
                    Build a trustworthy family history with sources, places, media, and collaboration all in one calm workspace.
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <x-filament::button
                        tag="a"
                        :href="\Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource::getUrl('create')"
                        color="white"
                        icon="heroicon-o-plus"
                    >
                        Create your first tree
                    </x-filament::button>
                    <x-filament::button
                        tag="a"
                        :href="route('filament.app.pages.workspace-setup')"
                        color="gray"
                        outlined
                        icon="heroicon-o-sparkles"
                    >
                        Review setup
                    </x-filament::button>
                </div>
            </div>
            <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full border-[24px] border-white/10"></div>
            <div class="absolute -bottom-24 right-20 h-52 w-52 rounded-full border-[18px] border-white/10"></div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Quick actions">
            @php
                $quickActions = [
                    [
                        'title' => 'Add a person',
                        'description' => 'Capture what you already know.',
                        'icon' => 'heroicon-o-user-plus',
                        'url' => \Liberu\Genealogy\People\Filament\Resources\PersonResource::getUrl('create'),
                    ],
                    [
                        'title' => 'Import records',
                        'description' => 'Bring in a GEDCOM or research file.',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'url' => \Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource::getUrl('create'),
                    ],
                    [
                        'title' => 'Plan research',
                        'description' => 'Turn a question into a next step.',
                        'icon' => 'heroicon-o-light-bulb',
                        'url' => \Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource::getUrl('create'),
                    ],
                    [
                        'title' => 'Invite family',
                        'description' => 'Share the work with people you trust.',
                        'icon' => 'heroicon-o-user-group',
                        'url' => \Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource::getUrl('create'),
                    ],
                ];
            @endphp

            @foreach ($quickActions as $action)
                <a href="{{ $action['url'] }}" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="rounded-lg bg-primary-50 p-2.5 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                            <x-filament::icon :icon="$action['icon']" class="h-5 w-5" />
                        </div>
                        <x-filament::icon icon="heroicon-m-arrow-up-right" class="h-4 w-4 text-gray-400 transition group-hover:text-primary-500" />
                    </div>
                    <h2 class="mt-4 font-semibold text-gray-950 dark:text-white">{{ $action['title'] }}</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $action['description'] }}</p>
                </a>
            @endforeach
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-primary-600 dark:text-primary-300">A better research habit</p>
                        <h2 class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">Keep every discovery connected</h2>
                    </div>
                    <x-filament::icon icon="heroicon-o-link" class="h-6 w-6 text-primary-500" />
                </div>
                <div class="mt-6 space-y-4">
                    @foreach ([['title' => 'Record the person', 'text' => 'Start with names, dates, and relationships.'], ['title' => 'Attach the evidence', 'text' => 'Link each claim to a source or citation.'], ['title' => 'Leave a clear next step', 'text' => 'Make it easy for your future self or a collaborator to continue.']] as $step => $item)
                        <div class="flex gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700 dark:bg-primary-500/20 dark:text-primary-200">{{ $step + 1 }}</span>
                            <div>
                                <h3 class="font-medium text-gray-950 dark:text-white">{{ $item['title'] }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Workspace health</p>
                <h2 class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">You are in control</h2>
                <ul class="mt-5 space-y-4 text-sm">
                    <li class="flex items-start gap-3"><x-filament::icon icon="heroicon-o-shield-check" class="mt-0.5 h-5 w-5 text-success-500" /><span><strong class="font-medium text-gray-950 dark:text-white">Private by default</strong><br><span class="text-gray-600 dark:text-gray-400">Choose what your family shares.</span></span></li>
                    <li class="flex items-start gap-3"><x-filament::icon icon="heroicon-o-document-check" class="mt-0.5 h-5 w-5 text-success-500" /><span><strong class="font-medium text-gray-950 dark:text-white">Evidence-first</strong><br><span class="text-gray-600 dark:text-gray-400">Keep sources beside the story they support.</span></span></li>
                    <li class="flex items-start gap-3"><x-filament::icon icon="heroicon-o-arrow-path" class="mt-0.5 h-5 w-5 text-success-500" /><span><strong class="font-medium text-gray-950 dark:text-white">Always portable</strong><br><span class="text-gray-600 dark:text-gray-400">Your research remains exportable.</span></span></li>
                </ul>
            </div>
        </section>
    </div>
</x-filament-panels::page>
