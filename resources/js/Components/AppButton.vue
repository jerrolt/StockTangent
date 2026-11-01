<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, default: null },
    type: { type: String, default: 'button' },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'success', 'warning', 'danger', 'confirm'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md'].includes(value),
    },
    disabled: { type: Boolean, default: false },
    icon: {
        type: String,
        default: null,
        validator: (value) => ['create', 'edit', 'delete', 'restore', 'previous', 'next'].includes(value),
    },
});

const icons = {
    create: 'fa fa-plus-circle',
    edit: 'fa fa-pencil-square-o',
    delete: 'fa fa-trash',
    restore: 'fa fa-undo',
    previous: 'fa fa-chevron-left',
    next: 'fa fa-chevron-right',
};

const variants = {
    confirm: 'border-[#fbbf24] bg-[#92400e] text-amber-50 [--button-glow:#fbbf2466] hover:bg-[#78350f] active:bg-[#652d0d] focus-visible:ring-[#fbbf24]',
    primary: 'border-[#00a6ff] bg-[#005b99] text-blue-50 [--button-glow:#00a6ff66] hover:bg-[#004e85] active:bg-[#00416f] focus-visible:ring-[#00a6ff]',
    secondary: 'border-gray-400 bg-gray-600 text-gray-50 [--button-glow:#94a3b833] hover:bg-gray-700 active:bg-gray-800 focus-visible:ring-gray-400',
    success: 'border-[#c084fc] bg-[#6b21a8] text-purple-50 [--button-glow:#c084fc66] hover:bg-[#581c87] active:bg-[#4a1773] focus-visible:ring-[#c084fc]',
    warning: 'border-[#86efac] bg-[#347344] text-green-50 [--button-glow:#86efac66] hover:bg-[#2d643b] active:bg-[#265632] focus-visible:ring-[#86efac]',
    danger: 'border-[#ff5252] bg-[#b91c1c] text-red-50 [--button-glow:#ff525266] hover:bg-[#a31818] active:bg-[#8c1414] focus-visible:ring-[#ff5252]',
};

const sizes = {
    sm: 'min-h-9 px-3 py-1.5 text-xs',
    md: 'min-h-10 px-4 py-2 text-sm',
};
</script>

<template>
    <component
        :is="href && !disabled ? Link : 'button'"
        :href="href && !disabled ? href : undefined"
        :type="!href || disabled ? type : undefined"
        :disabled="disabled || undefined"
        :aria-disabled="disabled || undefined"
        class="app-button inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg border-2 font-bold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50"
        :class="[variants[variant], sizes[size]]"
    >
        <!-- Supply a custom SVG through #icon only when Font Awesome has no matching icon. -->
        <span v-if="icon || $slots.icon" class="inline-flex shrink-0 items-center justify-center" aria-hidden="true">
            <slot name="icon">
                <i :class="[icons[icon], 'fa-fw']"></i>
            </slot>
        </span>
        <slot />
    </component>
</template>

<style scoped>
.app-button {
    background-image: linear-gradient(180deg, rgb(255 255 255 / 12%), transparent 55%, rgb(0 0 0 / 12%));
    box-shadow:
        inset 0 1px 0 rgb(255 255 255 / 55%),
        inset 1px 0 0 rgb(255 255 255 / 20%),
        inset 0 -2px 0 rgb(0 0 0 / 25%),
        0 0 12px var(--button-glow);
    text-shadow: 0 1px 1px rgb(0 0 0 / 30%);
}

.app-button:active {
    box-shadow: inset 0 2px 4px rgb(0 0 0 / 30%), 0 0 6px var(--button-glow);
}
</style>
