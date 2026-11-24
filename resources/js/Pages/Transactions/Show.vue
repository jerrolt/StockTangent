<script setup>
import AppButton from '@/Components/AppButton.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import indexStyles from '@/Styles/indexStyles';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    transaction: { type: Object, required: true },
    inventory: { type: Object, required: true },
    items: { type: Object, required: true },
});

const dateTime = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' });
const unitPrice = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD', maximumFractionDigits: 4 });

function date(value) {
    return value ? dateTime.format(new Date(value)) : '—';
}

const title = computed(() => `Transaction #${props.transaction.id}`);
const details = computed(() => [
    { label: 'Date', value: date(props.transaction.created_at) },
    { label: `Transaction/Reference #`, value: `${props.transaction.reference_number ?? ' '}` },
    { label: 'Status', value: props.transaction.status, class: 'capitalize' },
    { label: 'Type', value: props.transaction.type.replaceAll('_', ' '), class: 'capitalize' },
    
    // { label: `Transaction (ID: ${props.transaction.id})`, value: `[REF #] ${props.transaction.reference_number ?? ' '}` },
    { label: `Inventory (ID: ${props.transaction.inventory_id})`, value: `${props.inventory.name}` },
    { label: 'Owner', value: props.inventory.owner?.firstname ?? '—' },
    // { label: 'User ID', value: props.transaction.user_id },
    // {label: 'User Name', value: props.transaction.user?.firstname && props.transaction.user?.lastname ? `${props.transaction.user.firstname} ${props.transaction.user.lastname}` : '—'},
    
    
    // { label: 'Reference Number', value: props.transaction.reference_number || '—' },
    
    // { label: 'Last Updated', value: date(props.transaction.updated_at) },
]);

const itemColumns = [
    // { key: 'id', label: 'Item ID' },
    // { key: 'transaction_id', label: 'Transaction ID' },
    // { key: 'inventory_item_id', label: 'Inventory Item ID' },
    // { key: 'product_id', label: 'Product ID' },
    { key: 'product', label: 'Item' },
    // { key: 'store_id', label: 'Store ID' },
    { key: 'store', label: 'Store' },
    { key: 'quantity', label: 'Quantity', numeric: true },
    { key: 'unit_price', label: 'Unit Price', numeric: true },
    // { key: 'notes', label: 'Notes' },
    { key: 'created_at', label: 'Created' },
    // { key: 'updated_at', label: 'Last Updated' },
];
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader :title="title" :description="inventory.name" icon="fa-exchange">
                <template #actions>
                    <AppButton :href="route('inventories.transactions', inventory.id)" variant="secondary" icon="previous">
                        Back to Transactions
                    </AppButton>
                </template>
            </SiteHeader>
        </template>

        <div class="py-12">
            <div :class="indexStyles.container">
                <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <dl class="grid gap-5 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="detail in details" :key="detail.label">
                            <dt class="font-bold text-slate-700">{{ detail.label }}</dt>
                            <dd class="mt-1 break-words text-slate-600" :class="detail.class">{{ detail.value }}</dd>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <dt class="font-bold text-slate-700">Notes</dt>
                            <dd class="mt-1 whitespace-pre-wrap break-words text-slate-600">{{ transaction.notes || '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <section aria-labelledby="transaction-items-heading" :class="indexStyles.section">
                    <div :class="indexStyles.sectionHeader">
                        <h2 id="transaction-items-heading" class="text-lg font-bold text-slate-900">Transaction Summary</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table aria-labelledby="transaction-items-heading" :class="indexStyles.table">
                            <thead :class="indexStyles.tableHead">
                                <tr>
                                    <th
                                        v-for="column in itemColumns"
                                        :key="column.key"
                                        scope="col"
                                        class="whitespace-nowrap"
                                        :class="[indexStyles.columnHeading, { 'text-right': column.numeric }]"
                                    >{{ column.label }}</th>
                                </tr>
                            </thead>
                            <tbody :class="indexStyles.tableBody">
                                <tr v-for="item in items.data" :key="item.id" :class="indexStyles.row">
                                    <!-- <th scope="row" :class="indexStyles.rowHeading">{{ item.id }}</th> -->
                                    <!-- <td :class="indexStyles.cell">{{ item.transaction_id }}</td> -->
                                    <!-- <td :class="indexStyles.cell">{{ item.inventory_item_id }}</td> -->
                                    <!-- <td :class="indexStyles.cell">{{ item.product_id }}</td> -->
                                    <td class="min-w-36 break-words" :class="indexStyles.cell">{{ item.product?.name ?? '—' }}</td>
                                    <!-- <td :class="indexStyles.cell">{{ item.store_id ?? '—' }}</td> -->
                                    <td class="min-w-36 break-words" :class="indexStyles.cell">{{ item.store?.name ?? '—' }}</td>
                                    <td class="text-right tabular-nums" :class="indexStyles.cell">{{ item.quantity }} {{ item.unit_of_measure }}</td>
                                    <td class="text-right tabular-nums" :class="indexStyles.cell">
                                        {{ item.unit_price == null ? '—' : unitPrice.format(item.unit_price) }}
                                    </td>
                                    <!-- <td class="min-w-48 whitespace-pre-wrap break-words" :class="indexStyles.cell">{{ item.notes || '—' }}</td> -->
                                    <td class="whitespace-nowrap" :class="indexStyles.cell">{{ date(item.created_at) }}</td>
                                    <!-- <td class="whitespace-nowrap" :class="indexStyles.cell">{{ date(item.updated_at) }}</td> -->
                                </tr>
                                <tr v-if="!items.data.length">
                                    <td :colspan="itemColumns.length" :class="indexStyles.empty">No items in this transaction.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav v-if="items.last_page > 1" aria-label="Transaction item pages" :class="[indexStyles.pagination, 'flex-wrap gap-3']">
                        <AppButton :href="items.prev_page_url" :disabled="!items.prev_page_url" icon="previous" variant="secondary" size="sm" preserve-scroll>Previous</AppButton>
                        <span class="text-gray-600">Page {{ items.current_page }} of {{ items.last_page }}</span>
                        <AppButton :href="items.next_page_url" :disabled="!items.next_page_url" icon="next" variant="secondary" size="sm" preserve-scroll>Next</AppButton>
                    </nav>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
