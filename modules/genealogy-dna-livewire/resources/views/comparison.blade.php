<div>
    <form wire:submit="compare" aria-label="Compare DNA kits">
        <label for="genealogy-dna-kit-a">First kit</label>
        <select id="genealogy-dna-kit-a" wire:model="kitA" required>
            <option value="">Select a kit</option>
            @foreach ($kits as $kit)<option value="{{ $kit->id }}">{{ $kit->name }}</option>@endforeach
        </select>
        <label for="genealogy-dna-kit-b">Second kit</label>
        <select id="genealogy-dna-kit-b" wire:model="kitB" required>
            <option value="">Select a kit</option>
            @foreach ($kits as $kit)<option value="{{ $kit->id }}">{{ $kit->name }}</option>@endforeach
        </select>
        @error('kitA') <p role="alert">{{ $message }}</p> @enderror
        @error('kitB') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit" wire:loading.attr="disabled">Compare kits</button>
    </form>
    @if ($result !== null)
        <output aria-label="DNA comparison result">{{ $result['predicted_relationship'] ?? 'Comparison complete' }}</output>
    @endif
</div>
