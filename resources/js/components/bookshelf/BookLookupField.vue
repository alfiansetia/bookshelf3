<script setup lang="ts">
import { ref } from 'vue';
import { BookOpen, Check, Loader2, Search, Sparkles, X } from '@lucide/vue';

export type BookLookupItem = {
    key: string | null;
    title: string;
    author: string | null;
    authors: string[];
    publisher: string | null;
    published_year: number | null;
    total_pages: number | null;
    isbn: string | null;
    language: string | null;
    cover_id: number | null;
    cover_url: string | null;
    cover_large_url: string | null;
};

const emit = defineEmits<{
    select: [item: BookLookupItem];
}>();

const props = defineProps<{
    lookupUrl: string;
}>();

const query = ref('');
const results = ref<BookLookupItem[]>([]);
const source = ref<'db' | 'api' | null>(null);
const provider = ref<'google' | 'openlibrary'>('google');
const loading = ref(false);
/** True bila request terakhir gagal (network/server error). */
const failed = ref(false);
const selectedKey = ref<string | null>(null);
/** Item yang sedang dipakai. Bila ada, list menciut jadi ringkasan. */
const selectedItem = ref<BookLookupItem | null>(null);
/** List disembunyikan manual via tombol tutup (tetap bisa dibuka lagi). */
const listHidden = ref(false);

let aborter: AbortController | null = null;
const clientCache = new Map<string, BookLookupItem[]>();

function itemKey(item: BookLookupItem, i: number): string {
    return `${item.key ?? item.title}-${i}`;
}

async function runSearch(term: string): Promise<void> {
    const q = term.trim();

    if (q.length < 3) {
        results.value = [];
        source.value = null;
        return;
    }

    const cacheKey = q.toLowerCase();

    if (clientCache.has(cacheKey)) {
        results.value = clientCache.get(cacheKey) ?? [];
        source.value = 'db';
        failed.value = false;
        return;
    }

    aborter?.abort();
    aborter = new AbortController();
    loading.value = true;
    failed.value = false;

    try {
        const url = new URL(props.lookupUrl, window.location.origin);
        url.searchParams.set('q', q);
        const res = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            signal: aborter.signal,
        });

        if (!res.ok) {
            failed.value = true;
            return;
        }

        const json = (await res.json()) as {
            data: BookLookupItem[];
            source: 'db' | 'api';
            provider: 'google' | 'openlibrary';
        };

        results.value = json.data ?? [];
        source.value = json.source ?? null;
        provider.value = json.provider ?? 'google';
        clientCache.set(cacheKey, results.value);
    } catch (e) {
        if ((e as Error).name !== 'AbortError') {
            results.value = [];
            failed.value = true;
        }
    } finally {
        loading.value = false;
    }
}

/**
 * Trigger manual via tombol Cari / Enter. Tidak ada auto-fetch saat
 * mengetik supaya typo + koreksi tidak menghabiskan quota API.
 */
function triggerSearch(): void {
    if (query.value.trim().length < 3 || loading.value) {
        return;
    }

    // Query baru = pilihan lama batal, list dibuka lagi.
    selectedItem.value = null;
    selectedKey.value = null;
    listHidden.value = false;

    void runSearch(query.value);
}

/**
 * Pilih card: form terisi, list menciut jadi ringkasan supaya field
 * wajib (judul, penulis) langsung terlihat tanpa scroll jauh.
 */
function choose(item: BookLookupItem, i: number): void {
    selectedKey.value = itemKey(item, i);
    selectedItem.value = item;
    listHidden.value = true;
    emit('select', item);
}

function clearAll(): void {
    query.value = '';
    results.value = [];
    source.value = null;
    selectedKey.value = null;
    selectedItem.value = null;
    listHidden.value = false;
    failed.value = false;
}
</script>

