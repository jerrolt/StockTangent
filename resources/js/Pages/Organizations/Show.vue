<script setup>
import AppButton from '@/Components/AppButton.vue';
import OrganizationSummary from '@/Components/OrganizationSummary.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ organization: { type: Object, required: true } });
</script>

<template>
    <Head :title="organization.name" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader :title="organization.name" icon="fa-building-o" description="Organization summary">
                <template #actions>
                    <AppButton :href="route('organizations.index')" variant="secondary" icon="previous">Organizations</AppButton>
                </template>
            </SiteHeader>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <OrganizationSummary :organization="organization" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
