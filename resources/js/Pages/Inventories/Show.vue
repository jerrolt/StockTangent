<script setup>
import AppButton from '@/Components/AppButton.vue';
import InventoryItemList from '@/Components/InventoryItemList.vue';
import InventorySummary from '@/Components/InventorySummary.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ inventory: { type: Object, required: true } });
</script>

<template>
    <Head :title="inventory.name" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader :title="inventory.name" icon="fa-archive" description="Inventory summary">
                <template #actions>
                    <AppButton :href="route('inventories.index')" variant="secondary" icon="previous">Inventories</AppButton>
                    <AppButton :href="route('inventories.items', inventory.id)" variant="secondary">Items</AppButton>
                    <AppButton :href="route('inventories.transactions', inventory.id)" variant="secondary">Transactions</AppButton>
                </template>
            </SiteHeader>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <InventorySummary :inventory="inventory" />
                </div>
                <InventoryItemList :items="inventory.items" class="mt-8" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