<template>
    <div class="space-y-3">
        <div class="grid gap-2">
            <label
                for="book-lookup"
                class="flex items-center gap-1.5 text-sm font-medium"
            >
                Cari data buku
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-semibold text-primary"
                >
                    <Sparkles class="size-3" />
                    {{ provider === 'google' ? 'Google Books' : 'OpenLibrary' }}
                </span>
            </label>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <input
                        id="book-lookup"
                        v-model="query"
                        type="text"
                        autocomplete="off"
                        placeholder="Ketik judul, mis. Bumi Manusia… (min. 3 huruf)"
                        class="w-full rounded-md border border-input bg-background py-2 pr-9 pl-9 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus:border-primary/50 focus:ring-2 focus:ring-primary/20"
                        @keydown.enter.prevent="triggerSearch"
                    />
                    <button
                        v-if="query"
                        type="button"
                        aria-label="Bersihkan pencarian"
                        class="absolute top-1/2 right-2.5 -translate-y-1/2 rounded p-0.5 text-muted-foreground hover:text-foreground"
                        @click="clearAll"
                    >
                        <X class="size-4" />
                    </button>
                </div>
                <button
                    type="button"
                    :disabled="query.trim().length < 3 || loading"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-xs transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="triggerSearch"
                >
                    <Loader2
                        v-if="loading"
                        class="size-4 animate-spin"
                    />
                    <Search v-else class="size-4" />
                    Cari
                </button>
            </div>
            <div
                aria-live="polite"
                class="flex min-h-5 items-center gap-1.5 text-xs"
            >
                <template v-if="loading">
                    <Loader2 class="size-3.5 animate-spin text-primary" />
                    <span class="font-medium text-foreground">
                        Mencari “{{ query.trim() }}”…
                    </span>
                </template>
                <template v-else-if="failed">
                    <span class="font-medium text-destructive">
                        Pencarian gagal. Periksa koneksi lalu tekan Cari
                        lagi.
                    </span>
                </template>
                <template v-else-if="results.length > 0">
                    <Check class="size-3.5 text-emerald-600" />
                    <span class="text-muted-foreground">
                        Ditemukan {{ results.length }} hasil.
                        <span v-if="source === 'db'"
                            >Dari database lokal.</span
                        >
                        <span v-else-if="source === 'api'"
                            >Dari
                            {{
                                provider === 'google'
                                    ? 'Google Books'
                                    : 'OpenLibrary'
                            }}, tersimpan ke database.</span
                        >
                    </span>
                </template>
                <template v-else>
                    <span class="text-muted-foreground">
                        Ketik min. 3 huruf lalu tekan Cari (atau Enter).
                        Klik “Pakai” pada hasilnya untuk mengisi form
                        otomatis.
                    </span>
                </template>
            </div>
        </div>

        <!-- Skeleton saat loading dan belum ada hasil lama -->
        <div
            v-if="loading && results.length === 0"
            class="grid gap-2 sm:grid-cols-2"
            aria-hidden="true"
        >
            <div
                v-for="n in 4"
                :key="n"
                class="flex animate-pulse gap-3 rounded-xl border border-border/70 bg-card p-3"
            >
                <div class="h-20 w-14 shrink-0 rounded-md bg-muted" />
                <div class="flex-1 space-y-2 py-1">
                    <div class="h-3.5 w-3/4 rounded bg-muted" />
                    <div class="h-3 w-1/2 rounded bg-muted" />
                    <div class="h-6 w-16 rounded-full bg-muted" />
                </div>
            </div>
        </div>

        <!-- Ringkasan pilihan: list menciut supaya field wajib langsung terlihat -->
        <div
            v-if="selectedItem"
            class="flex items-center gap-3 rounded-xl border border-primary/40 bg-primary/5 p-3"
        >
            <span
                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/15"
            >
                <Check class="size-4 text-primary" />
            </span>
            <p class="min-w-0 flex-1 truncate text-sm">
                <span class="font-medium text-foreground">
                    {{ selectedItem.title }}
                </span>
                <span class="text-muted-foreground">
                    · {{ selectedItem.author ?? 'Penulis tak dikenal' }}
                </span>
            </p>
            <button
                type="button"
                class="shrink-0 rounded-full border border-border/70 px-3 py-1 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground"
                @click="listHidden = !listHidden"
            >
                {{ listHidden ? 'Lihat hasil lain' : 'Sembunyikan' }}
            </button>
        </div>

        <!-- List card: in-flow (tidak menutup field), tinggi dibatasi + scroll -->
        <div
            v-if="results.length > 0 && (!selectedItem || !listHidden)"
            class="grid max-h-[560px] gap-2 overflow-y-auto pr-0.5 transition-opacity sm:grid-cols-2"
            :class="loading ? 'pointer-events-none opacity-60' : ''"
        >
            <article
                v-for="(item, i) in results"
                :key="itemKey(item, i)"
                class="flex gap-3 rounded-xl border p-3 transition-colors"
                :class="
                    selectedKey === itemKey(item, i)
                        ? 'border-primary/60 bg-primary/5'
                        : 'border-border/70 bg-card'
                "
            >
                <img
                    v-if="item.cover_url"
                    :src="item.cover_url"
                    :alt="item.title"
                    loading="lazy"
                    class="h-20 w-14 shrink-0 rounded-md border border-border/60 object-cover"
                />
                <span
                    v-else
                    class="flex h-20 w-14 shrink-0 items-center justify-center rounded-md border border-border/60 bg-muted"
                >
                    <BookOpen class="size-5 text-muted-foreground" />
                </span>
                <div class="min-w-0 flex-1 space-y-1">
                    <p
                        class="line-clamp-2 text-sm leading-snug font-medium text-foreground"
                    >
                        {{ item.title }}
                    </p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{ item.author ?? 'Penulis tak dikenal' }}
                        <template v-if="item.published_year">
                            · {{ item.published_year }}
                        </template>
                    </p>
                    <p
                        v-if="item.publisher || item.total_pages"
                        class="truncate text-xs text-muted-foreground"
                    >
                        <template v-if="item.publisher">{{
                            item.publisher
                        }}</template>
                        <template v-if="item.total_pages">
                            · {{ item.total_pages }} hlm
                        </template>
                    </p>
                    <button
                        type="button"
                        class="mt-1 inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium transition-colors"
                        :class="
                            selectedKey === itemKey(item, i)
                                ? 'border-primary/60 bg-primary text-primary-foreground'
                                : 'border-border/70 text-foreground hover:border-primary/50 hover:text-primary'
                        "
                        @click="choose(item, i)"
                    >
                        <Check
                            v-if="selectedKey === itemKey(item, i)"
                            class="size-3"
                        />
                        {{
                            selectedKey === itemKey(item, i)
                                ? 'Dipakai'
                                : 'Pakai'
                        }}
                    </button>
                </div>
            </article>
        </div>

        <p
            v-else-if="query.trim().length >= 3 && !loading"
            class="rounded-xl border border-dashed border-border/70 p-4 text-center text-xs text-muted-foreground"
        >
            Tidak ada hasil untuk “{{ query.trim() }}”. Coba kata kunci lain
            atau isi manual di bawah.
        </p>
    </div>
</template>
