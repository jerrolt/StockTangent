<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    firstname: user.firstname,
    lastname: user.lastname,
    email: user.email,
    address_line1: user.address_line1 ?? '',
    address_line2: user.address_line2 ?? '',
    city: user.city ?? '',
    state: user.state ?? '',
    postal_code: user.postal_code ?? '',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information, email address, and location.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="firstname" value="First name" />

                <TextInput
                    id="firstname"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.firstname"
                    required
                    autofocus
                    autocomplete="given-name"
                />

                <InputError class="mt-2" :message="form.errors.firstname" />
            </div>

            <div>
                <InputLabel for="lastname" value="Last name" />

                <TextInput
                    id="lastname"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.lastname"
                    required
                    autocomplete="family-name"
                />

                <InputError class="mt-2" :message="form.errors.lastname" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <fieldset class="space-y-6">
                <legend class="text-base font-medium text-gray-900">Location</legend>

                <div>
                    <InputLabel for="address_line1" value="Address" />
                    <TextInput id="address_line1" v-model="form.address_line1" type="text" class="mt-1 block w-full" autocomplete="address-line1" maxlength="255" />
                    <InputError class="mt-2" :message="form.errors.address_line1" />
                </div>

                <div>
                    <InputLabel for="address_line2" value="Apartment, suite, etc. (optional)" />
                    <TextInput id="address_line2" v-model="form.address_line2" type="text" class="mt-1 block w-full" autocomplete="address-line2" maxlength="255" />
                    <InputError class="mt-2" :message="form.errors.address_line2" />
                </div>

                <div>
                    <InputLabel for="city" value="City" />
                    <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" autocomplete="address-level2" maxlength="100" />
                    <InputError class="mt-2" :message="form.errors.city" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <InputLabel for="state" value="State (2-letter code)" />
                        <TextInput id="state" v-model="form.state" type="text" class="mt-1 block w-full uppercase" autocomplete="address-level1" maxlength="2" />
                        <InputError class="mt-2" :message="form.errors.state" />
                    </div>
                    <div>
                        <InputLabel for="postal_code" value="ZIP code" />
                        <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" autocomplete="postal-code" maxlength="50" />
                        <InputError class="mt-2" :message="form.errors.postal_code" />
                    </div>
                </div>
            </fieldset>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
