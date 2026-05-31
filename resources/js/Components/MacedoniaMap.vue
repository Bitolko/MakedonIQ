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

const mapAriaLabel = computed(() => `Stylised Macedonia terrain map with a highlighted ${targetTypeLabel.value.toLowerCase()} marker.`);

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
        class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[#111817] p-4 shadow-[0_24px_60px_rgba(16,24,24,0.28)]"
        role="img"
        :aria-label="mapAriaLabel"
    >
        <span class="sr-only">The highlighted marker is positioned visually on the illustrated map.</span>

        <div class="pointer-events-none absolute inset-0 opacity-70" style="background-image: radial-gradient(circle at 22% 18%, rgba(244,196,48,0.20), transparent 26%), radial-gradient(circle at 78% 70%, rgba(0,172,153,0.16), transparent 28%), linear-gradient(135deg, #141b1a, #0f1317);" />
        <div class="pointer-events-none absolute inset-0 opacity-30" style="background-image: repeating-linear-gradient(135deg, transparent 0 18px, rgba(244,196,48,0.10) 18px 20px);" />

        <div class="absolute left-4 top-4 z-20 rounded-full bg-white/95 px-3 py-2 text-[0.68rem] font-black uppercase leading-tight text-heritage-red shadow-card sm:left-5 sm:top-5 sm:text-xs">
            Highlighted {{ targetTypeLabel }}
        </div>

        <div class="relative aspect-[1.35/1] overflow-hidden rounded-[1.5rem]">
            <svg
                aria-hidden="true"
                class="absolute inset-0 h-full w-full"
                preserveAspectRatio="xMidYMid meet"
                viewBox="0 0 720 520"
            >
                <defs>
                    <linearGradient id="terrainTop" x1="8%" x2="92%" y1="3%" y2="97%">
                        <stop offset="0%" stop-color="#8fe7b0" />
                        <stop offset="34%" stop-color="#2ba876" />
                        <stop offset="70%" stop-color="#0d7567" />
                        <stop offset="100%" stop-color="#074842" />
                    </linearGradient>
                    <linearGradient id="terrainEdge" x1="0%" x2="100%" y1="0%" y2="100%">
                        <stop offset="0%" stop-color="#f4c430" />
                        <stop offset="50%" stop-color="#a40000" />
                        <stop offset="100%" stop-color="#5c130d" />
                    </linearGradient>
                    <linearGradient id="lakeFill" x1="0%" x2="100%" y1="0%" y2="100%">
                        <stop offset="0%" stop-color="#7ee9ff" />
                        <stop offset="100%" stop-color="#137b99" />
                    </linearGradient>
                    <filter id="terrainShadow" x="-20%" y="-20%" width="140%" height="150%">
                        <feDropShadow dx="0" dy="22" stdDeviation="18" flood-color="#000000" flood-opacity="0.45" />
                    </filter>
                    <filter id="softGlow" x="-40%" y="-40%" width="180%" height="180%">
                        <feGaussianBlur stdDeviation="8" result="blur" />
                        <feMerge>
                            <feMergeNode in="blur" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                </defs>

                <rect width="720" height="520" rx="42" fill="transparent" />

                <ellipse cx="365" cy="402" rx="230" ry="42" fill="#000000" opacity="0.34" />

                <path
                    d="M141 232C113 205 119 164 155 137C191 110 231 124 271 105C319 82 352 106 391 91C435 75 464 104 501 103C548 102 581 135 582 174C627 191 653 222 633 260C660 302 627 351 577 351C548 380 507 383 473 363C438 398 389 392 361 359C325 379 282 367 260 331C218 342 177 322 174 286C139 279 116 249 141 232Z"
                    fill="url(#terrainEdge)"
                    filter="url(#terrainShadow)"
                    opacity="0.95"
                    transform="translate(0 24)"
                />

                <path
                    d="M141 232C113 205 119 164 155 137C191 110 231 124 271 105C319 82 352 106 391 91C435 75 464 104 501 103C548 102 581 135 582 174C627 191 653 222 633 260C660 302 627 351 577 351C548 380 507 383 473 363C438 398 389 392 361 359C325 379 282 367 260 331C218 342 177 322 174 286C139 279 116 249 141 232Z"
                    fill="url(#terrainTop)"
                    stroke="#f4c430"
                    stroke-opacity="0.38"
                    stroke-width="3"
                />

                <path d="M180 220C230 183 289 195 333 168C373 145 425 146 483 172C520 188 552 188 594 206" fill="none" stroke="#dff7c6" stroke-linecap="round" stroke-opacity="0.48" stroke-width="10" />
                <path d="M194 260C240 233 293 238 344 217C395 196 443 207 493 232C532 252 571 247 616 266" fill="none" stroke="#dff7c6" stroke-linecap="round" stroke-opacity="0.34" stroke-width="8" />
                <path d="M240 319C285 296 326 292 370 308C411 322 443 310 486 285" fill="none" stroke="#083b35" stroke-linecap="round" stroke-opacity="0.34" stroke-width="10" />
                <path d="M288 148L319 203L346 152L384 219L421 142L466 229L506 166" fill="none" stroke="#fff8d7" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.56" stroke-width="8" />
                <path d="M288 148L319 203L346 152L384 219L421 142L466 229L506 166" fill="none" stroke="#0e5d54" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.25" stroke-width="18" />

                <path
                    d="M161 326C184 306 223 316 226 345C229 374 193 393 164 378C137 365 136 347 161 326Z"
                    fill="url(#lakeFill)"
                    filter="url(#softGlow)"
                    opacity="0.96"
                />
                <path
                    d="M127 286C148 270 174 277 179 298C185 324 154 338 131 321C111 306 108 300 127 286Z"
                    fill="url(#lakeFill)"
                    opacity="0.8"
                />
                <path
                    d="M566 275C586 266 610 276 611 296C612 315 588 325 567 315C548 306 548 284 566 275Z"
                    fill="url(#lakeFill)"
                    opacity="0.44"
                />

                <circle cx="374" cy="126" r="9" fill="#fff8d7" opacity="0.65" />
                <circle cx="453" cy="234" r="7" fill="#fff8d7" opacity="0.44" />
                <circle cx="269" cy="300" r="8" fill="#fff8d7" opacity="0.42" />
            </svg>

            <div class="absolute h-0 w-0 -translate-x-1/2 -translate-y-1/2" :style="markerStyle" aria-hidden="true">
                <span class="absolute -left-8 -top-8 h-16 w-16 animate-ping rounded-full bg-heritage-gold/30" />
                <span class="absolute -left-6 -top-6 h-12 w-12 rounded-full border-4 border-white bg-heritage-red shadow-[0_16px_28px_rgba(164,0,0,0.45)]" />
                <span class="absolute -left-2.5 -top-2.5 h-5 w-5 rounded-full bg-heritage-gold shadow-[0_0_20px_rgba(244,196,48,0.85)]" />
                <span class="absolute left-0 top-5 h-8 w-1 -translate-x-1/2 rotate-12 rounded-full bg-heritage-red-dark/70" />
                <span v-if="showLabel && label" class="absolute left-7 top-0 max-w-[10rem] rounded-full bg-white px-3 py-2 text-left text-xs font-black leading-tight text-heritage-ink shadow-card">
                    {{ label }}
                </span>
            </div>
        </div>
    </div>
</template>
