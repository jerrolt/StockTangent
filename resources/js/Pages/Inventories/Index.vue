<script setup>
import indexStyles from '@/Styles/indexStyles';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import ActionResultBanner from '@/Components/ActionResultBanner.vue';
import Modal from '@/Components/Modal.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import InventoryNameLink from '@/Components/InventoryNameLink.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    inventories: { type: Object, required: true },
    deletedInventories: { type: Object, required: true },
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
    { id: 'active-inventories', title: 'Active Inventories', inventories: props.inventories, deleted: false },
    { id: 'deleted-inventories', title: 'Deleted Inventories', inventories: props.deletedInventories, deleted: true },
]);

const collapsedTables = ref({
    'active-inventories': false,
    'deleted-inventories': false,
});

function isTableHidden(section) {
    return section.inventories.total === 0 || collapsedTables.value[section.id];
}

function toggleTable(section) {
    if (section.inventories.total === 0) return;
    collapsedTables.value[section.id] = !collapsedTables.value[section.id];
}

const restoreForm = useForm({});
const deleteForm = useForm({});
const inventoryToConfirm = ref(null);
const resultsBanner = ref(null);
const focusResultsAfterClose = ref(false);
const confirmationAction = ref('delete');
const confirmationForm = computed(() => confirmationAction.value === 'delete' ? deleteForm : restoreForm);

function confirmDelete(inventory) {
    deleteForm.clearErrors();
    confirmationAction.value = 'delete';
    inventoryToConfirm.value = inventory;
}

function cancelConfirmation() {
    if (confirmationForm.value.processing) return;
    inventoryToConfirm.value = null;
    confirmationForm.value.clearErrors();
}

function executeConfirmation() {
    if (!inventoryToConfirm.value || confirmationForm.value.processing) return;

    const method = confirmationAction.value === 'delete' ? 'delete' : 'patch';
    const routeName = confirmationAction.value === 'delete' ? 'inventories.destroy' : 'inventories.restore';
    confirmationForm.value[method](route(routeName, inventoryToConfirm.value.id), {
        preserveScroll: false,
        onSuccess: () => {
            focusResultsAfterClose.value = true;
            inventoryToConfirm.value = null;
        },
    });
}

function focusActionResults() {
    if (!focusResultsAfterClose.value) return;
    focusResultsAfterClose.value = false;
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    resultsBanner.value?.focus();
}

function restore(inventory) {
    restoreForm.clearErrors();
    confirmationAction.value = 'restore';
    inventoryToConfirm.value = inventory;
}

function deletedDate(value) {
    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
}
</script>

