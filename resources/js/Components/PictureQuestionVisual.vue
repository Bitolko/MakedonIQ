<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    metadata: {
        type: Object,
        default: () => ({}),
    },
    language: {
        type: String,
        default: 'en',
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const imageLoaded = ref(false);
const imageError = ref(false);

const imagePath = computed(() => {
    const path = String(props.metadata?.image_path || '').trim();

    return path.length ? path : null;
});

const normalizedLanguage = computed(() => String(props.language || 'en').toLowerCase());
const imageType = computed(() => String(props.metadata?.image_type || 'other').toLowerCase());
const canTryImage = computed(() => Boolean(imagePath.value) && !imageError.value);

const altText = computed(() => {
    const preferredAlt = normalizedLanguage.value === 'mk'
        ? props.metadata?.image_alt_mk
        : props.metadata?.image_alt_en;

    return preferredAlt || props.metadata?.image_alt_en || 'Picture quiz clue';
});

const placeholderLabel = computed(() => ({
    placeholder: 'IMG',
    food: 'BOWL',
    city: 'CITY',
    lake: 'WAVE',
    landmark: 'MARK',
    alphabet: 'Aa',
    culture: 'ART',
    music: 'NOTE',
    other: 'IMG',
}[imageType.value] || 'IMG'));

const placeholderToneClass = computed(() => ({
    placeholder: 'from-white via-heritage-gold-faint to-heritage-red-faint text-heritage-red',
    food: 'from-heritage-gold-faint via-white to-heritage-red-faint text-heritage-gold-deep',
    city: 'from-white via-heritage-panel to-heritage-gold-faint text-heritage-navy',
    lake: 'from-white via-sky-50 to-heritage-panel text-sky-800',
    landmark: 'from-heritage-panel via-white to-heritage-gold-faint text-heritage-red',
    alphabet: 'from-white via-heritage-gold-faint to-heritage-panel text-heritage-red',
    culture: 'from-heritage-red-faint via-white to-heritage-gold-faint text-heritage-red-dark',
    music: 'from-heritage-panel via-white to-heritage-red-faint text-heritage-navy',
    other: 'from-white via-heritage-panel to-heritage-gold-faint text-heritage-muted',
}[imageType.value] || 'from-white via-heritage-panel to-heritage-gold-faint text-heritage-muted'));

watch(imagePath, () => {
    imageLoaded.value = false;
    imageError.value = false;
});

function handleImageLoad() {
    imageLoaded.value = true;
}

function handleImageError() {
    imageLoaded.value = false;
    imageError.value = true;
}
</script>

<template>
    <figure class="overflow-hidden rounded-[1.5rem] border border-heritage-gold/30 bg-white shadow-card">
        <div :class="['relative overflow-hidden bg-heritage-panel', compact ? 'aspect-[16/9]' : 'aspect-[4/3] sm:aspect-[16/9]']">
            <img
                v-if="canTryImage"
                :src="imagePath"
                :alt="altText"
                :class="['absolute inset-0 h-full w-full object-cover transition-opacity duration-300', imageLoaded ? 'opacity-100' : 'opacity-0']"
                @load="handleImageLoad"
                @error="handleImageError"
            >

            <div
                v-if="!canTryImage || !imageLoaded"
                :class="['absolute inset-0 flex flex-col items-center justify-center bg-linear-to-br p-5 text-center', placeholderToneClass]"
            >
                <div class="rounded-full border border-white/80 bg-white/85 px-3 py-1 text-[0.68rem] font-black uppercase text-heritage-red shadow-card">
                    Picture Quiz
                </div>
                <div :class="['mt-5 flex items-center justify-center rounded-[1.25rem] border border-white/80 bg-white/75 font-black shadow-card', compact ? 'h-16 w-16 text-lg' : 'h-24 w-24 text-2xl']">
                    {{ placeholderLabel }}
                </div>
                <figcaption class="mt-4 max-w-sm">
                    <p :class="['font-black text-heritage-ink', compact ? 'text-base' : 'text-xl']">Image coming soon</p>
                    <p class="mt-1 text-sm font-bold leading-6 text-heritage-muted">This question is ready. The picture clue will be added later.</p>
                </figcaption>
            </div>
        </div>
    </figure>
</template>
