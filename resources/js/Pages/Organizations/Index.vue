<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import ActionResultBanner from '@/Components/ActionResultBanner.vue';
import Modal from '@/Components/Modal.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import OrganizationNameLink from '@/Components/OrganizationNameLink.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    organizations: { type: Object, required: true },
    deletedOrganizations: { type: Object, required: true },
    success: { type: String, default: null },
    error: { type: String, default: null },
    actionResult: { type: Object, default: null },
});

const page = usePage();
const feedback = computed(() => {
    const errors = Object.values(page.props.errors ?? {}).flat().filter(Boolean);
    if (errors.length) return { status: 'error', message: 'Please review the following errors and try again.', details: errors };
    if (props.actionResult) return props.actionResult;
    if (props.error) return { status: 'error', message: props.error };
    if (props.success) return { status: 'success', message: props.success };
    return null;
});

const sections = computed(() => [
    { id: 'active-organizations', title: 'Active Organizations', organizations: props.organizations, deleted: false },
    { id: 'deleted-organizations', title: 'Deleted Organizations', organizations: props.deletedOrganizations, deleted: true },
]);

const collapsedTables = ref({
    'active-organizations': false,
    'deleted-organizations': false,
});

function isTableHidden(section) {
    return section.organizations.total === 0 || collapsedTables.value[section.id];
}

function toggleTable(section) {
    if (section.organizations.total === 0) return;
    closeMapPreview();
    collapsedTables.value[section.id] = !collapsedTables.value[section.id];
}

const restoreForm = useForm({});
const deleteForm = useForm({});
const organizationToConfirm = ref(null);
const resultsBanner = ref(null);
const focusResultsAfterClose = ref(false);
const confirmationAction = ref('delete');
const confirmationForm = computed(() => confirmationAction.value === 'delete' ? deleteForm : restoreForm);

function confirmDelete(organization) {
    closeMapPreview();
    deleteForm.clearErrors();
    confirmationAction.value = 'delete';
    organizationToConfirm.value = organization;
}

function cancelConfirmation() {
    if (confirmationForm.value.processing) return;
    organizationToConfirm.value = null;
    confirmationForm.value.clearErrors();
}

function executeConfirmation() {
    if (!organizationToConfirm.value || confirmationForm.value.processing) return;

    const method = confirmationAction.value === 'delete' ? 'delete' : 'patch';
    const routeName = confirmationAction.value === 'delete' ? 'organizations.destroy' : 'organizations.restore';
    confirmationForm.value[method](route(routeName, organizationToConfirm.value.id), {
        preserveScroll: false,
        onSuccess: () => {
            focusResultsAfterClose.value = true;
            organizationToConfirm.value = null;
        },
    });
}

function focusActionResults() {
    if (!focusResultsAfterClose.value) return;
    focusResultsAfterClose.value = false;
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    resultsBanner.value?.focus();
}

function restore(organization) {
    closeMapPreview();
    restoreForm.clearErrors();
    confirmationAction.value = 'restore';
    organizationToConfirm.value = organization;
}

function deletedDate(value) {
    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
}

function fullAddress(organization) {
    return [organization.address_line1, organization.address_line2, organization.city, organization.state, organization.postal_code]
        .filter((part) => part?.trim())
        .join(', ');
}

