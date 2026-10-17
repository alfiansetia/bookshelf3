<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Hapus akun"
            description="Hapus akunmu beserta seluruh isi rak"
        />
        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Perhatian</p>
                <p class="text-sm">Tindakan ini tidak bisa dibatalkan.</p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        variant="destructive"
                        data-test="delete-user-button"
                    >
                        Hapus akun
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ processing }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle>
                                Yakin ingin menghapus akunmu?
                            </DialogTitle>
                            <DialogDescription>
                                Seluruh buku, ulasan, dan rak publikmu akan ikut
                                terhapus permanen dan tidak bisa dikembalikan.
                            </DialogDescription>
                        </DialogHeader>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary" type="button">
                                    Batal
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Hapus akun
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
