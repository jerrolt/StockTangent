<script setup>
import { computed } from 'vue';
import AppButton from '@/Components/AppButton.vue';

const props = defineProps({
    member: { type: Object, required: true },
    compact: { type: Boolean, default: false },
});

const name = computed(() => [props.member.firstname, props.member.lastname].filter(Boolean).join(' ') || props.member.name || '—');
const address = computed(() => [
    props.member.address_line1,
    props.member.address_line2,
    props.member.city,
    props.member.state,
    props.member.postal_code,
].filter(Boolean).join(', '));

const connections = computed(() => [
    { title: 'Organizations', records: props.member.organizations ?? [], empty: 'No connected organizations.' },
    { title: 'Inventories', records: props.member.inventories ?? [], empty: 'No connected inventories.' },
]);

function date(value) {
    return value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value)) : '—';
}
</script>

<template>
    <div class="space-y-5">
        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold" :class="member.deleted_at ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'">
            {{ member.deleted_at ? 'Deleted member' : 'Active member' }}
        </span>
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="font-bold text-slate-700">Name</dt>
                <dd class="mt-1 break-words leading-relaxed text-slate-600">{{ name }}</dd>
            </div>
            <div>
                <dt class="font-bold text-slate-700">Email</dt>
                <dd class="mt-1 break-words leading-relaxed text-slate-600">{{ member.email || 'No email provided.' }}</dd>
            </div>
            <div v-if="member.role">
                <dt class="font-bold text-slate-700">Role</dt>
                <dd class="mt-1 break-words leading-relaxed text-slate-600">{{ member.role }}</dd>
            </div>
            <div>
                <dt class="font-bold text-slate-700">Address</dt>
                <dd class="mt-1 break-words leading-relaxed text-slate-600" :class="{ 'line-clamp-3': compact }">{{ address || 'No address provided.' }}</dd>
            </div>
            <div v-for="connection in connections" :key="connection.title">
                <dt class="font-bold text-slate-700">{{ connection.title }}</dt>
                <dd class="mt-1 break-words leading-relaxed text-slate-600">
                    <ul v-if="connection.records.length" class="space-y-2">
                        <li v-for="record in connection.records" :key="record.id" class="flex items-start gap-2" :class="record.deleted_at ? 'p-0.5  border border-red-200 bg-red-50 text-red-800' : ''">
                            <span v-if="record.deleted_at" class="min-w-0 flex-1 break-words text-red-800">{{ record.name }}</span>
                            <span v-else class="min-w-0 flex-1 break-words">{{ record.name }}</span>

                            <span v-if="record.deleted_at" class="shrink-0 rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-bold text-red-800">Disabled</span>
                        </li>
                    </ul>
                    <span v-else>{{ connection.empty }}</span>
                </dd>
            </div>
            <template v-if="!compact">
                <div>
                    <dt class="font-bold text-slate-700">Created</dt>
                    <dd class="mt-1 text-slate-600">{{ date(member.created_at) }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-700">Last updated</dt>
                    <dd class="mt-1 text-slate-600">{{ date(member.updated_at) }}</dd>
                </div>
                <div v-if="member.deleted_at">
                    <dt class="font-bold text-slate-700">Deleted</dt>
                    <dd class="mt-1 text-slate-600">{{ date(member.deleted_at) }}</dd>
                </div>
            </template>
        </dl>
        <div v-if="member.can_update" class="border-t border-slate-200 pt-4">
            <AppButton :href="route('members.edit', member.id)" icon="edit" variant="warning" :size="compact ? 'sm' : 'md'">Edit</AppButton>
        </div>
    </div>
</template>