function mapsUrl(organization) {
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAddress(organization))}`;
}

const mapPreview = ref(null);
let mapCloseTimer;

function keepMapOpen() {
    clearTimeout(mapCloseTimer);
}

function closeMapPreview() {
    keepMapOpen();
    mapPreview.value = null;
}

function scheduleMapClose() {
    keepMapOpen();
    mapCloseTimer = setTimeout(closeMapPreview, 200);
}

function showMapPreview(event, organization) {
    keepMapOpen();
    const anchor = event.currentTarget.getBoundingClientRect();
    const width = Math.min(380, window.innerWidth - 32);
    const height = Math.min(340, window.innerHeight - 32);
    const top = anchor.bottom + height + 12 <= window.innerHeight
        ? anchor.bottom + 8
        : Math.max(16, anchor.top - height - 8);

    mapPreview.value = {
        name: organization.name,
        address: fullAddress(organization),
        url: mapsUrl(organization),
        embedUrl: `https://maps.google.com/maps?q=${encodeURIComponent(fullAddress(organization))}&output=embed`,
        style: {
            left: `${Math.max(16, Math.min(anchor.left, window.innerWidth - width - 16))}px`,
            top: `${top}px`,
            width: `${width}px`,
            height: `${height}px`,
        },
    };
}

function dismissMapOnEscape(event) {
    if (event.key === 'Escape') closeMapPreview();
}

onMounted(() => {
    window.addEventListener('resize', closeMapPreview);
    window.addEventListener('scroll', closeMapPreview, true);
    window.addEventListener('keydown', dismissMapOnEscape);
});

onUnmounted(() => {
    keepMapOpen();
    window.removeEventListener('resize', closeMapPreview);
    window.removeEventListener('scroll', closeMapPreview, true);
    window.removeEventListener('keydown', dismissMapOnEscape);
});

function openMap(event, organization) {
    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;

    event.preventDefault();
    closeMapPreview();
    window.open(mapsUrl(organization), '_blank', 'popup,width=1100,height=800,noopener,noreferrer');
}
</script>

