<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Monitor, Moon, Sun } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import type { Appearance } from '@/types';

type Props = {
    /** Ukuran tombol supaya sejajar dengan elemen lain di navbar. */
    size?: 'sm' | 'default';
};

const props = withDefaults(defineProps<Props>(), {
    size: 'default',
});

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

// Ikon ditentukan setelah komponen terpasang agar markup server dan klien sama.
const mounted = ref(false);

onMounted(() => {
    mounted.value = true;
});

const triggerIcon = computed(() =>
    mounted.value && resolvedAppearance.value === 'dark' ? Moon : Sun,
);

const options: Array<{ value: Appearance; label: string; icon: typeof Sun }> = [
    { value: 'light', label: 'Terang', icon: Sun },
    { value: 'dark', label: 'Gelap', icon: Moon },
    { value: 'system', label: 'Ikuti sistem', icon: Monitor },
];

function selectAppearance(value: unknown): void {
    updateAppearance(value as Appearance);
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                :size="props.size === 'sm' ? 'icon-sm' : 'icon'"
                class="cursor-pointer"
                aria-label="Ganti tema"
                data-test="theme-toggle"
            >
                <component :is="triggerIcon" class="size-4.5" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-44">
            <DropdownMenuLabel>Tema</DropdownMenuLabel>
            <DropdownMenuRadioGroup
                :model-value="appearance"
                @update:model-value="selectAppearance"
            >
                <DropdownMenuRadioItem
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                    class="cursor-pointer"
                >
                    <component :is="option.icon" />
                    {{ option.label }}
                </DropdownMenuRadioItem>
            </DropdownMenuRadioGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
