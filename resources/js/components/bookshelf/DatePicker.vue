<script setup lang="ts">
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone, parseDate, today } from '@internationalized/date';
import {
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerContent,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerNext,
    DatePickerPrev,
    DatePickerRoot,
    DatePickerTrigger,
} from 'reka-ui';
import { computed, ref, useId, watch } from 'vue';
import { CalendarDays, ChevronLeft, ChevronRight, X } from '@lucide/vue';
import { formatDate } from '@/lib/format';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        id?: string;
        placeholder?: string;
        disabled?: boolean;
        class?: string;
    }>(),
    {
        modelValue: null,
        id: undefined,
        placeholder: 'Pilih tanggal',
        disabled: false,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

const open = ref(false);
const rootId = `tanggal-${useId()}`;

/**
 * Hari ini di zona waktu pengguna, jadi batas "tidak boleh lewat hari ini"
 * sama dengan validasi di backend (`before_or_equal:today`).
 */
const maxDate = computed(() => today(getLocalTimeZone()));

/**
 * Nilai kalender dari string `YYYY-MM-DD` milik form.
 */
const calendarValue = computed<DateValue | undefined>(() => {
    if (!props.modelValue) {
        return undefined;
    }

    try {
        return parseDate(props.modelValue);
    } catch {
        return undefined;
    }
});

const label = computed(() => formatDate(props.modelValue) ?? props.placeholder);

function select(value: DateValue | undefined): void {
    emit('update:modelValue', value ? value.toString() : null);
}

function clear(): void {
    emit('update:modelValue', null);
}

// Tutup popover saat nilai dikosongkan.
watch(
    () => props.modelValue,
    (value) => {
        if (!value) {
            open.value = false;
        }
    },
);
</script>

<template>
    <div :class="cn('flex items-center gap-2', props.class)">
        <DatePickerRoot
            v-model:open="open"
            :model-value="calendarValue"
            close-on-select
            locale="id-ID"
            weekday-format="short"
            :week-starts-on="1"
            :max-value="maxDate"
            :disabled="props.disabled"
            :is-date-disabled="(date: DateValue) => date.compare(maxDate) > 0"
            @update:model-value="select"
        >
            <DatePickerTrigger
                :id="props.id ?? rootId"
                :disabled="props.disabled"
                :aria-label="props.placeholder"
                :class="
                    cn(
                        'flex h-9 w-full min-w-0 items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                        'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                        'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
                        !calendarValue && 'text-muted-foreground',
                    )
                "
            >
                <span class="truncate">{{ label }}</span>
                <CalendarDays class="size-4 shrink-0 opacity-60" />
            </DatePickerTrigger>

            <DatePickerContent
                :side-offset="6"
                :collision-padding="12"
                class="z-50 rounded-xl border border-border bg-popover p-3 text-popover-foreground shadow-md"
            >
                <DatePickerCalendar v-slot="{ weekDays, grid }">
                    <DatePickerHeader
                        class="mb-3 flex items-center justify-between"
                    >
                        <DatePickerPrev
                            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <ChevronLeft class="size-4" />
                        </DatePickerPrev>

                        <DatePickerHeading
                            class="text-sm font-medium text-foreground"
                        />

                        <DatePickerNext
                            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <ChevronRight class="size-4" />
                        </DatePickerNext>
                    </DatePickerHeader>

                    <DatePickerGrid
                        v-for="month in grid"
                        :key="month.value.toString()"
                        class="w-full border-collapse"
                    >
                        <DatePickerGridHead>
                            <DatePickerGridRow
                                class="flex w-full justify-between"
                            >
                                <DatePickerHeadCell
                                    v-for="day in weekDays"
                                    :key="day"
                                    class="w-9 rounded-md text-[11px] font-medium text-muted-foreground"
                                >
                                    {{ day }}
                                </DatePickerHeadCell>
                            </DatePickerGridRow>
                        </DatePickerGridHead>

                        <DatePickerGridBody class="mt-1 grid gap-1">
                            <DatePickerGridRow
                                v-for="(weekDates, index) in month.rows"
                                :key="`week-${index}`"
                                class="flex w-full justify-between"
                            >
                                <DatePickerCell
                                    v-for="weekDate in weekDates"
                                    :key="weekDate.toString()"
                                    :date="weekDate"
                                >
                                    <DatePickerCellTrigger
                                        :day="weekDate"
                                        :month="month.value"
                                        :class="
                                            cn(
                                                'flex size-9 items-center justify-center rounded-md text-sm transition-colors',
                                                'hover:bg-muted hover:text-foreground',
                                                'focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                                                'data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selected]:hover:bg-primary/90',
                                                'data-[today]:font-semibold data-[today]:underline data-[today]:underline-offset-4',
                                                'data-[disabled]:pointer-events-none data-[disabled]:opacity-30',
                                                'data-[outside-view]:opacity-40',
                                            )
                                        "
                                    />
                                </DatePickerCell>
                            </DatePickerGridRow>
                        </DatePickerGridBody>
                    </DatePickerGrid>
                </DatePickerCalendar>
            </DatePickerContent>
        </DatePickerRoot>

        <button
            v-if="calendarValue && !props.disabled"
            type="button"
            aria-label="Hapus tanggal"
            class="inline-flex size-9 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            @click="clear"
        >
            <X class="size-4" />
        </button>
    </div>
</template>
