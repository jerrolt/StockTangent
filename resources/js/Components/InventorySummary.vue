<script setup>
import AppButton from '@/Components/AppButton.vue';

const props = defineProps({
    inventory: { type: Object, required: true },
    compact: { type: Boolean, default: false },
});

function date(value) {
    return value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value)) : '—';
}
</script>

<template>
    <div class="space-y-5">
        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold" :class="inventory.deleted_at ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'">
            {{ inventory.deleted_at ? 'Deleted inventory' : 'Active inventory' }}
        </span>
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="font-bold text-slate-700">Owner</dt>
                <dd class="mt-1 break-words leading-relaxed text-slate-600">{{ inventory.organization?.name || [inventory.owner?.firstname, inventory.owner?.lastname].filter(Boolean).join(' ') || 'Personal inventory' }}</dd>
            </div>
            <div>
                <dt class="font-bold text-slate-700">Description</dt>
                <dd class="mt-1 whitespace-pre-line break-words leading-relaxed text-slate-600" :class="{ 'line-clamp-3': compact }">{{ inventory.description || 'No description provided.' }}</dd>
            </div>
            <div>
                <dt class="font-bold text-slate-700">Authorized Members</dt>
                <dd class="mt-1 whitespace-pre-line break-words leading-relaxed text-slate-600" :class="{ 'line-clamp-3': compact }">{{ inventory.users?.map(user => `${user.firstname} ${user.lastname}`).join(', ') || 'No users assigned.' }}</dd>
            </div>
            <template v-if="!compact">
                <div>
                    <dt class="font-bold text-slate-700">Created</dt>
                    <dd class="mt-1 text-slate-600">{{ date(inventory.created_at) }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-700">Last updated</dt>
                    <dd class="mt-1 text-slate-600">{{ date(inventory.updated_at) }}</dd>
                </div>
                
                <div v-if="inventory.deleted_at">
                    <dt class="font-bold text-slate-700">Deleted</dt>
                    <dd class="mt-1 text-slate-600">{{ date(inventory.deleted_at) }}</dd>
                </div>
            </template>
        </dl>
        <div v-if="inventory.can_update" class="border-t border-slate-200 pt-4">
            <AppButton :href="route('inventories.edit', inventory.id)" icon="edit" variant="warning" :size="compact ? 'sm' : 'md'">Edit</AppButton>
        </div>
    </div>
</template>
