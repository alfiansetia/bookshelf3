<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    CalendarDays,
    EyeOff,
    Heart,
    Pencil,
    Star,
    TrendingUp,
} from '@lucide/vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import ShareButton from '@/components/bookshelf/ShareButton.vue';
import SocialLinks from '@/components/bookshelf/SocialLinks.vue';
import { useInitials } from '@/composables/useInitials';
import { formatMonthYear, formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import type { ShelfProfile, ShelfSummary } from '@/types';

const props = withDefaults(
    defineProps<{
        profile: ShelfProfile;
        stats: ShelfSummary;
        isOwner?: boolean;
        shelfUrl?: string | null;
        class?: string;
    }>(),
    {
        isOwner: false,
        shelfUrl: null,
    },
);

const { getInitials } = useInitials();

const shareUrl = computed(() => {
    if (props.shelfUrl) {
        return props.shelfUrl;
    }

    const origin = typeof window === 'undefined' ? '' : window.location.origin;

    return `${origin}/${props.profile.username}`;
});

const highlights = computed(() => [
    {
        icon: BookOpen,
        label: 'Total buku',
        value: props.stats.total,
    },
    {
        icon: TrendingUp,
        label: 'Sudah dibaca',
        value: props.stats.finished,
    },
    {
        icon: Star,
        label: 'Rata-rata rating',
        value: props.stats.average_rating || '—',
    },
    {
        icon: Heart,
        label: 'Favorit',
        value: props.stats.favorites,
    },
]);
</script>

<template>
    <section
        :class="
            cn(
                'overflow-hidden rounded-3xl border border-border/70 bg-card/80 shadow-sm backdrop-blur',
                props.class,
            )
        "
    >
        <div
            class="h-24 w-full bg-gradient-to-r from-primary/85 via-primary/60 to-accent/70 sm:h-28"
            aria-hidden="true"
        />

        <div class="px-5 pb-6 sm:px-8">
            <div
                class="-mt-12 flex flex-col gap-5 sm:-mt-14 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="flex items-end gap-4">
                    <Avatar
                        class="size-20 shrink-0 rounded-2xl ring-4 ring-card sm:size-24"
                    >
                        <AvatarImage
                            v-if="props.profile.avatar_url"
                            :src="props.profile.avatar_url"
                            :alt="props.profile.name"
                        />
                        <AvatarFallback class="rounded-2xl text-2xl">
                            {{ getInitials(props.profile.name) }}
                        </AvatarFallback>
                    </Avatar>

                    <div class="min-w-0 pb-1">
                        <h1
                            class="truncate font-display text-2xl leading-tight text-foreground sm:text-3xl"
                        >
                            {{ props.profile.name }}
                        </h1>
                        <p class="text-sm text-primary">
                            /{{ props.profile.username }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <ShareButton
                        v-if="!props.isOwner"
                        :url="shareUrl"
                        :title="`Rak buku ${props.profile.name}`"
                        text="Lihat rak buku dan review yang sudah dibuat."
                        label="Bagikan rak"
                    />

                    <template v-else>
                        <Button as-child variant="outline" size="default">
                            <Link :href="editProfile()">
                                <Pencil class="size-4" />
                                Ubah profil
                            </Link>
                        </Button>
                        <ShareButton
                            :url="shareUrl"
                            :title="`Rak buku ${props.profile.name}`"
                            label="Bagikan"
                        />
                    </template>
                </div>
            </div>

            <p
                v-if="props.profile.tagline"
                class="mt-4 font-display text-lg text-foreground"
            >
                {{ props.profile.tagline }}
            </p>

            <p
                v-if="props.profile.bio"
                class="mt-2 max-w-3xl text-sm leading-relaxed text-pretty text-muted-foreground"
            >
                {{ props.profile.bio }}
            </p>

            <div
                class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted-foreground"
            >
                <span
                    v-if="props.profile.member_since"
                    class="inline-flex items-center gap-1.5"
                >
                    <CalendarDays class="size-3.5" />
                    Bergabung
                    {{
                        formatMonthYear(props.profile.joined_at) ??
                        props.profile.member_since
                    }}
                </span>

                <span
                    v-if="!props.profile.is_public"
                    class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 font-medium text-muted-foreground"
                >
                    <EyeOff class="size-3.5" />
                    Rak ini sedang disembunyikan dari publik
                </span>
            </div>

            <SocialLinks
                v-if="props.profile.socials.length > 0"
                :links="props.profile.socials"
                class="mt-5"
            />

            <dl
                class="mt-6 grid grid-cols-2 gap-3 border-t border-border/70 pt-5 sm:grid-cols-4"
            >
                <div
                    v-for="item in highlights"
                    :key="item.label"
                    class="flex items-center gap-2.5"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-xl bg-muted text-muted-foreground"
                    >
                        <component :is="item.icon" class="size-4" />
                    </span>
                    <div>
                        <dd
                            class="font-display text-xl leading-none text-foreground"
                        >
                            {{ formatNumber(Number(item.value)) }}
                        </dd>
                        <dt
                            class="text-[11px] tracking-wide text-muted-foreground uppercase"
                        >
                            {{ item.label }}
                        </dt>
                    </div>
                </div>
            </dl>
        </div>
    </section>
</template>
