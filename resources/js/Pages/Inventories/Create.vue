<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InventoryForm from './Partials/InventoryForm.vue';
import { Head } from '@inertiajs/vue3';
defineProps({ organizations: { type: Array, required: true } });
</script>

<template>
    <Head title="Create inventory" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create inventory</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <p class="mb-6 text-sm text-gray-600">Enter the inventory name, choose its owner, and add an optional description.</p>
                    <InventoryForm :organizations="organizations" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
