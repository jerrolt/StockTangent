<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrganizationForm from './Partials/OrganizationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ organization: { type: Object, required: true } });
</script>

<template>
    <Head title="Edit organization" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit organization</h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <p v-if="organization.deleted_at" role="status" class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        This organization is deleted. Saving changes will keep it deleted; use Restore in the organization list to reactivate it.
                    </p>
                    <OrganizationForm :organization="organization" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
