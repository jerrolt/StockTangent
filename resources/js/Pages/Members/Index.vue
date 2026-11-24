<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import ActionResultBanner from '@/Components/ActionResultBanner.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import MemberNameLink from '@/Components/MemberNameLink.vue';
import indexStyles from '@/Styles/indexStyles';
import { App, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    members: { type: Object, required: true },
    deletedMembers: { type: Object, required: true },
});

const page = usePage();
const feedback = computed(() => {
    const errors = Object.values(page.props.errors ?? {}).flat().filter(Boolean);
    if (errors.length) return { status: 'error', message: 'Please review the following errors and try again.', details: errors };
    if (page.props.actionResult) return page.props.actionResult;
    if (page.props.error) return { status: 'error', message: page.props.error };
    if (page.props.success) return { status: 'success', message: page.props.success };
    return null;
});

const sections = computed(() => [
    { id: 'active-members', title: 'Active Members', members: props.members, deleted: false },
    { id: 'inactive-members', title: 'Inactive Members', members: props.deletedMembers, deleted: true },
]);

const collapsedTables = ref({});

function isTableHidden(section) {
    return section.members.total === 0 || !!collapsedTables.value[section.id];
}

function toggleTable(section) {
    if (section.members.total === 0) return;
    collapsedTables.value[section.id] = !collapsedTables.value[section.id];
}

function memberName(member) {
    return [member.firstname, member.lastname].filter(Boolean).join(' ') || member.email;
}

function deletedDate(value) {
    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
}
</script>

<template>
    <Head title="Members" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader title="Members" icon="fa-users">
                <template #actions>
                    <!-- <AppButton :href="route('members.create')" icon="create">Add member</AppButton> -->
                </template>
            </SiteHeader>
        </template>

        <div class="py-12">
            <div :class="indexStyles.container">
                <ActionResultBanner v-if="feedback" :result="feedback" />
                <section v-for="section in sections" :key="section.id" :aria-labelledby="section.id" :class="indexStyles.section">
                    <SiteHeader
                        :heading-id="section.id"
                        :title="section.title"
                        variant="secondary"
                        :tone="section.deleted ? 'red' : 'green'"
                        :icon="section.deleted ? 'fa-trash' : 'fa-check-circle'"
                        :displayIcons="false"
                        :count="section.members.total"
                        :class="indexStyles.sectionHeader"
                    >
                        <template #actions>
                            <button
                                type="button"
                                :class="indexStyles.toggle"
                                :disabled="section.members.total === 0"
                                :aria-expanded="!isTableHidden(section)"
                                :aria-controls="`${section.id}-table`"
                                :aria-label="`${isTableHidden(section) ? 'Show' : 'Hide'} ${section.title} table`"
                                :title="section.members.total === 0 ? 'No members to show' : `${isTableHidden(section) ? 'Show' : 'Hide'} table`"
                                @click="toggleTable(section)"
                            >
                                <span>{{ isTableHidden(section) ? 'Show' : 'Hide' }}</span>
                                <i :class="isTableHidden(section) ? 'fa fa-chevron-down' : 'fa fa-chevron-up'" aria-hidden="true"></i>
                            </button>
                        </template>
                    </SiteHeader>
                    <div :id="`${section.id}-table`" v-show="!isTableHidden(section)">
                        <div class="overflow-x-auto">
                            <table :class="indexStyles.table">
                                <caption class="sr-only">{{ section.title }}</caption>
                                <thead :class="indexStyles.tableHead">
                                    <tr>
                                        <th scope="col" :class="indexStyles.columnHeading">Name</th>
                                        <th scope="col" :class="indexStyles.columnHeading">Email</th>
                                        <!-- <th scope="col" :class="indexStyles.columnHeading">Role</th> -->
                                        <th v-if="section.deleted" scope="col" class="px-6 py-4 text-right font-bold">Deleted on</th>
                                        <th scope="col" class="px-6 py-4 text-right font-bold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody :class="indexStyles.tableBody">
                                    <tr v-if="!section.members.data.length">
                                        <td :colspan="section.deleted ? 5 : 4" :class="indexStyles.empty">{{ section.deleted ? 'No deleted members.' : 'No active members yet.' }}</td>
                                    </tr>
                                    <tr v-for="member in section.members.data" :key="member.id" :class="indexStyles.row">
                                        <th scope="row" :class="indexStyles.rowHeading">
                                            <MemberNameLink :member="member" />
                                        </th>
                                        <td :class="indexStyles.cell">
                                            {{ member.email }}
                                        </td>
                                        <!-- <td class="max-w-xs break-words px-6 py-4 text-gray-600">{{ member.role || '—' }}</td> -->
                                        <td v-if="section.deleted" class="whitespace-nowrap px-6 py-4 text-right text-gray-600">
                                            <time :datetime="member.deleted_at">{{ deletedDate(member.deleted_at) }}</time>
                                        </td>
                                        <td :class="indexStyles.actions">
                                            <div class="flex justify-end gap-2">
                                                <AppButton v-if="!section.deleted && member.id === $page.props.auth.user.id" :href="route('profile.edit', member.id)" icon="edit" variant="warning" size="sm" :aria-label="`Edit ${memberName(member)}`">Edit</AppButton>
                                                <!-- <span v-else class="text-gray-600">—</span> -->
                                                <AppButton v-if="!section.deleted" :href="route('members.destroy', member.id)" icon="delete" variant="danger" size="sm" :aria-label="`Delete ${memberName(member)}`">Disable</AppButton>
                                                <AppButton v-else :href="route('members.restore', member.id)" icon="restore" variant="success" size="sm" :aria-label="`Restore ${memberName(member)}`">Activate</AppButton>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav v-if="section.members.last_page > 1" :aria-label="`${section.title} pages`" :class="indexStyles.pagination">
                            <AppButton :href="section.members.prev_page_url" :disabled="!section.members.prev_page_url" icon="previous" variant="secondary" size="sm" preserve-scroll>Previous</AppButton>
                            <span class="text-gray-600">Page {{ section.members.current_page }} of {{ section.members.last_page }}</span>
                            <AppButton :href="section.members.next_page_url" :disabled="!section.members.next_page_url" icon="next" variant="secondary" size="sm" preserve-scroll>Next</AppButton>
                        </nav>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
