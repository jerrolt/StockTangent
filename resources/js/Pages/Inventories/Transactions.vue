<script setup>
import AppButton from '@/Components/AppButton.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import indexStyles from '@/Styles/indexStyles';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    inventory: { type: Object, required: true },
});

const transactionDate = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' });
const quantityFormat = new Intl.NumberFormat(undefined, { maximumFractionDigits: 4 });
const unitPriceFormat = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' });
const referencePreview = ref(null);
let closeTimer;

function keepPreviewOpen() {
    clearTimeout(closeTimer);
}

function closePreview() {
    keepPreviewOpen();
    referencePreview.value = null;
}

function schedulePreviewClose() {
    keepPreviewOpen();
    closeTimer = setTimeout(closePreview, 200);
}

function showPreview(event, transaction) {
    keepPreviewOpen();
    const anchor = event.currentTarget.getBoundingClientRect();
    const width = Math.min(320, window.innerWidth - 32);
    const spaceBelow = window.innerHeight - anchor.bottom;
    const showBelow = spaceBelow >= 240 || spaceBelow >= anchor.top;

    referencePreview.value = {
        transaction,
        style: {
            left: `${Math.max(16, Math.min(anchor.left, window.innerWidth - width - 16))}px`,
            width: `${width}px`,
            maxHeight: `${Math.min(240, (showBelow ? spaceBelow : anchor.top) - 16)}px`,
            ...(showBelow ? { top: `${anchor.bottom}px` } : { bottom: `${window.innerHeight - anchor.top}px` }),
        },
    };
}

function dismissOnEscape(event) {
    if (event.key === 'Escape') closePreview();
}

function dismissOnScroll(event) {
    if (!event.target.closest?.('#transaction-reference-preview')) closePreview();
}

onMounted(() => {
    window.addEventListener('keydown', dismissOnEscape);
    window.addEventListener('resize', closePreview);
    window.addEventListener('scroll', dismissOnScroll, true);
});

