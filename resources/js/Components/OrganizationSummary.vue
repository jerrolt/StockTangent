<script setup>
import { computed } from 'vue';
import AppButton from '@/Components/AppButton.vue';

const props = defineProps({
    organization: { type: Object, required: true },
    compact: { type: Boolean, default: false },
});

const address = computed(() => [
    props.organization.address_line1,
    props.organization.address_line2,
    props.organization.city,
    props.organization.state,
    props.organization.postal_code,
].filter(Boolean).join(', '));

function date(value) {
    return value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value)) : '—';
}
</script>

<template>
    <div class="space-y-5">
        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold" :class="organization.deleted_at ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'">
            {{ organization.deleted_at ? 'Deleted organization' : 'Active organization' }}
        </span>
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="font-bold text-slate-700"><i class="fa fa-map-marker mr-1" aria-hidden="true"></i> Address</dt>
                <dd class="mt-1 break-words leading-relaxed text-slate-600">{{ address || 'No address provided.' }}</dd>
            </div>
            <div>
                <dt class="font-bold text-slate-700">Description</dt>
                <dd class="mt-1 whitespace-pre-line break-words leading-relaxed text-slate-600" :class="{ 'line-clamp-3': compact }">{{ organization.description || 'No description provided.' }}</dd>
            </div>
            <template v-if="!compact">
                <div>
                    <dt class="font-bold text-slate-700">Created</dt>
                    <dd class="mt-1 text-slate-600">{{ date(organization.created_at) }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-700">Last updated</dt>
                    <dd class="mt-1 text-slate-600">{{ date(organization.updated_at) }}</dd>
                </div>
                <div v-if="organization.deleted_at">
                    <dt class="font-bold text-slate-700">Deleted</dt>
                    <dd class="mt-1 text-slate-600">{{ date(organization.deleted_at) }}</dd>
                </div>
            </template>
        </dl>
        <div class="border-t border-slate-200 pt-4">
            <AppButton :href="route('organizations.edit', organization.id)" icon="edit" variant="warning" :size="compact ? 'sm' : 'md'">Edit</AppButton>
        </div>
    </div>
</template>
