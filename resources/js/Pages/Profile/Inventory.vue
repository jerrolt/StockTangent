<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import indexStyles from '@/Styles/indexStyles';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ user: { type: Object, required: true } });
</script>

<template>
    <Head title="My Inventories" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold leading-tight text-gray-800">My Inventories</h1>
        </template>

        <div class="py-12" :class="indexStyles.container">
            <div :class="indexStyles.section" class="overflow-x-auto!">
                <table :class="indexStyles.table">
                    <caption class="sr-only">My Inventories</caption>
                    <thead :class="indexStyles.tableHead">
                        <tr>
                            <th scope="col" :class="indexStyles.columnHeading">Name</th>
                            <th scope="col" :class="indexStyles.columnHeading">Description</th>
                        </tr>
                    </thead>
                    <tbody :class="indexStyles.tableBody">
                        <tr v-for="inventory in props.user.inventories" :key="inventory.id" :class="indexStyles.row">
                            <th scope="row" :class="indexStyles.rowHeading">
                                <Link
                                    :href="route('inventories.show', inventory.id)"
                                    class="rounded underline decoration-slate-300 underline-offset-4 hover:text-blue-700 hover:decoration-blue-400 focus-visible:ring-2 focus-visible:ring-blue-500"
                                >{{ inventory.name }}</Link>
                            </th>
                            <td class="break-words" :class="indexStyles.cell">{{ inventory.description || '—' }}</td>
                        </tr>
                        <tr v-if="!props.user.inventories.length">
                            <td colspan="2" :class="indexStyles.empty">You don’t have any inventories yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