onUnmounted(() => {
    keepPreviewOpen();
    window.removeEventListener('keydown', dismissOnEscape);
    window.removeEventListener('resize', closePreview);
    window.removeEventListener('scroll', dismissOnScroll, true);
});
</script>
<template>
    <Head :title="`Transactions for ${props.inventory.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader :title="`Transactions for ${props.inventory.name}`" icon="fa-archive" description="Inventory transactions">
                <template #actions>
                    <AppButton :href="route('inventories.show', props.inventory.id)" variant="secondary" icon="previous">Back to Inventory</AppButton>
                </template>
            </SiteHeader>
        </template>

        <div class="py-12">
            <div :class="indexStyles.container">
                <div :class="indexStyles.section">
                    <div class="overflow-x-auto">
                        <table :class="indexStyles.table">
                            <caption class="sr-only">Transactions for {{ props.inventory.name }}</caption>
                            <thead :class="indexStyles.tableHead">
                                <tr>
                                    <th scope="col" :class="indexStyles.columnHeading">Date</th>
                                    <th scope="col" :class="indexStyles.columnHeading">Type</th>
                                    <th scope="col" :class="indexStyles.columnHeading">Status</th>
                                    <th scope="col" :class="indexStyles.columnHeading">Reference Number</th>
                                    <th scope="col" :class="indexStyles.columnHeading">Notes</th>
                                    <th scope="col" :class="indexStyles.columnHeading"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody :class="indexStyles.tableBody">
                                <tr v-for="transaction in props.inventory.transactions" :key="transaction.id" :class="indexStyles.row">
                                    <td class="whitespace-nowrap" :class="indexStyles.cell">
                                        <time v-if="transaction.created_at" :datetime="transaction.created_at">
                                            {{ transactionDate.format(new Date(transaction.created_at)) }}
                                        </time>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="whitespace-nowrap capitalize" :class="indexStyles.cell">{{ transaction.type.replaceAll('_', ' ') }}</td>
                                    <td class="whitespace-nowrap capitalize" :class="indexStyles.cell">{{ transaction.status }}</td>
                                    <td class="break-words" :class="indexStyles.cell">
                                        <button
                                            v-if="transaction.reference_number"
                                            type="button"
                                            :aria-describedby="referencePreview?.transaction.id === transaction.id ? 'transaction-reference-preview' : undefined"
                                            class="rounded text-slate-900 underline decoration-slate-300 underline-offset-4 hover:text-blue-700 hover:decoration-blue-400 focus-visible:ring-2 focus-visible:ring-blue-500"
                                            @mouseenter="showPreview($event, transaction)"
                                            @mouseleave="schedulePreviewClose"
                                            @focus="showPreview($event, transaction)"
                                            @blur="schedulePreviewClose"
                                            @click="showPreview($event, transaction)"
                                        >{{ transaction.reference_number }}</button>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="whitespace-pre-line break-words" :class="indexStyles.cell">{{ transaction.notes || '—' }}</td>
                                    <td :class="indexStyles.actions">
                                        <Link
                                            :href="route('transactions.show', transaction.id)"
                                            :aria-label="`View transaction #${transaction.id}`"
                                            class="rounded font-medium text-blue-700 underline underline-offset-4 hover:text-blue-900 focus-visible:ring-2 focus-visible:ring-blue-500"
                                        >View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!props.inventory.transactions.length">
                                    <td colspan="6" :class="indexStyles.empty">No transactions for this inventory.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <Teleport to="body">
        <div
            v-if="referencePreview"
            id="transaction-reference-preview"
            role="tooltip"
            class="fixed z-50 overflow-y-auto rounded-xl border border-blue-200 bg-white text-sm shadow-2xl ring-1 ring-black/5"
            :style="referencePreview.style"
            @mouseenter="keepPreviewOpen"
            @mouseleave="schedulePreviewClose"
        >
            <div class="break-words border-b border-blue-100 bg-blue-50 px-4 py-2">
                <p class="font-bold text-slate-900">{{ referencePreview.transaction.reference_number }}</p>
                <p class="text-xs text-slate-600">Transaction ID #{{ referencePreview.transaction.id }}</p>
            </div>
            <div class="space-y-1 px-4 py-3 text-slate-700">
                <p class="font-semibold capitalize">{{ referencePreview.transaction.type.replaceAll('_', ' ') }}</p>
                <p class="whitespace-pre-line break-words">{{ referencePreview.transaction.notes || 'No notes.' }}</p>
            </div>
            <div class="space-y-2 border-t border-slate-200 px-4 py-3 text-slate-700">
                <p class="font-semibold text-slate-900">Transaction Items</p>
                <div v-if="referencePreview.transaction.items.length" class="overflow-x-auto">
                    <table aria-label="Transaction Items" :class="indexStyles.table">
                        <thead :class="indexStyles.tableHead">
                            <tr>
                                <th scope="col" class="px-2 py-1.5 font-bold">Product</th>
                                <th scope="col" class="px-2 py-1.5 text-right font-bold">Quantity</th>
                                <th v-if="referencePreview.transaction.type !== 'usage'" scope="col" class="px-2 py-1.5 text-right font-bold">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody :class="indexStyles.tableBody">
                            <tr v-for="item in referencePreview.transaction.items" :key="item.id" :class="indexStyles.row">
                                <th scope="row" class="break-words px-2 py-1.5 font-normal">
                                    {{ item.product?.name ?? `Product #${item.product_id}` }}
                                    <span class="block text-xs text-slate-500">Item ID #{{ item.id }}</span>
                                </th>
                                <td class="px-2 py-1.5 text-right tabular-nums">{{ quantityFormat.format(item.quantity) }}</td>
                                <td v-if="referencePreview.transaction.type !== 'usage'" class="px-2 py-1.5 text-right tabular-nums">
                                    {{ item.unit_price == null ? '—' : unitPriceFormat.format(item.unit_price) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else>No items in this transaction.</p>
            </div>
        </div>
    </Teleport>
</template>
