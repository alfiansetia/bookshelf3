<?php

it('menyediakan berkas pendukung PWA', function () {
    expect(public_path('manifest.webmanifest'))->toBeReadableFile()
        ->and(public_path('sw.js'))->toBeReadableFile()
        ->and(public_path('offline.html'))->toBeReadableFile()
        ->and(public_path('icons/icon-192.png'))->toBeReadableFile()
        ->and(public_path('icons/icon-512.png'))->toBeReadableFile()
        ->and(public_path('icons/icon-maskable-512.png'))->toBeReadableFile()
        ->and(public_path('apple-touch-icon.png'))->toBeReadableFile();
});

it('mendefinisikan manifest yang bisa dipasang', function () {
    $manifest = json_decode(
        (string) file_get_contents(public_path('manifest.webmanifest')),
        true,
    );

    expect($manifest)->toBeArray()
        ->and($manifest['start_url'])->toBe('/')
        ->and($manifest['scope'])->toBe('/')
        ->and($manifest['display'])->toBe('standalone')
        ->and($manifest['icons'])->toHaveCount(3);

    $purposes = array_column($manifest['icons'], 'purpose');

    expect($purposes)->toContain('maskable');
});
