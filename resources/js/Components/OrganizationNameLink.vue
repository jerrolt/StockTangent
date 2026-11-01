<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import OrganizationSummary from '@/Components/OrganizationSummary.vue';

const props = defineProps({ organization: { type: Object, required: true } });
const preview = ref(null);
let closeTimer;

function keepOpen() {
    clearTimeout(closeTimer);
}

function close() {
    keepOpen();
    preview.value = null;
}

function scheduleClose() {
    keepOpen();
    closeTimer = setTimeout(close, 200);
}

function show(event) {
    keepOpen();
    const anchor = event.currentTarget.getBoundingClientRect();
    const width = Math.min(400, window.innerWidth - 32);
    const height = Math.min(440, window.innerHeight - 32);

    preview.value = {
        left: `${Math.max(16, Math.min(anchor.left, window.innerWidth - width - 16))}px`,
        top: `${anchor.bottom + height + 12 <= window.innerHeight ? anchor.bottom + 8 : Math.max(16, anchor.top - height - 8)}px`,
        width: `${width}px`,
        maxHeight: `${height}px`,
    };
}

function openSummary(event) {
    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;

    event.preventDefault();
    close();
    window.open(route('organizations.show', props.organization.id), '_blank', 'popup,width=900,height=750,noopener,noreferrer');
}

function escape(event) {
    if (event.key === 'Escape') close();
}

function dismissOnScroll(event) {
    // Allow scrolling inside the summary without dismissing it.
    if (!event.target.closest?.('[data-organization-summary]')) close();
}

onMounted(() => {
    window.addEventListener('resize', close);
    window.addEventListener('scroll', dismissOnScroll, true);
    window.addEventListener('keydown', escape);
});

onUnmounted(() => {
    keepOpen();
    window.removeEventListener('resize', close);
    window.removeEventListener('scroll', dismissOnScroll, true);
    window.removeEventListener('keydown', escape);
});
</script>

<template>
    <a
        :href="route('organizations.show', organization.id)"
        target="_blank"
        rel="noopener noreferrer"
        :aria-label="`${organization.name}: open organization summary in a new window`"
        class="rounded font-bold text-slate-900 underline decoration-slate-300 underline-offset-4 hover:text-blue-700 hover:decoration-blue-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
        @mouseenter="show"
        @mouseleave="scheduleClose"
        @focus="show"
        @blur="scheduleClose"
        @click="openSummary"
    >{{ organization.name }}</a>

    <Teleport to="body">
        <div
            v-if="preview"
            data-organization-summary
            role="region"
            :aria-label="`Summary of ${organization.name}`"
            class="fixed z-50 overflow-y-auto rounded-xl border border-blue-200 bg-white text-left shadow-2xl ring-1 ring-black/5"
            :style="preview"
            @mouseenter="keepOpen"
            @mouseleave="scheduleClose"
            @focusin="keepOpen"
            @focusout="scheduleClose"
        >
            <div class="flex items-start justify-between gap-3 border-b border-blue-100 bg-blue-50 px-5 py-3">
                <p class="break-words text-base font-bold text-slate-900">{{ organization.name }}</p>
                <button type="button" aria-label="Close organization summary" class="rounded px-1 text-slate-500 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-500" @click="close">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="p-5">
                <OrganizationSummary :organization="organization" compact />
            </div>
        </div>
    </Teleport>
</template>
