<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrganizationForm from './Partials/OrganizationForm.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create organization" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create organization</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <p class="mb-6 text-sm text-gray-600">Enter the organization name and any contact details. Only the name is required.</p>
                    <OrganizationForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
