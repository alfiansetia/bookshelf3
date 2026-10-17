<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/security';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Keamanan', href: edit() }],
    },
});
</script>

<template>
    <Head title="Keamanan akun" />

    <h1 class="sr-only">Pengaturan keamanan</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Keamanan akun"
            description="Akun Rak Buku hanya bisa diakses lewat Google."
        />

        <div class="flex gap-4 rounded-lg border border-border bg-muted/40 p-4">
            <ShieldCheck class="mt-0.5 size-5 shrink-0 text-primary" />
            <div class="space-y-1 text-sm">
                <p class="font-medium">Tidak ada kata sandi di Rak Buku.</p>
                <p class="text-muted-foreground">
                    Kami tidak menyimpan kata sandi maupun kode pemulihan.
                    Seluruh proses masuk diverifikasi langsung oleh Google,
                    termasuk verifikasi dua langkah milikmu.
                </p>
            </div>
        </div>

        <div class="space-y-3 text-sm text-muted-foreground">
            <p>
                Ingin mengganti kata sandi, melihat perangkat yang pernah masuk,
                atau mengaktifkan verifikasi dua langkah? Kelola semuanya dari
                akun Google kamu.
            </p>
            <Button as-child variant="outline">
                <a
                    href="https://myaccount.google.com/security"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Buka keamanan Google
                </a>
            </Button>
        </div>
    </div>
</template>
