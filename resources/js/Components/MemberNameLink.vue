<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import MemberSummary from '@/Components/MemberSummary.vue';

const props = defineProps({ member: { type: Object, required: true } });
const memberName = computed(() => [props.member.firstname, props.member.lastname].filter(Boolean).join(' ') || props.member.name || props.member.email || 'Member');
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
    const height = Math.min(440, Math.max(0, anchor.top - 24));

    preview.value = {
        left: `${Math.max(16, Math.min(anchor.left, window.innerWidth - width - 16))}px`,
        bottom: `${window.innerHeight - anchor.top + 8}px`,
        width: `${width}px`,
        maxHeight: `${height}px`,
    };
}

function escape(event) {
    if (event.key === 'Escape') close();
}

function dismissOnScroll(event) {
    // Allow scrolling inside the summary without dismissing it.
    if (!event.target.closest?.('[data-member-summary]')) close();
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
        :href="route('members.show', member.id)"
        :aria-label="`${memberName}: view member summary`"
        class="rounded font-bold text-slate-900 underline decoration-slate-300 underline-offset-4 hover:text-blue-700 hover:decoration-blue-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
        @mouseenter="show"
        @mouseleave="scheduleClose"
        @focus="show"
        @blur="scheduleClose"
        @click="close"
    >{{ memberName }}</a>

    <Teleport to="body">
        <div
            v-if="preview"
            data-member-summary
            role="region"
            :aria-label="`Summary of ${memberName}`"
            class="fixed z-50 overflow-y-auto rounded-xl border border-blue-200 bg-white text-left shadow-2xl ring-1 ring-black/5"
            :style="preview"
            @mouseenter="keepOpen"
            @mouseleave="scheduleClose"
            @focusin="keepOpen"
            @focusout="scheduleClose"
        >
            <div class="flex items-start justify-between gap-3 border-b border-blue-100 bg-blue-50 px-5 py-3">
                <p class="break-words text-base font-bold text-slate-900">{{ memberName }}</p>
                <button type="button" aria-label="Close member summary" class="rounded px-1 text-slate-500 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-500" @click="close">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="p-5">
                <MemberSummary :member="member" compact />
            </div>
        </div>
    </Teleport>
</template>
