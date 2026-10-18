<?php

it('memakai skema https saat proxy meneruskan permintaan https', function () {
    $sitemap = (string) $this->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('/sitemap.xml')
        ->assertOk()
        ->getContent();

    expect($sitemap)
        ->toContain('<loc>https://')
        ->not->toContain('<loc>http://');
});

it('tetap memakai http saat tidak ada proxy yang meneruskan skema', function () {
    $sitemap = (string) $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($sitemap)->toContain('<loc>http://');
});
