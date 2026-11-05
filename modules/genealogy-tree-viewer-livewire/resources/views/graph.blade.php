@assets
    @vite('resources/css/genealogy-tree.css')
@endassets

<section class="space-y-6 text-gray-900 dark:text-gray-100" aria-label="Family tree explorer">
    <header>
        <h2 class="text-2xl font-semibold">Explore your family</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Start with someone you know, explore their relatives, and grow your family story.</p>
    </header>

    @auth
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <label for="{{ $this->getId() }}-search" class="block text-sm font-medium">Find a person</label>
            <input id="{{ $this->getId() }}-search" type="search" wire:model.live.debounce.300ms="search" maxlength="100" placeholder="Search by first or last name" class="mt-2 w-full rounded-lg border border-gray-300 bg-transparent p-3 dark:border-gray-600" aria-describedby="{{ $this->getId() }}-search-help">
            <p id="{{ $this->getId() }}-search-help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Type at least 2 characters. Birth dates help distinguish people with the same name.</p>
            @if (mb_strlen(trim($search)) >= 2)
                <ul class="mt-3 grid gap-2 sm:grid-cols-2" aria-label="Matching people">
                    @forelse ($people as $person)
                        <li wire:key="search-person-{{ $person->id }}">
                            <button type="button" wire:click="navigateTo('{{ $person->id }}')" wire:loading.attr="disabled" class="w-full rounded-lg border border-gray-200 p-3 text-left hover:border-indigo-500 focus-visible:outline-2 focus-visible:outline-indigo-500 dark:border-gray-700">
                                <span class="block font-medium">{{ $person->display_name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $person->birth_date?->format('Y') ?? 'Birth date unknown' }} · {{ $person->isLiving() ? 'Living' : 'Deceased' }}</span>
                            </button>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 dark:text-gray-400">No matching people. Try another name or check the spelling.</li>
                    @endforelse
                </ul>
                @if ($people->count() === 10)
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Showing the first 10 matches. Add more of the name to narrow your search.</p>
                @endif
            @endif
        </div>
    @endauth

    <form wire:submit="loadGraph" class="space-y-4 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-40 flex-1">
                <label for="{{ $this->getId() }}-view" class="block text-sm font-medium">Show relatives</label>
                <select id="{{ $this->getId() }}-view" wire:model="view" class="mt-2 w-full rounded-lg border border-gray-300 bg-white p-3 dark:border-gray-600 dark:bg-gray-900">
                    <option value="chart">All family</option>
                    <option value="pedigree">Ancestors</option>
                    <option value="descendants">Descendants</option>
                </select>
            </div>
            <div>
                <label for="{{ $this->getId() }}-generations" class="block text-sm font-medium">Generations</label>
                <input id="{{ $this->getId() }}-generations" type="number" min="0" max="12" wire:model="generations" class="mt-2 w-24 rounded-lg border border-gray-300 bg-transparent p-3 dark:border-gray-600">
            </div>
            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-5 py-3 font-medium text-white hover:bg-indigo-500 disabled:opacity-50">Update tree</button>
        </div>
        <div class="flex flex-wrap gap-4 text-sm">
            @auth
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="includeLiving"> Include living people</label>
            @endauth
            <label class="flex items-center gap-2"><input type="checkbox" wire:model="includeSiblings"> Include siblings</label>
        </div>
        <details class="text-sm">
            <summary class="cursor-pointer text-gray-600 dark:text-gray-400">Advanced options</summary>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <label>Person ID<input type="text" wire:model="personId" class="mt-1 block w-full rounded-lg border border-gray-300 bg-transparent p-2 dark:border-gray-600"></label>
                <label>Maximum people<input type="number" min="100" max="5000" wire:model="maxNodes" class="mt-1 block w-full rounded-lg border border-gray-300 bg-transparent p-2 dark:border-gray-600"></label>
            </div>
        </details>
        @foreach (['personId', 'generations', 'view', 'maxNodes', 'includeLiving', 'includeSiblings'] as $field)
            @error($field)<p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        @endforeach
    </form>

    <p wire:loading.delay role="status" class="text-sm text-indigo-600 dark:text-indigo-400">Updating your family tree…</p>
    @if ($notice !== '')
        <p role="status" class="rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-950 dark:text-green-200">{{ $notice }}</p>
    @endif

    @if ($data === [])
        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
            <h3 class="font-semibold">Your family story starts with a person</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Find someone above to explore their family. You can then add parents, children, and partners.</p>
        </div>
    @else
        <div class="space-y-4" wire:loading.class="opacity-50">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-semibold">{{ $data['root']['name'] }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ count($data['nodes']) }} people · {{ count($data['ancestors']) }} ancestors · {{ count($data['descendants']) }} descendants</p>
                </div>
                @if ($personId !== $startingPersonId)
                    <button type="button" wire:click="returnToStart" wire:loading.attr="disabled" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-600">Back to starting person</button>
                @endif
            </div>
            @if ($data['navigation']['truncated'] ?? false)
                <p role="status" class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-200">Some relatives are outside this view. Show fewer generations or increase the maximum people in Advanced options.</p>
            @endif
            <p class="text-sm text-gray-500 dark:text-gray-400">Select a person to explore their family.</p>
            @php
                $ancestorsById = collect($data['ancestors'])->keyBy('person.id');
                $descendantsById = collect($data['descendants'])->keyBy('person.id');
                $siblingsById = collect($data['siblings'])->keyBy('id');
                $partnersById = collect($data['partners'])->where('for_person_id', $data['root']['id'])->keyBy('person.id');
            @endphp
            <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" aria-label="Tree nodes">
                @foreach ($data['nodes'] as $node)
                    <li wire:key="genealogy-tree-node-{{ $node['id'] }}">
                        <button type="button" wire:click="navigateTo('{{ $node['id'] }}')" wire:loading.attr="disabled" class="h-full w-full rounded-xl border p-4 text-left hover:border-indigo-500 focus-visible:outline-2 focus-visible:outline-indigo-500 {{ $node['id'] === $data['root']['id'] ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' }}">
                            <span class="block font-semibold">{{ $node['name'] }}</span>
                            <span class="mt-1 block text-sm text-gray-600 dark:text-gray-400">{{ $node['birth_year'] ?? '?' }} – {{ $node['death_year'] ?? ($node['is_living'] ? 'Living' : '?') }}</span>
                            @php
                                $ancestor = $ancestorsById->get($node['id']);
                                $descendant = $descendantsById->get($node['id']);
                                $isSibling = $siblingsById->has($node['id']);
                                $isPartner = $partnersById->has($node['id']);
                            @endphp
                            <span class="mt-2 block text-xs font-medium text-indigo-600 dark:text-indigo-300">
                                @if ($node['id'] === $data['root']['id'])
                                    Current person
                                @elseif ($ancestor)
                                    {{ $ancestor['generation'] === 1 ? 'Parent' : 'Ancestor · Generation '.$ancestor['generation'] }}
                                @elseif ($descendant)
                                    {{ $descendant['generation'] === 1 ? 'Child' : 'Descendant · Generation '.$descendant['generation'] }}
                                @elseif ($isSibling)
                                    Sibling
                                @elseif ($isPartner)
                                    Partner
                                @else
                                    Extended family
                                @endif
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
            @if (count($data['nodes']) === 1)
                <p class="rounded-lg bg-gray-50 p-4 text-sm dark:bg-gray-800">No relatives are visible with these settings. Try All family, more generations, or include living people.</p>
            @endif
        </div>

        @auth
            <details class="rounded-xl border border-gray-200 p-4 dark:border-gray-700" wire:key="add-relative-{{ $data['root']['id'] }}">
                <summary class="cursor-pointer font-semibold">Add a relative of {{ $data['root']['name'] }}</summary>
                <form wire:submit="addRelative" class="mt-4 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Check the search above first to avoid adding someone twice. New people are private by default. Leave dates blank if unknown.</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm">Relationship to {{ $data['root']['name'] }}
                            <select wire:model="relativeType" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 dark:border-gray-600 dark:bg-gray-900"><option value="child">Child</option><option value="parent">Parent</option><option value="partner">Partner</option></select>
                        </label>
                        <label class="text-sm">First name (required)<input type="text" wire:model="givenName" required maxlength="255" class="mt-1 block w-full rounded-lg border border-gray-300 bg-transparent p-3 dark:border-gray-600"></label>
                        <label class="text-sm">Last name<input type="text" wire:model="familyName" maxlength="255" class="mt-1 block w-full rounded-lg border border-gray-300 bg-transparent p-3 dark:border-gray-600"></label>
                        <label class="text-sm">Birth date<input type="date" wire:model="birthDate" class="mt-1 block w-full rounded-lg border border-gray-300 bg-transparent p-3 dark:border-gray-600"></label>
                        <label class="text-sm">Death date<input type="date" wire:model="deathDate" class="mt-1 block w-full rounded-lg border border-gray-300 bg-transparent p-3 dark:border-gray-600"></label>
                    </div>
                    @foreach (['relativeType', 'givenName', 'familyName', 'birthDate', 'deathDate'] as $field)
                        @error($field)<p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    @endforeach
                    <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-5 py-3 font-medium text-white hover:bg-indigo-500 disabled:opacity-50">Save relative</button>
                </form>
            </details>
        @endauth
    @endif
</section>
