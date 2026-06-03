<script setup>
import { computed } from 'vue';
import MacedoniaMap from './MacedoniaMap.vue';
import PrimaryButton from './PrimaryButton.vue';

const props = defineProps({
    categorySlug: {
        type: String,
        default: '',
    },
    lessonSlug: {
        type: String,
        default: '',
    },
});

const panels = {
    'macedonian-language': {
        eyebrow: 'Phrase practice',
        title: 'Everyday Macedonian',
        intro: 'Short phrases learners can say aloud and recognise at home.',
        items: [
            { title: 'Здраво', detail: 'Hello' },
            { title: 'Добро утро', detail: 'Good morning' },
            { title: 'Благодарам', detail: 'Thank you' },
            { title: 'Како си?', detail: 'How are you?' },
        ],
    },
    'macedonian-alphabet': {
        eyebrow: 'Cyrillic builder',
        title: 'Letters into words',
        intro: 'Recognise letters first, then connect them to simple words.',
        letters: ['А', 'Б', 'В', 'Г', 'Д', 'М', 'К'],
        items: [
            { title: 'Македонија', detail: 'Macedonia' },
            { title: 'мајка', detail: 'mother' },
            { title: 'татко', detail: 'father' },
            { title: 'добро', detail: 'good' },
        ],
    },
    geography: {
        eyebrow: 'Map practice',
        title: 'Places on the map',
        intro: 'Connect cities, lakes, and landmarks to real locations.',
        items: [
            { title: 'Skopje', detail: 'Capital city' },
            { title: 'Ohrid', detail: 'Lake city' },
            { title: 'Bitola', detail: 'South-west city' },
            { title: 'Lake Ohrid', detail: 'Famous lake' },
            { title: 'Lake Prespa', detail: 'South-west lake' },
        ],
    },
    'history-of-macedonia': {
        eyebrow: 'Timeline thinking',
        title: 'Stories and memory',
        intro: 'History becomes easier when learners connect places, people, and traditions.',
        items: [
            { title: 'Family stories', detail: 'Listen and ask questions' },
            { title: 'Old places', detail: 'Connect history to geography' },
            { title: 'Museums', detail: 'Look for objects and context' },
            { title: 'Traditions', detail: 'Notice what families preserve' },
            { title: 'Language', detail: 'Words carry memory' },
        ],
    },
    'culture-and-traditions': {
        eyebrow: 'Heritage patterns',
        title: 'Community life',
        intro: 'Culture is learned through repeated family and community moments.',
        items: [
            { title: 'Family', detail: 'People and belonging' },
            { title: 'Oro', detail: 'Dance and rhythm' },
            { title: 'Weddings', detail: 'Customs and celebration' },
            { title: 'Holidays', detail: 'Food, visits, and songs' },
            { title: 'Music', detail: 'Shared memory' },
        ],
    },
    'food-and-music': {
        eyebrow: 'Taste and rhythm',
        title: 'Food words that stick',
        intro: 'Dishes and songs make vocabulary easier to remember.',
        items: [
            { title: 'Tavče gravče', detail: 'Bean dish' },
            { title: 'Ajvar', detail: 'Pepper spread' },
            { title: 'Shopska salad', detail: 'Shared meal' },
            { title: 'Folk music', detail: 'Celebration sound' },
            { title: 'Shared meals', detail: 'Family language' },
        ],
    },
    'folklore-songs': {
        eyebrow: 'Folklore song',
        title: 'Lyrics, rhythm, memory',
        intro: 'Learn song titles, safe lyric excerpts, vocabulary, and cultural context before original audio is added.',
        items: [
            { title: 'Title phrase', detail: 'Read the Macedonian line aloud' },
            { title: 'Meaning', detail: 'Connect words with English context' },
            { title: 'Vocabulary', detail: 'Save useful song words' },
            { title: 'Listen later', detail: 'Original audio clips coming soon' },
        ],
    },
};

const panel = computed(() => panels[props.categorySlug] || {
    eyebrow: 'Learning module',
    title: 'Read, practise, review',
    intro: 'Build understanding with a short lesson, then check it with a quiz.',
    items: [
        { title: 'Read', detail: 'Understand the idea' },
        { title: 'Practise', detail: 'Try examples' },
        { title: 'Review', detail: 'Return after the quiz' },
    ],
});

const isGeography = computed(() => props.categorySlug === 'geography');
const isAlphabet = computed(() => props.categorySlug === 'macedonian-alphabet');
const isHistory = computed(() => props.categorySlug === 'history-of-macedonia');
const isSongLesson = computed(() => props.categorySlug === 'folklore-songs' || props.lessonSlug.startsWith('folklore-song-'));
const panelIcon = computed(() => {
    if (isSongLesson.value) {
        return 'FS';
    }

    if (props.categorySlug === 'food-and-music') {
        return 'FM';
    }

    if (props.categorySlug === 'macedonian-alphabet') {
        return 'А';
    }

    return props.categorySlug === 'geography' ? 'MAP' : 'IQ';
});
</script>

