<script setup>
import { onUnmounted, ref, watch } from 'vue';
import AppButton from '@/Components/AppButton.vue';

const props = defineProps({
    result: { type: Object, required: true },
});

const dismissed = ref(false);
const fading = ref(false);
const banner = ref(null);
let dismissTimer;
let fadeTimer;

function clearTimers() {
    clearTimeout(dismissTimer);
    clearTimeout(fadeTimer);
}

function dismissImmediately() {
    clearTimers();
    fading.value = false;
    dismissed.value = true;
}

function handleInteraction(event) {
    clearTimers();
    fading.value = false;

    const button = event.target.closest('button, a');
    if (button && !button.disabled && button.getAttribute('aria-disabled') !== 'true') {
        // Removing the banner is scheduled after the clicked button's handler runs.
        dismissImmediately();
    }
}

defineExpose({
    focus: () => banner.value?.focus({ preventScroll: true }),
});
watch(() => props.result, (_result, _previous, onCleanup) => {
    clearTimers();
    dismissed.value = false;
    fading.value = false;
    dismissTimer = setTimeout(() => {
        fading.value = true;
        fadeTimer = setTimeout(() => { dismissed.value = true; }, 1000);
    }, 1000);
    onCleanup(clearTimers);
}, { immediate: true });

onUnmounted(clearTimers);
</script>

<template>
    <div
        v-if="!dismissed"
        ref="banner"
        tabindex="-1"
        :role="result.status === 'error' ? 'alert' : 'status'"
        aria-atomic="true"
        class="action-result-banner mb-8 rounded-xl border border-l-4 bg-white p-5 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 sm:p-6"
        :class="[result.status === 'error' ? 'border-red-200 border-l-red-500' : 'border-emerald-200 border-l-emerald-500', { 'is-fading': fading }]"
        @click.capture="handleInteraction"
    >
        <div class="flex items-start gap-4">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xl" :class="result.status === 'error' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'" aria-hidden="true">
            <i :class="result.status === 'error' ? 'fa fa-exclamation-circle' : 'fa fa-check-circle'"></i>
        </span>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-bold text-slate-900">{{ result.status === 'error' ? 'Action could not be completed' : 'Action completed successfully' }}</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ result.message }}</p>
            <p v-if="result.organization && !result.message.startsWith(result.organization.name + ':')" class="mt-2 break-words text-sm font-bold text-slate-800">{{ result.organization.name }}</p>
            <ul v-if="result.details?.length" class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                <li v-for="detail in result.details" :key="detail">{{ detail }}</li>
            </ul>
        </div>
        <button type="button" aria-label="Dismiss action result" class="shrink-0 rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" @click="dismissImmediately">
            <i class="fa fa-times" aria-hidden="true"></i>
        </button>
        </div>
        <div v-if="$slots.actions" class="mt-5 flex flex-wrap items-center justify-center gap-3 border-t border-slate-100 pt-4">
            <slot name="actions" />
            <AppButton variant="secondary" @click="dismissImmediately">Done</AppButton>
        </div>
    </div>
</template>

<style scoped>
.action-result-banner {
    opacity: 1;
}

.action-result-banner.is-fading {
    opacity: 0;
    transition: opacity 1s ease-out;
}

@media (prefers-reduced-motion: reduce) {
    .action-result-banner.is-fading {
        transition: none;
    }
}
</style>
