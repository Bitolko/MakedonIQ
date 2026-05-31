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
});

const targetTypeLabel = computed(() => ({
    city: 'City',
    lake: 'Lake',
    landmark: 'Landmark',
    region: 'Region',
})[props.targetType] || 'Place');

const mapAriaLabel = computed(() => `Stylised Macedonia map with a highlighted ${targetTypeLabel.value.toLowerCase()} marker.`);

const markerStyle = computed(() => ({
    left: `${clampPercent(props.x)}%`,
    top: `${clampPercent(props.y)}%`,
}));

function clampPercent(value) {
    const number = Number(value);

    if (!Number.isFinite(number)) {
        return 50;
    }

    return Math.min(92, Math.max(8, number));
}
</script>

<template>
    <div
        class="relative overflow-hidden rounded-[2rem] border border-heritage-gold/30 bg-linear-to-br from-white via-heritage-gold-faint to-heritage-red-faint p-4 shadow-card"
        role="img"
        :aria-label="mapAriaLabel"
    >
        <span class="sr-only">The highlighted marker is positioned visually on the illustrated map.</span>
        <div class="absolute left-3 top-3 z-10 max-w-[calc(100%-1.5rem)] rounded-full bg-white/90 px-3 py-2 text-[0.68rem] font-black uppercase leading-tight text-heritage-gold-deep shadow-card sm:left-5 sm:top-5 sm:text-xs">
            Highlighted {{ targetTypeLabel }}
        </div>

        <div class="relative aspect-[1.55/1] overflow-hidden rounded-[1.5rem] bg-heritage-panel">
            <svg
                aria-hidden="true"
                class="absolute inset-0 h-full w-full"
                preserveAspectRatio="xMidYMid meet"
                viewBox="0 0 640 420"
            >
                <defs>
                    <linearGradient id="mapTerrain" x1="0%" x2="100%" y1="0%" y2="100%">
                        <stop offset="0%" stop-color="#fff8e4" />
                        <stop offset="45%" stop-color="#f6d88a" />
                        <stop offset="100%" stop-color="#fffdf7" />
                    </linearGradient>
                    <linearGradient id="mapLake" x1="0%" x2="100%" y1="0%" y2="100%">
                        <stop offset="0%" stop-color="#e7f4ff" />
                        <stop offset="100%" stop-color="#82b8d8" />
                    </linearGradient>
                </defs>

                <rect width="640" height="420" rx="32" fill="#fffdf7" />
                <path
                    d="M134 86C185 48 264 58 302 82C343 107 389 90 434 113C497 145 538 210 516 260C496 306 441 310 412 341C371 384 300 370 265 344C230 318 187 326 150 298C102 262 77 202 94 154C102 130 112 104 134 86Z"
                    fill="url(#mapTerrain)"
                    stroke="#b00000"
                    stroke-opacity="0.4"
                    stroke-width="6"
                />
                <path
                    d="M166 304C189 286 224 297 227 325C230 350 198 370 170 357C144 346 139 324 166 304Z"
                    fill="url(#mapLake)"
                    opacity="0.9"
                />
                <path
                    d="M142 266C159 255 179 260 184 278C190 301 164 313 144 299C128 288 126 276 142 266Z"
                    fill="url(#mapLake)"
                    opacity="0.78"
                />
                <path d="M178 145C230 126 270 133 319 121C359 112 390 120 438 148" fill="none" stroke="#1f2a44" stroke-dasharray="8 12" stroke-opacity="0.18" stroke-width="6" />
                <path d="M170 234C229 210 283 213 334 197C389 180 431 196 479 231" fill="none" stroke="#1f2a44" stroke-dasharray="8 12" stroke-opacity="0.16" stroke-width="6" />
                <path d="M248 315C283 287 323 275 367 282C405 288 426 275 454 253" fill="none" stroke="#1f2a44" stroke-linecap="round" stroke-opacity="0.14" stroke-width="7" />
                <circle cx="330" cy="118" fill="#b00000" opacity="0.14" r="11" />
                <circle cx="405" cy="187" fill="#b00000" opacity="0.13" r="8" />
                <circle cx="280" cy="287" fill="#b00000" opacity="0.12" r="9" />
            </svg>

            <div class="absolute h-0 w-0 -translate-x-1/2 -translate-y-1/2" :style="markerStyle" aria-hidden="true">
                <span class="absolute -left-7 -top-7 h-14 w-14 animate-ping rounded-full bg-heritage-red/25" />
                <span class="absolute -left-5 -top-5 h-10 w-10 rounded-full border-4 border-white bg-heritage-red shadow-[0_10px_25px_rgba(176,0,0,0.35)]" />
                <span class="absolute -left-2 -top-2 h-4 w-4 rounded-full bg-heritage-gold" />
                <span v-if="showLabel && label" class="absolute left-6 top-1 max-w-[10rem] rounded-full bg-white px-3 py-2 text-left text-xs font-black leading-tight text-heritage-ink shadow-card">
                    {{ label }}
                </span>
            </div>
        </div>
    </div>
</template>