<template>
    <Head title="Inventories" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader title="Inventories" icon="fa-archive">
                <template #actions>
                    <AppButton :href="route('inventories.create')" icon="create">New Inventory</AppButton>
                </template>
            </SiteHeader>
        </template>

        <div class="py-12">
            <div :class="indexStyles.container">
                <ActionResultBanner v-if="feedback" ref="resultsBanner" :result="feedback">
                    <template v-if="feedback.inventory && feedback.nextAction" #actions>
                        <AppButton
                            v-if="feedback.nextAction === 'restore'"
                            icon="restore"
                            variant="success"
                            :disabled="restoreForm.processing"
                            :aria-label="`Restore ${feedback.inventory.name}`"
                            @click="restore(feedback.inventory)"
                        >{{ restoreForm.processing ? 'Restoring…' : 'Restore' }}</AppButton>
                        <AppButton
                            v-else-if="feedback.nextAction === 'delete'"
                            icon="delete"
                            variant="danger"
                            :aria-label="`Delete ${feedback.inventory.name}`"
                            @click="confirmDelete(feedback.inventory)"
                        >Delete</AppButton>
                    </template>
                </ActionResultBanner>
                <section v-for="section in sections" :key="section.id" :aria-labelledby="section.id" :class="indexStyles.section">
                    <SiteHeader
                        :heading-id="section.id"
                        :title="section.title"
                        variant="secondary"
                        :tone="section.deleted ? 'red' : 'green'"
                        :icon="section.deleted ? 'fa-trash' : 'fa-check-circle'"
                        :displayIcons="false"
                        :count="section.inventories.total"
                        :class="indexStyles.sectionHeader"
                    >
                        <template #actions>
                            <button
                                type="button"
                                :class="indexStyles.toggle"
                                :disabled="section.inventories.total === 0"
                                :aria-expanded="!isTableHidden(section)"
                                :aria-controls="`${section.id}-table`"
                                :aria-label="`${isTableHidden(section) ? 'Show' : 'Hide'} ${section.title} table`"
                                :title="section.inventories.total === 0 ? 'No inventories to show' : `${isTableHidden(section) ? 'Show' : 'Hide'} table`"
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
                                <caption class="sr-only">{{ section.title }} you belong to</caption>
                                <thead :class="indexStyles.tableHead">
                                    <tr>
                                        <th scope="col" :class="indexStyles.columnHeading">Name</th>
                                        <th scope="col" :class="indexStyles.columnHeading">Owner</th>
                                        <th scope="col" :class="indexStyles.columnHeading">Description</th>
                                        <th v-if="section.deleted" scope="col" class="px-6 py-4 text-right font-bold">Deleted on</th>
                                        <th scope="col" class="px-6 py-4 text-right font-bold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody :class="indexStyles.tableBody">
                                    <tr v-if="!section.inventories.data.length">
                                        <td :colspan="section.deleted ? 5 : 4" :class="indexStyles.empty">{{ section.deleted ? 'No deleted inventories.' : 'No active inventories yet.' }}</td>
                                    </tr>
                                    <tr v-for="inventory in section.inventories.data" :key="inventory.id" :class="indexStyles.row">
                                        <th scope="row" :class="indexStyles.rowHeading">
                                            <InventoryNameLink :inventory="inventory" />
                                        </th>
                                        <td :class="indexStyles.cell">
                                            {{ inventory.organization?.name || [inventory.owner?.firstname, inventory.owner?.lastname].filter(Boolean).join(' ') || 'Personal inventory' }}
                                        </td>
                                        <td class="max-w-xs break-words px-6 py-4 text-gray-600">{{ inventory.description || '—' }}</td>
                                        <td v-if="section.deleted" class="whitespace-nowrap px-6 py-4 text-right text-gray-600">
                                            <time :datetime="inventory.deleted_at">{{ deletedDate(inventory.deleted_at) }}</time>
                                        </td>
                                        <td v-if="section.deleted" :class="indexStyles.actions">
                                            <div class="flex justify-end gap-2">
                                                <AppButton v-if="inventory.can_update" :href="route('inventories.edit', inventory.id)" icon="edit" variant="warning" size="sm" :aria-label="`Edit ${inventory.name}`">Edit</AppButton>
                                            <AppButton
                                                v-if="inventory.can_delete"
                                                icon="restore"
                                                variant="success"
                                                size="sm"
                                                :aria-label="`Restore ${inventory.name}`"
                                                :disabled="restoreForm.processing"
                                                @click="restore(inventory)"
                                            >Restore</AppButton>
                                            </div>
                                        </td>
                                        <td v-else :class="indexStyles.actions">
                                            <div class="flex justify-end gap-2">
                                                <AppButton v-if="inventory.can_update" :href="route('inventories.edit', inventory.id)" icon="edit" variant="warning" size="sm" :aria-label="`Edit ${inventory.name}`">Edit</AppButton>
                                                <AppButton v-if="inventory.can_delete" icon="delete" variant="danger" size="sm" :aria-label="`Delete ${inventory.name}`" @click="confirmDelete(inventory)">Delete</AppButton>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav v-if="section.inventories.last_page > 1" :aria-label="`${section.title} pages`" :class="indexStyles.pagination">
                            <AppButton :href="section.inventories.prev_page_url" :disabled="!section.inventories.prev_page_url" icon="previous" variant="secondary" size="sm" preserve-scroll>Previous</AppButton>
                            <span class="text-gray-600">Page {{ section.inventories.current_page }} of {{ section.inventories.last_page }}</span>
                            <AppButton :href="section.inventories.next_page_url" :disabled="!section.inventories.next_page_url" icon="next" variant="secondary" size="sm" preserve-scroll>Next</AppButton>
                        </nav>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>

    <Modal :show="!!inventoryToConfirm" :closeable="!confirmationForm.processing" max-width="lg" aria-labelledby="inventory-confirmation-title" aria-describedby="inventory-confirmation-description" @close="cancelConfirmation" @closed="focusActionResults">
        <form v-if="inventoryToConfirm" class="p-6 sm:p-8" aria-labelledby="inventory-confirmation-title" aria-describedby="inventory-confirmation-description" @submit.prevent="executeConfirmation">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full text-xl" :class="confirmationAction === 'delete' ? 'bg-red-100 text-red-700' : 'bg-purple-100 text-purple-700'" aria-hidden="true">
                <i :class="confirmationAction === 'delete' ? 'fa fa-trash' : 'fa fa-undo'"></i>
            </div>
            <h2 id="inventory-confirmation-title" class="text-xl font-bold text-slate-900">{{ confirmationAction === 'delete' ? 'Delete' : 'Restore' }} {{ inventoryToConfirm.name }}?</h2>
            <p id="inventory-confirmation-description" class="mt-3 text-sm leading-relaxed text-slate-600">
                {{ confirmationAction === 'delete' ? 'This inventory will move to the deleted list for all members. Its items and transaction records will be retained, and you can restore it afterward.' : 'This inventory will return to the active list for all members. Its existing details, memberships, items, and transaction records will be preserved.' }}
            </p>
            <div v-if="confirmationForm.hasErrors" role="alert" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <p v-for="(message, field) in confirmationForm.errors" :key="field">{{ message }}</p>
            </div>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <AppButton variant="secondary" autofocus :disabled="confirmationForm.processing" @click="cancelConfirmation">Cancel</AppButton>
                <AppButton type="submit" :variant="confirmationAction === 'delete' ? 'danger' : 'success'" :icon="confirmationAction" :disabled="confirmationForm.processing" :aria-label="`Confirm ${confirmationAction} ${inventoryToConfirm.name}`">{{ confirmationForm.processing ? (confirmationAction === 'delete' ? 'Deleting…' : 'Restoring…') : 'Confirm' }}</AppButton>
            </div>
        </form>
    </Modal>

</template>
