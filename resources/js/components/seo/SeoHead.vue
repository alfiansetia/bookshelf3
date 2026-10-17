<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

type OpenGraphType = 'website' | 'article' | 'book' | 'profile';

const props = withDefaults(
    defineProps<{
        title: string;
        description?: string | null;
        canonical?: string | null;
        image?: string | null;
        type?: OpenGraphType;
        noindex?: boolean;
    }>(),
    {
        description: null,
        canonical: null,
        image: null,
        type: 'website',
        noindex: false,
    },
);

const page = usePage();

const defaults = computed(() => page.props.seo);

const resolvedDescription = computed(
    () => props.description || defaults.value.description,
);

const resolvedImage = computed(() => props.image || defaults.value.image);

/**
 * Ukuran hanya benar untuk gambar bawaan (1200x630), bukan sampul buku.
 */
const usesDefaultImage = computed(() => props.image === null);
</script>

<template>
    <Head :title="title">
        <meta
            head-key="description"
            name="description"
            :content="resolvedDescription"
        />
        <meta
            v-if="noindex"
            head-key="robots"
            name="robots"
            content="noindex, nofollow"
        />

        <link
            v-if="canonical"
            head-key="canonical"
            rel="canonical"
            :href="canonical"
        />

        <meta
            head-key="og:site_name"
            property="og:site_name"
            :content="defaults.siteName"
        />
        <meta head-key="og:type" property="og:type" :content="type" />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta
            head-key="og:description"
            property="og:description"
            :content="resolvedDescription"
        />
        <meta
            head-key="og:locale"
            property="og:locale"
            :content="defaults.locale"
        />
        <meta
            v-if="canonical"
            head-key="og:url"
            property="og:url"
            :content="canonical"
        />
        <meta
            v-if="resolvedImage"
            head-key="og:image"
            property="og:image"
            :content="resolvedImage"
        />
        <meta
            v-if="resolvedImage"
            head-key="og:image:alt"
            property="og:image:alt"
            :content="title"
        />
        <meta
            v-if="resolvedImage && usesDefaultImage"
            head-key="og:image:width"
            property="og:image:width"
            content="1200"
        />
        <meta
            v-if="resolvedImage && usesDefaultImage"
            head-key="og:image:height"
            property="og:image:height"
            content="630"
        />

        <meta
            head-key="twitter:card"
            name="twitter:card"
            :content="resolvedImage ? 'summary_large_image' : 'summary'"
        />
        <meta
            head-key="twitter:title"
            name="twitter:title"
            :content="title"
        />
        <meta
            head-key="twitter:description"
            name="twitter:description"
            :content="resolvedDescription"
        />
        <meta
            v-if="resolvedImage"
            head-key="twitter:image"
            name="twitter:image"
            :content="resolvedImage"
        />
        <meta
            v-if="defaults.twitter"
            head-key="twitter:site"
            name="twitter:site"
            :content="defaults.twitter"
        />
    </Head>
</template>
