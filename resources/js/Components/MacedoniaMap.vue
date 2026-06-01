<script setup>
import { computed } from 'vue';

const props = defineProps({
    x: {
        type: [Number, String],
        default: 50,
    },
    y: {
        type: [Number, String],
        default: 50,
    },
    targetType: {
        type: String,
        default: 'place',
    },
    showLabel: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: '',
    },
    variant: {
        type: String,
        default: 'square',
    },
});

const targetTypeLabel = computed(() => ({
    city: 'City',
    lake: 'Lake',
    landmark: 'Landmark',
    mountain: 'Mountain',
    region: 'Region',
})[props.targetType] || 'Place');

const mapAriaLabel = computed(() => `Stylised Macedonia terrain map with a highlighted ${targetTypeLabel.value.toLowerCase()} marker.`);
const mapImageSrc = computed(() => props.variant === 'wide'
    ? '/images/makedonia-map-3d-wide.jpg'
    : '/images/makedonia-map-3d-square.jpg');

const aspectClass = computed(() => props.variant === 'wide' ? 'aspect-[43/24]' : 'aspect-square');
const paddingClass = computed(() => props.variant === 'compact' ? 'p-2 sm:p-3' : 'p-3 sm:p-4');

const markerStyle = computed(() => ({
    left: `${clampPercent(props.x)}%`,
    top: `${clampPercent(props.y)}%`,
}));

function clampPercent(value) {
    const number = Number(value);

    if (!Number.isFinite(number)) {
        return 50;
    }

    return Math.min(94, Math.max(6, number));
}
</script>

<template>
    <div
        :class="[
            'relative overflow-hidden rounded-[2rem] border border-white/10 bg-[#101416] shadow-[0_24px_60px_rgba(16,24,24,0.28)]',
            paddingClass,
        ]"
        role="img"
        :aria-label="mapAriaLabel"
    >
        <span class="sr-only">The highlighted marker is positioned visually on the illustrated map.</span>

        <div :class="['relative isolate overflow-hidden rounded-[1.5rem] bg-[#111817]', aspectClass]">
            <img
                aria-hidden="true"
                alt=""
                class="absolute inset-0 h-full w-full object-fill"
                loading="lazy"
                :src="mapImageSrc"
            >
            <div class="pointer-events-none absolute inset-0 bg-linear-to-b from-white/0 via-transparent to-black/10" />
            <div class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10" />

            <div class="absolute z-10 h-0 w-0 -translate-x-1/2 -translate-y-1/2" :style="markerStyle" aria-hidden="true">
                <span class="absolute -left-4 -top-4 h-8 w-8 animate-ping rounded-full bg-heritage-gold/20 sm:-left-5 sm:-top-5 sm:h-10 sm:w-10" />
                <span class="absolute -left-2.5 -top-2.5 h-5 w-5 rounded-full border-2 border-white bg-heritage-red shadow-[0_8px_18px_rgba(164,0,0,0.5)] ring-1 ring-heritage-gold/55 sm:-left-3 sm:-top-3 sm:h-6 sm:w-6" />
                <span class="absolute -left-1 -top-1 h-2 w-2 rounded-full bg-heritage-gold shadow-[0_0_14px_rgba(244,196,48,0.9)] sm:-left-1.5 sm:-top-1.5 sm:h-3 sm:w-3" />
                <span v-if="showLabel && label" class="absolute left-7 top-0 max-w-[10rem] rounded-full bg-white px-3 py-2 text-left text-xs font-black leading-tight text-heritage-ink shadow-card">
                    {{ label }}
                </span>
            </div>
        </div>
    </div>
</template>