<template>
    <div class="overflow-hidden rounded-[2rem] border border-white/60 bg-white p-4 shadow-soft sm:p-5">
        <div class="rounded-[1.5rem] bg-heritage-panel p-4 sm:p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="label">{{ panel.eyebrow }}</p>
                    <h2 class="mt-2 text-2xl font-black leading-tight text-heritage-ink">{{ panel.title }}</h2>
                    <p class="mt-2 text-sm leading-6 text-heritage-muted">{{ panel.intro }}</p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-sm font-black text-heritage-red shadow-card">
                    {{ panelIcon }}
                </div>
            </div>
        </div>

        <div v-if="isSongLesson" class="mt-4 grid gap-4">
            <div class="rounded-[1.5rem] bg-heritage-navy p-5 text-white shadow-card">
                <p class="text-xs font-black uppercase text-heritage-gold">Lyric card</p>
                <h3 class="mt-3 text-2xl font-black">Read first, listen later</h3>
                <div class="mt-4 grid grid-cols-5 items-end gap-2">
                    <span class="h-10 rounded-full bg-heritage-red" />
                    <span class="h-16 rounded-full bg-heritage-gold" />
                    <span class="h-12 rounded-full bg-white/70" />
                    <span class="h-20 rounded-full bg-heritage-red" />
                    <span class="h-14 rounded-full bg-heritage-gold" />
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div v-for="item in panel.items" :key="item.title" class="rounded-2xl bg-white p-4 shadow-card">
                    <p class="text-lg font-black text-heritage-ink">{{ item.title }}</p>
                    <p class="mt-1 text-sm leading-6 text-heritage-muted">{{ item.detail }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-heritage-gold/40 bg-heritage-gold-faint p-4">
                <p class="text-sm font-black text-heritage-ink">Audio quiz coming soon</p>
                <p class="mt-1 text-sm font-bold leading-6 text-heritage-gold-deep">Future clips will use original MakedonIQ recordings.</p>
            </div>
        </div>

        <div v-else-if="isGeography" class="mt-4 grid gap-4">
            <div class="rounded-[1.5rem] bg-heritage-navy p-3">
                <MacedoniaMap :x="52" :y="35.5" target-type="city" variant="compact" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div v-for="item in panel.items" :key="item.title" class="rounded-2xl bg-heritage-gold-faint p-3">
                    <p class="text-sm font-black text-heritage-ink">{{ item.title }}</p>
                    <p class="mt-1 text-xs font-bold text-heritage-gold-deep">{{ item.detail }}</p>
                </div>
            </div>
            <PrimaryButton href="/map-challenge" variant="gold" class="w-full">Map Challenge</PrimaryButton>
        </div>

        <div v-else-if="isAlphabet" class="mt-4 grid gap-4">
            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                <div v-for="letter in panel.letters" :key="letter" class="flex aspect-square items-center justify-center rounded-2xl bg-white text-3xl font-black text-heritage-red shadow-card">
                    {{ letter }}
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div v-for="item in panel.items" :key="item.title" class="rounded-2xl bg-heritage-gold-faint p-3">
                    <p class="text-lg font-black text-heritage-ink">{{ item.title }}</p>
                    <p class="text-sm font-bold text-heritage-muted">{{ item.detail }}</p>
                </div>
            </div>
        </div>

        <div v-else-if="isHistory" class="mt-4">
            <div class="relative grid gap-3">
                <div class="absolute bottom-4 left-5 top-4 w-1 rounded-full bg-heritage-gold/70" />
                <div v-for="(item, index) in panel.items" :key="item.title" class="relative grid grid-cols-[2.5rem_1fr] gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-heritage-red text-sm font-black text-white shadow-card">
                        {{ index + 1 }}
                    </div>
                    <div class="rounded-2xl bg-white p-3 shadow-card">
                        <p class="font-black text-heritage-ink">{{ item.title }}</p>
                        <p class="mt-1 text-sm leading-6 text-heritage-muted">{{ item.detail }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="mt-4 grid gap-3 sm:grid-cols-2">
            <div v-for="item in panel.items" :key="item.title" class="rounded-2xl bg-white p-4 shadow-card">
                <p class="text-lg font-black text-heritage-ink">{{ item.title }}</p>
                <p class="mt-1 text-sm leading-6 text-heritage-muted">{{ item.detail }}</p>
            </div>
        </div>
    </div>
</template>
