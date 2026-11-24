<script setup>
import SiteHeader from '@/Components/SiteHeader.vue';
import indexStyles from '@/Styles/indexStyles';

defineProps({
    items: { type: Array, required: true },
    total: { type: Number, default: null },
});
</script>

<template>
    <div :class="indexStyles.section">
        <SiteHeader
            title="Inventory Items"
            variant="secondary"
            tone="neutral"
            :count="total"
            :class="indexStyles.sectionHeader"
        />
        <div class="overflow-x-auto">
            <table aria-label="Inventory Items" :class="indexStyles.table">
                <thead :class="indexStyles.tableHead">
                    <tr>
                        <th scope="col" :class="indexStyles.columnHeading">Product</th>
                        <th scope="col" :class="indexStyles.columnHeading">SKU</th>
                        <th scope="col" :class="indexStyles.columnHeading">Quantity</th>
                        <th scope="col" :class="indexStyles.columnHeading">Reorder Level</th>
                    </tr>
                </thead>
                <tbody :class="indexStyles.tableBody">
                    <tr v-for="item in items" :key="item.id" :class="indexStyles.row">
                        <th scope="row" :class="indexStyles.rowHeading">
                            {{ item.product?.name ?? `Product #${item.product_id}` }}
                            <span v-if="item.deleted_at" class="block text-xs font-normal text-red-700">Deleted</span>
                        </th>
                        <td :class="indexStyles.cell">{{ item.product?.sku || '—' }}</td>
                        <td :class="indexStyles.cell">{{ item.quantity }} {{ item.product?.unit_of_measure }}</td>
                        <td :class="indexStyles.cell">{{ item.reorder_level ?? '—' }}</td>
                    </tr>
                    <tr v-if="!items.length">
                        <td colspan="4" :class="indexStyles.empty">No items in this inventory.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <slot />
    </div>
</template>
