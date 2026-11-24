<script setup>
import AppButton from '@/Components/AppButton.vue';
import InventoryItemList from '@/Components/InventoryItemList.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import indexStyles from '@/Styles/indexStyles';
import { Head } from '@inertiajs/vue3';

defineProps({
    inventory: { type: Object, required: true },
    items: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Items for ${inventory.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <SiteHeader :title="`Items for ${inventory.name}`" icon="fa-archive">
                <template #actions>
                    <AppButton :href="route('inventories.show', inventory.id)" variant="secondary" icon="previous">Back to Inventory</AppButton>
                </template>
            </SiteHeader>
        </template>

        <div class="py-12">
            <div :class="indexStyles.container">
                <InventoryItemList :items="items.data" :total="items.total">
                    <nav v-if="items.last_page > 1" aria-label="Inventory item pages" :class="[indexStyles.pagination, 'flex-wrap gap-3']">
                        <AppButton :href="items.prev_page_url" :disabled="!items.prev_page_url" icon="previous" variant="secondary" size="sm" preserve-scroll>Previous</AppButton>
                        <span class="text-gray-600">Page {{ items.current_page }} of {{ items.last_page }}</span>
                        <AppButton :href="items.next_page_url" :disabled="!items.next_page_url" icon="next" variant="secondary" size="sm" preserve-scroll>Next</AppButton>
                    </nav>
                </InventoryItemList>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