<template>
    <Head title="Organizations" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader title="Organizations" icon="fa-building-o">
                <template #actions>
                    <AppButton :href="route('organizations.create')" icon="create">New Organization</AppButton>
                </template>
            </SiteHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ActionResultBanner v-if="feedback" ref="resultsBanner" :result="feedback">
                    <template v-if="feedback.organization" #actions>
                        <AppButton
                            v-if="feedback.nextAction === 'restore'"
                            icon="restore"
                            variant="success"
                            :disabled="restoreForm.processing"
                            :aria-label="`Restore ${feedback.organization.name}`"
                            @click="restore(feedback.organization)"
                        >{{ restoreForm.processing ? 'Restoring…' : 'Restore' }}</AppButton>
                        <AppButton
                            v-else-if="feedback.nextAction === 'delete'"
                            icon="delete"
                            variant="danger"
                            :aria-label="`Delete`"
                            @click="confirmDelete(feedback.organization)"
                        >Delete</AppButton>
                    </template>
                </ActionResultBanner>
                <section v-for="section in sections" :key="section.id" :aria-labelledby="section.id" class="mb-8 overflow-hidden rounded-lg bg-white shadow-sm">
                    <SiteHeader
                        :heading-id="section.id"
                        :title="section.title"
                        variant="secondary"
                        :tone="section.deleted ? 'red' : 'green'"
                        :icon="section.deleted ? 'fa-trash' : 'fa-check-circle'"
                        :displayIcons="false"
                        :count="section.organizations.total"
                        class="border-b border-gray-200 px-6 py-4"
                    >
                        <template #actions>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded px-1 py-1 text-xs font-medium text-slate-500 transition-colors enabled:hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:text-slate-400"
                                :disabled="section.organizations.total === 0"
                                :aria-expanded="!isTableHidden(section)"
                                :aria-controls="`${section.id}-table`"
                                :aria-label="`${isTableHidden(section) ? 'Show' : 'Hide'} ${section.title} table`"
                                :title="section.organizations.total === 0 ? 'No organizations to show' : `${isTableHidden(section) ? 'Show' : 'Hide'} table`"
                                @click="toggleTable(section)"
                            >
                                <span>{{ isTableHidden(section) ? 'Show' : 'Hide' }}</span>
                                <i :class="isTableHidden(section) ? 'fa fa-chevron-down' : 'fa fa-chevron-up'" aria-hidden="true"></i>
                            </button>
                        </template>
                    </SiteHeader>
                    <div :id="`${section.id}-table`" v-show="!isTableHidden(section)">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <caption class="sr-only">{{ section.title }} you belong to</caption>
                            <thead class="border-b border-slate-200 bg-slate-100 text-xs uppercase tracking-wider text-slate-700">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold">Name</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Location</th>
                                    <th scope="col" class="px-6 py-4 font-bold">Description</th>
                                    <th v-if="section.deleted" scope="col" class="px-6 py-4 text-right font-bold">Deleted on</th>
                                    <th scope="col" class="px-6 py-4 text-right font-bold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="!section.organizations.data.length">
                                    <td :colspan="section.deleted ? 5 : 4" class="px-6 py-8 text-center text-gray-600">{{ section.deleted ? 'No deleted organizations.' : 'No active organizations yet.' }}</td>
                                </tr>
                                <tr v-for="organization in section.organizations.data" :key="organization.id" class="transition-colors hover:bg-blue-50 focus-within:bg-blue-50">
                                    <th scope="row" class="px-6 py-4 font-bold text-slate-900">
                                        <OrganizationNameLink :organization="organization" />
                                    </th>
                                    <td class="px-6 py-4 text-gray-600">
                                        <a
                                            v-if="fullAddress(organization)"
                                            :href="mapsUrl(organization)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            :aria-label="`View ${organization.name} at ${fullAddress(organization)} in Google Maps (opens in a new window)`"
                                            class="inline-flex items-center gap-2 rounded font-medium text-blue-700 underline decoration-blue-300 underline-offset-4 hover:text-blue-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                            @click="openMap($event, organization)"
                                            @mouseenter="showMapPreview($event, organization)"
                                            @mouseleave="scheduleMapClose"
                                            @focus="showMapPreview($event, organization)"
                                            @blur="scheduleMapClose"
                                        >
                                            {{ [organization.city, organization.state].filter(Boolean).join(', ') || fullAddress(organization) }}
                                            <i class="fa fa-map-marker text-base" aria-hidden="true"></i>
                                        </a>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="max-w-xs break-words px-6 py-4 text-gray-600">{{ organization.description || '—' }}</td>
                                    <td v-if="section.deleted" class="whitespace-nowrap px-6 py-4 text-right text-gray-600">
                                        <time :datetime="organization.deleted_at">{{ deletedDate(organization.deleted_at) }}</time>
                                    </td>
                                    <td v-if="section.deleted" class="whitespace-nowrap px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <AppButton :href="route('organizations.edit', organization.id)" icon="edit" variant="warning" size="sm" :aria-label="`Edit ${organization.name}`">Edit</AppButton>
                                        <AppButton
                                            icon="restore"
                                            variant="success"
                                            size="sm"
                                            :aria-label="`Restore ${organization.name}`"
                                            :disabled="restoreForm.processing"
                                            @click="restore(organization)"
                                            
                                        >Restore</AppButton>
                                        </div>
                                    </td>
                                    <td v-else class="whitespace-nowrap px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <AppButton :href="route('organizations.edit', organization.id)" icon="edit" variant="warning" size="sm" :aria-label="`Edit ${organization.name}`">Edit</AppButton>
                                            <AppButton icon="delete" variant="danger" size="sm" :aria-label="`Delete ${organization.name}`" @click="confirmDelete(organization)">Delete</AppButton>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav v-if="section.organizations.last_page > 1" :aria-label="`${section.title} pages`" class="flex items-center justify-between border-t border-gray-200 px-6 py-4 text-sm">
                        <AppButton :href="section.organizations.prev_page_url" :disabled="!section.organizations.prev_page_url" icon="previous" variant="secondary" size="sm" preserve-scroll>Previous</AppButton>
                        <span class="text-gray-600">Page {{ section.organizations.current_page }} of {{ section.organizations.last_page }}</span>
                        <AppButton :href="section.organizations.next_page_url" :disabled="!section.organizations.next_page_url" icon="next" variant="secondary" size="sm" preserve-scroll>Next</AppButton>
                    </nav>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>

    <Modal :show="!!organizationToConfirm" :closeable="!confirmationForm.processing" max-width="lg" aria-labelledby="organization-confirmation-title" aria-describedby="organization-confirmation-description" @close="cancelConfirmation" @closed="focusActionResults">
        <form v-if="organizationToConfirm" class="p-6 sm:p-8" aria-labelledby="organization-confirmation-title" aria-describedby="organization-confirmation-description" @submit.prevent="executeConfirmation">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full text-xl" :class="confirmationAction === 'delete' ? 'bg-red-100 text-red-700' : 'bg-purple-100 text-purple-700'" aria-hidden="true">
                <i :class="confirmationAction === 'delete' ? 'fa fa-trash' : 'fa fa-undo'"></i>
            </div>
            <h2 id="organization-confirmation-title" class="text-xl font-bold text-slate-900">{{ confirmationAction === 'delete' ? 'Delete' : 'Restore' }} {{ organizationToConfirm.name }}?</h2>
            <p id="organization-confirmation-description" class="mt-3 text-sm leading-relaxed text-slate-600">
                {{ confirmationAction === 'delete' ? 'This organization will move to the deleted list for all members. Its inventory records will be retained, and you can restore it afterward.' : 'This organization will return to the active list for all members. Its existing details, memberships, and inventory records will be preserved.' }}
            </p>
            <div v-if="confirmationForm.hasErrors" role="alert" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <p v-for="(message, field) in confirmationForm.errors" :key="field">{{ message }}</p>
            </div>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <AppButton variant="secondary" autofocus :disabled="confirmationForm.processing" @click="cancelConfirmation">Cancel</AppButton>
                <AppButton type="submit" :variant="confirmationAction === 'delete' ? 'danger' : 'success'" :icon="confirmationAction" :disabled="confirmationForm.processing" :aria-label="`Confirm ${confirmationAction} ${organizationToConfirm.name}`">{{ confirmationForm.processing ? (confirmationAction === 'delete' ? 'Deleting…' : 'Restoring…') : 'Confirm' }}</AppButton>
            </div>
        </form>
    </Modal>

    <Teleport to="body">
        <div
            v-if="mapPreview"
            role="region"
            :aria-label="`Map preview for ${mapPreview.name}`"
            class="fixed z-50 flex flex-col overflow-hidden rounded-xl border border-blue-200 bg-white shadow-2xl ring-1 ring-black/5"
            :style="mapPreview.style"
            @mouseenter="keepMapOpen"
            @mouseleave="scheduleMapClose"
            @focusin="keepMapOpen"
            @focusout="scheduleMapClose"
        >
            <div class="flex items-start justify-between gap-3 border-b border-blue-100 bg-blue-50 px-4 py-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-slate-900">{{ mapPreview.name }}</p>
                    <p class="mt-1 text-xs text-slate-600">{{ mapPreview.address }}</p>
                </div>
                <button type="button" aria-label="Close map preview" class="rounded px-1 text-slate-500 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-500" @click="closeMapPreview">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <iframe
                :key="mapPreview.embedUrl"
                :src="mapPreview.embedUrl"
                :title="`Google Maps location for ${mapPreview.name}`"
                class="min-h-0 w-full flex-1 border-0"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
            ></iframe>
            <a :href="mapPreview.url" target="_blank" rel="noopener noreferrer" class="border-t border-blue-100 px-4 py-2 text-xs font-bold text-blue-700 underline hover:text-blue-900">
                Open in Google Maps <span class="sr-only">(opens in a new window or tab)</span>
                <i class="fa fa-external-link ml-1" aria-hidden="true"></i>
            </a>
        </div>
    </Teleport>
</template>
