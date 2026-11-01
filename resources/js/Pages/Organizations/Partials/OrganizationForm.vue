<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    organization: { type: Object, default: () => ({}) },
});

const form = useForm({
    name: props.organization.name ?? '',
    address_line1: props.organization.address_line1 ?? '',
    address_line2: props.organization.address_line2 ?? '',
    city: props.organization.city ?? '',
    state: props.organization.state ?? '',
    postal_code: props.organization.postal_code ?? '',
    description: props.organization.description ?? '',
});

const fields = [
    { key: 'name', label: 'Organization name', autocomplete: 'organization', max: 255, wide: true },
    { key: 'address_line1', label: 'Address line 1', autocomplete: 'address-line1', max: 255, wide: true },
    { key: 'address_line2', label: 'Address line 2', autocomplete: 'address-line2', max: 255, wide: true },
    { key: 'city', label: 'City', autocomplete: 'address-level2', max: 100 },
    { key: 'state', label: 'State (2-letter code)', autocomplete: 'address-level1', max: 2 },
    { key: 'postal_code', label: 'Postal code', autocomplete: 'postal-code', max: 50 },
];

function submit() {
    if (props.organization.id) {
        form.put(route('organizations.update', props.organization.id));
    } else {
        form.post(route('organizations.store'));
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
                {{ form.processing ? 'Saving…' : organization.id ? 'Save changes' : 'Create organization' }}
            </PrimaryButton>
            <Link :href="route('organizations.index')" class="rounded text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">Cancel</Link>
        </div>
    </form>
</template>
