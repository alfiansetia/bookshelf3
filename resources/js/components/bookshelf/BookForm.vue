<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Heart, Loader2, Save, X } from '@lucide/vue';
import CoverUploadField from '@/components/bookshelf/CoverUploadField.vue';
import DatePicker from '@/components/bookshelf/DatePicker.vue';
import StarRatingInput from '@/components/bookshelf/StarRatingInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { iconByName } from '@/lib/icons';
import { statusStyle } from '@/lib/statusStyles';
import { cn } from '@/lib/utils';
import { index as booksIndex, store, update } from '@/routes/books';
import type {
    Book,
    BookFormValues,
    BookStatus,
    LanguageOption,
    StatusOption,
} from '@/types';

const props = withDefaults(
    defineProps<{
        book?: Book | null;
        statuses: StatusOption[];
        languages: LanguageOption[];
        coverMaxKilobytes?: number;
    }>(),
    {
        book: null,
        coverMaxKilobytes: 3072,
    },
);

const isEditing = computed(() => props.book !== null);

const form = useForm<BookFormValues>({
    title: props.book?.title ?? '',
    author: props.book?.author ?? '',
    publisher: props.book?.publisher ?? '',
    published_year: props.book?.published_year?.toString() ?? '',
    language: props.book?.language ?? props.languages[0]?.value ?? 'id',
    status: props.book?.status ?? 'want_to_read',
    rating: props.book?.rating ?? null,
    synopsis: props.book?.synopsis ?? '',
    review: props.book?.review ?? '',
    total_pages: props.book?.total_pages?.toString() ?? '',
    started_at: props.book?.started_at ?? '',
    finished_at: props.book?.finished_at ?? '',
    is_favorite: props.book?.is_favorite ?? false,
    cover: null,
    remove_cover: false,
});

const existingCover = ref<string | null>(props.book?.cover_url ?? null);

const synopsisMax = 6000;
const reviewMax = 6000;

function chooseStatus(value: BookStatus): void {
    form.status = value;
}

function onCoverChange(file: File | null): void {
    form.cover = file;

    if (file !== null) {
        form.remove_cover = false;
    }
}

function onCoverRemove(): void {
    form.cover = null;
    form.remove_cover = true;
    existingCover.value = null;
}

function submit(): void {
    if (props.book !== null) {
        form.put(update.url(props.book.id), { preserveScroll: true });

        return;
    }

    form.post(store.url());
}
</script>

