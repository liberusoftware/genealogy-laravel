# Genealogy Media

This independent Liberu module owns the provider-neutral **Genealogy Media** capability.

It exposes a stable capability descriptor and service provider. Domain persistence, authorization, tenancy, jobs, and presentation adapters remain behind this package's public boundary; the matching API, Filament, and Livewire packages are optional adapters and never become core dependencies.

- Composer package: `liberusoftware/module-genealogy-media`
- Module installer name: `genealogy-media`
- Category: capability
- PHP/Laravel: PHP 8.5 / Laravel 13

Facial recognition is an optional integration: applications must bind a `FaceRecognitionProvider` explicitly. Analysis stores reviewable bounding-box tags only and fails closed when no provider is configured.

OCR/handwriting transcription is similarly opt-in through `TranscriptionProvider`; an unconfigured provider never writes placeholder text or confidence values.

The package is designed for the Liberu Composer installer and must not depend on an application's `App\\` classes.
