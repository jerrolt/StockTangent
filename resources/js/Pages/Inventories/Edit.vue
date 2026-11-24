<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InventoryForm from './Partials/InventoryForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ inventory: { type: Object, required: true } });
</script>

<template>
    <Head title="Edit inventory" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit inventory</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <p v-if="inventory.deleted_at" role="status" class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        This inventory is deleted. Saving changes will keep it deleted; use Restore in the inventory list to reactivate it.
                    </p>
                    <InventoryForm :inventory="inventory" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
