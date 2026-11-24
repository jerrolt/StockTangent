<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    organizations: { type: Array, default: () => [] },
    inventory: { type: Object, default: () => ({}) },
});

const form = useForm({
    name: props.inventory.name ?? '',
    organization_id: props.inventory.organization_id ?? '',
    description: props.inventory.description ?? '',
});

const fields = [
    { key: 'name', label: 'Inventory name', autocomplete: 'off', max: 255, wide: true },
];

function submit() {
    if (props.inventory.id) {
        form.put(route('inventories.update', props.inventory.id));
    } else {
        form.post(route('inventories.store'));
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <div v-for="field in fields" :key="field.key" :class="{ 'sm:col-span-2': field.wide }">
                <InputLabel :for="field.key" :value="field.label" />
                <TextInput
                    :id="field.key"
                    v-model="form[field.key]"
                    type="text"
                    class="mt-1 block w-full"
                    :maxlength="field.max"
                    :autocomplete="field.autocomplete"
                    :required="field.key === 'name'"
                    :autofocus="field.key === 'name' ? true : undefined"
                    :aria-invalid="!!form.errors[field.key]"
                    :aria-describedby="form.errors[field.key] ? `${field.key}-error` : undefined"
                />
                <InputError :id="`${field.key}-error`" class="mt-2" :message="form.errors[field.key]" />
            </div>
        </div>

        <div v-if="!inventory.id">
            <InputLabel for="organization_id" value="Owner" />
            <select id="organization_id" v-model="form.organization_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :aria-invalid="!!form.errors.organization_id" aria-describedby="organization_id-error">
                <option value="">Personal inventory</option>
                <option v-for="organization in organizations" :key="organization.id" :value="organization.id">{{ organization.name }}</option>
            </select>
            <InputError id="organization_id-error" class="mt-2" :message="form.errors.organization_id" />
        </div>
        <div>
            <InputLabel for="description" value="Description" />
            <textarea
                id="description"
                v-model="form.description"
                rows="4"
                maxlength="10000"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :aria-invalid="!!form.errors.description"
                :aria-describedby="form.errors.description ? 'description-error' : undefined"
            />
            <InputError id="description-error" class="mt-2" :message="form.errors.description" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                {{ form.processing ? 'Saving…' : inventory.id ? 'Save changes' : 'Create inventory' }}
            </PrimaryButton>
            <Link :href="route('inventories.index')" class="rounded text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">Cancel</Link>
        </div>
    </form>
</template>
