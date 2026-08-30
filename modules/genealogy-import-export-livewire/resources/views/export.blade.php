<form wire:submit="export">
    <label for="genealogy-export-name">Export name</label>
    <input id="genealogy-export-name" type="text" wire:model="name" required>

    <label for="genealogy-export-format">Format</label>
    <select id="genealogy-export-format" wire:model="format" required>
        <option value="gedcom">GEDCOM 5.5.1</option>
        <option value="gedcom-7">GEDCOM 7.0</option>
        <option value="gedcom-x">GEDCOM X JSON</option>
        <option value="gramps-xml">GRAMPS XML</option>
    </select>

    <button type="submit">Export genealogy</button>
</form>
