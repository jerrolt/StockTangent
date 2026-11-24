<script setup>
import AppButton from '@/Components/AppButton.vue';
import MemberSummary from '@/Components/MemberSummary.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ member: { type: Object, required: true } });
</script>

<template>
    <Head :title="member.name" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader :title="member.name" icon="fa-archive" description="Member summary">
                <template #actions>
                    
                    <AppButton :href="route('members.index')" variant="secondary" icon="previous">Members</AppButton>
                    <AppButton :href="route('profile.edit', member.id)" variant="warning" icon="edit">Edit Profile</AppButton>
                </template>
            </SiteHeader>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <MemberSummary :member="member" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
