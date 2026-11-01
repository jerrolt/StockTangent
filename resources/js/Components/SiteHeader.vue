<script setup>
defineProps({
    title: { type: String, required: true },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary'].includes(value),
    },
    tone: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'red', 'neutral'].includes(value),
    },
    displayIcons: { type: Boolean, default: true },
    icon: { type: String, default: null },
    count: { type: Number, default: null },
    description: { type: String, default: null },
    headingId: { type: String, default: null },
});

const tones = {
    blue: {
        heading: 'text-blue-800',
        accent: 'bg-blue-500',
        icon: 'border-blue-200 bg-blue-100 text-blue-700',
        badge: 'border-blue-200 bg-blue-100 text-blue-800',
        surface: 'bg-blue-50/60',
    },
    green: {
        heading: 'text-green-700',
        accent: 'bg-green-500',
        icon: 'border-green-200 bg-green-100 text-green-700',
        badge: 'border-green-200 bg-green-100 text-green-800',
        surface: 'bg-green-50/60',
    },
    red: {
        heading: 'text-red-700',
        accent: 'bg-red-500',
        icon: 'border-red-200 bg-red-100 text-red-700',
        badge: 'border-red-200 bg-red-100 text-red-800',
        surface: 'bg-red-50/60',
    },
    neutral: {
        heading: 'text-slate-800',
        accent: 'bg-slate-500',
        icon: 'border-slate-200 bg-slate-100 text-slate-700',
        badge: 'border-slate-200 bg-slate-100 text-slate-800',
        surface: 'bg-slate-50/60',
    },
};
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-4" :class="variant === 'secondary' ? tones[tone].surface : ''">
        <div class="flex min-w-0 items-center gap-3">
            <span v-if="variant === 'primary'" class="h-11 w-1.5 shrink-0 rounded-full" :class="tones[tone].accent" aria-hidden="true" />
            <span v-if="displayIcons && icon || $slots.icon" class="inline-flex shrink-0 items-center justify-center rounded-xl border" :class="[tones[tone].icon, variant === 'primary' ? 'h-11 w-11 text-xl' : 'h-9 w-9 text-base']" aria-hidden="true">
                <slot name="icon">
                    <i :class="['fa fa-fw', icon]"></i>
                </slot>
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <component
                        :is="variant === 'primary' ? 'h1' : 'h2'"
                        :id="headingId"
                        class="break-words font-bold tracking-tight"
                        :class="variant === 'primary' ? 'text-2xl leading-tight text-slate-900 sm:text-3xl' : ['text-lg leading-snug sm:text-xl', tones[tone].heading]"
                    >{{ title }}</component>
                    <span v-if="count !== null" class="inline-flex min-w-7 items-center justify-center rounded-full border px-2.5 py-0.5 text-xs font-bold tabular-nums" :class="tones[tone].badge">
                        <span class="sr-only">Total: </span>{{ count }}
                    </span>
                </div>
                <p v-if="description" class="mt-1 text-sm leading-relaxed text-slate-600">{{ description }}</p>
            </div>
        </div>
        <div v-if="$slots.actions" class="ms-auto flex shrink-0 flex-wrap items-center gap-3">
            <slot name="actions" />
        </div>
    </div>
</template>