<template>
    <form class="space-y-6" novalidate @submit.prevent="submit">
        <section
            class="space-y-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs sm:p-6"
        >
            <div class="space-y-1">
                <h2 class="font-display text-xl text-foreground">
                    {{ isEditing ? 'Ubah data buku' : 'Data buku' }}
                </h2>
                <p class="text-sm text-muted-foreground">
                    Isi informasi dasarnya dulu, sisanya bisa dilengkapi nanti.
                </p>
            </div>

            <CoverUploadField
                :model-value="form.cover"
                :existing-url="existingCover"
                :max-kilobytes="props.coverMaxKilobytes"
                @update:model-value="onCoverChange"
                @remove="onCoverRemove"
            />
            <InputError :message="form.errors.cover" />
            <InputError :message="form.errors.remove_cover" />

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="title">Judul buku</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        autocomplete="off"
                        placeholder="Contoh: Bumi Manusia"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="author">Penulis</Label>
                    <Input
                        id="author"
                        v-model="form.author"
                        type="text"
                        required
                        autocomplete="off"
                        placeholder="Contoh: Pramoedya Ananta Toer"
                    />
                    <InputError :message="form.errors.author" />
                </div>

                <div class="grid gap-2">
                    <Label for="publisher">Penerbit</Label>
                    <Input
                        id="publisher"
                        v-model="form.publisher"
                        type="text"
                        autocomplete="off"
                        placeholder="Opsional"
                    />
                    <InputError :message="form.errors.publisher" />
                </div>

                <div class="grid gap-2">
                    <Label for="published_year">Tahun terbit</Label>
                    <Input
                        id="published_year"
                        v-model="form.published_year"
                        type="number"
                        inputmode="numeric"
                        min="1000"
                        max="2100"
                        placeholder="Contoh: 1980"
                    />
                    <InputError :message="form.errors.published_year" />
                </div>

                <div class="grid gap-2">
                    <Label for="language">Bahasa buku</Label>
                    <Select v-model="form.language">
                        <SelectTrigger id="language" class="w-full">
                            <SelectValue placeholder="Pilih bahasa" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="language in props.languages"
                                :key="language.value"
                                :value="language.value"
                            >
                                {{ language.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        Bahasa dipakai pengunjung untuk menyaring rak kamu.
                    </p>
                    <InputError :message="form.errors.language" />
                </div>

                <div class="grid gap-2">
                    <Label for="total_pages">Jumlah halaman</Label>
                    <Input
                        id="total_pages"
                        v-model="form.total_pages"
                        type="number"
                        inputmode="numeric"
                        min="1"
                        placeholder="Opsional"
                    />
                    <InputError :message="form.errors.total_pages" />
                </div>
            </div>
        </section>

        <section
            class="space-y-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs sm:p-6"
        >
            <div class="space-y-1">
                <h2 class="font-display text-xl text-foreground">
                    Status & rating
                </h2>
                <p class="text-sm text-muted-foreground">
                    Status menentukan buku ini berdiri di rak yang mana.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <button
                    v-for="status in props.statuses"
                    :key="status.value"
                    type="button"
                    :aria-pressed="form.status === status.value"
                    :class="
                        cn(
                            'flex items-start gap-3 rounded-xl border p-3 text-left transition-colors',
                            form.status === status.value
                                ? cn(
                                      statusStyle(status.color).border,
                                      statusStyle(status.color).soft,
                                  )
                                : 'border-border/70 hover:border-primary/40',
                        )
                    "
                    @click="chooseStatus(status.value)"
                >
                    <span
                        :class="
                            cn(
                                'flex size-8 shrink-0 items-center justify-center rounded-lg',
                                statusStyle(status.color).badge,
                            )
                        "
                    >
                        <component
                            :is="iconByName(status.icon)"
                            class="size-4"
                        />
                    </span>
                    <span class="space-y-0.5">
                        <span class="block text-sm font-medium text-foreground">
                            {{ status.label }}
                        </span>
                        <span class="block text-xs text-muted-foreground">
                            {{ status.description }}
                        </span>
                    </span>
                </button>
            </div>
            <InputError :message="form.errors.status" />

            <div class="grid gap-2">
                <Label>Rating bintang</Label>
                <StarRatingInput v-model="form.rating" />
                <InputError :message="form.errors.rating" />
            </div>

            <div
                class="flex items-center justify-between rounded-xl border border-border/70 p-3"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300"
                    >
                        <Heart class="size-4" />
                    </span>
                    <div>
                        <p class="text-sm font-medium text-foreground">
                            Tandai sebagai favorit
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Buku favorit disorot di rak kamu.
                        </p>
                    </div>
                </div>
                <Switch
                    id="is_favorite"
                    :model-value="form.is_favorite"
                    @update:model-value="form.is_favorite = $event"
                />
            </div>
        </section>

        <section
            class="space-y-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs sm:p-6"
        >
            <div class="space-y-1">
                <h2 class="font-display text-xl text-foreground">Catatanmu</h2>
                <p class="text-sm text-muted-foreground">
                    Tulis dengan gayamu sendiri, pengunjung akan membacanya.
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="synopsis">Sinopsis versi kamu</Label>
                <Textarea
                    id="synopsis"
                    v-model="form.synopsis"
                    rows="4"
                    placeholder="Ceritakan singkat isi buku ini dengan bahasamu sendiri..."
                />
                <p class="text-xs text-muted-foreground">
                    {{ form.synopsis.length }}/{{ synopsisMax }} karakter
                </p>
                <InputError :message="form.errors.synopsis" />
            </div>

            <div class="grid gap-2">
                <Label for="review">Review & kesan</Label>
                <Textarea
                    id="review"
                    v-model="form.review"
                    rows="5"
                    placeholder="Apa yang paling kamu suka? Untuk siapa buku ini cocok?"
                />
                <p class="text-xs text-muted-foreground">
                    {{ form.review.length }}/{{ reviewMax }} karakter
                </p>
                <InputError :message="form.errors.review" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="started_at">Tanggal mulai baca</Label>
                    <DatePicker id="started_at" v-model="form.started_at" />
                    <InputError :message="form.errors.started_at" />
                </div>

                <div class="grid gap-2">
                    <Label for="finished_at">Tanggal selesai</Label>
                    <DatePicker id="finished_at" v-model="form.finished_at" />
                    <InputError :message="form.errors.finished_at" />
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                <Save v-else class="size-4" />
                {{ isEditing ? 'Simpan perubahan' : 'Tambahkan ke rak' }}
            </Button>

            <Button as-child type="button" variant="ghost">
                <Link :href="booksIndex()">
                    <X class="size-4" />
                    Batal
                </Link>
            </Button>
        </div>
    </form>
</template>
