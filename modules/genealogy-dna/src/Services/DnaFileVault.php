<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/** Encrypts raw genetic data at rest and keeps plaintext in memory only. */
final class DnaFileVault
{
    public function encrypt(string $plaintext): string
    {
        return Crypt::encryptString($plaintext);
    }

    /** Supports legacy plaintext files while all new writes are encrypted. */
    public function decrypt(string $ciphertext): string
    {
        try {
            return Crypt::decryptString($ciphertext);
        } catch (DecryptException) {
            return $ciphertext;
        }
    }

    public function store(string $plaintext, string $path, string $disk = 'private'): void
    {
        Storage::disk($disk)->put($path, $this->encrypt($plaintext));
    }

    public function read(string $path, string $disk = 'private'): string
    {
        $storage = Storage::disk($disk);
        if (! $storage->exists($path)) {
            return '';
        }

        return $this->decrypt((string) $storage->get($path));
    }

    public function delete(string $path, string $disk = 'private'): void
    {
        Storage::disk($disk)->delete($path);
    }
}
