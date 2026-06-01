<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import MacedoniaMap from '../../Components/MacedoniaMap.vue';
import {
    currentCategorySlug,
    currentLessonSlug,
    difficultyLabel,
    getLesson,
    learnCategoryUrl,
    learnUrl,
    localizedText,
    preferredLanguage,
} from '../../api/makedoniq';

const lesson = ref(null);
const isLoading = ref(true);
const error = ref('');
const language = ref(preferredLanguage() === 'mk' ? 'mk' : 'en');

const categorySlug = currentCategorySlug();
const lessonSlug = currentLessonSlug();

const title = computed(() => localizedText(lesson.value, 'title', language.value));
const summary = computed(() => localizedText(lesson.value, 'summary', language.value));
const categoryName = computed(() => localizedText(lesson.value?.category, 'name', language.value));
const content = computed(() => localizedText(lesson.value, 'content', language.value));
const paragraphs = computed(() => content.value.split(/\n{2,}/).map((item) => item.trim()).filter(Boolean));
const relatedQuizzes = computed(() => lesson.value?.related_quizzes || []);
const firstQuiz = computed(() => relatedQuizzes.value[0] || null);
const resolvedCategorySlug = computed(() => lesson.value?.category?.slug || categorySlug);
const categoryHref = computed(() => learnCategoryUrl(resolvedCategorySlug.value));

const isAlphabet = computed(() => resolvedCategorySlug.value === 'macedonian-alphabet');
const isGeography = computed(() => resolvedCategorySlug.value === 'geography');
const isLanguage = computed(() => resolvedCategorySlug.value === 'macedonian-language');
const isHistory = computed(() => resolvedCategorySlug.value === 'history-of-macedonia');
const isCulture = computed(() => resolvedCategorySlug.value === 'culture-and-traditions');
const isFoodMusic = computed(() => resolvedCategorySlug.value === 'food-and-music');

const heroBadge = computed(() => {
    if (isAlphabet.value) return 'Foundation series';
    if (isGeography.value) return 'Geography module';
    if (isLanguage.value) return 'Language lab';
    if (isHistory.value) return 'Story path';
    if (isCulture.value) return 'Heritage module';
    if (isFoodMusic.value) return 'Taste and rhythm';

    return 'Lesson';
});

const keyPoints = computed(() => {
    const source = paragraphs.value.length ? paragraphs.value : [summary.value].filter(Boolean);

    return source
        .flatMap((paragraph) => paragraph.split(/(?<=[.!?])\s+/))
        .map((item) => item.trim())
        .filter(Boolean)
        .slice(0, 3);
});

const alphabetLetters = [
    { letter: 'А', sound: 'ah' },
    { letter: 'Б', sound: 'beh' },
    { letter: 'В', sound: 'veh' },
    { letter: 'Г', sound: 'geh' },
    { letter: 'Д', sound: 'deh' },
    { letter: 'М', sound: 'em' },
    { letter: 'К', sound: 'kah' },
    { letter: 'О', sound: 'oh' },
    { letter: 'Р', sound: 'reh' },
    { letter: 'С', sound: 'seh' },
];

const alphabetWords = [
    { mk: 'Македонија', latin: 'Makedonija', en: 'Macedonia' },
    { mk: 'мајка', latin: 'majka', en: 'mother' },
    { mk: 'татко', latin: 'tatko', en: 'father' },
    { mk: 'добро', latin: 'dobro', en: 'good' },
];

const geographyCards = [
    { mk: 'Езеро', en: 'Lake', detail: 'Ohrid, Prespa, and Dojran' },
    { mk: 'Планина', en: 'Mountain', detail: 'Mavrovo, Pelister, and the western highlands' },
    { mk: 'Град', en: 'City', detail: 'Skopje, Bitola, Ohrid, and Prilep' },
    { mk: 'Река', en: 'River', detail: 'The Vardar valley connects regions and cities' },
];

const geographyLocations = [
    { title: 'Skopje', detail: 'Capital and cultural center.' },
    { title: 'Ohrid', detail: 'Lake city known for history and natural beauty.' },
    { title: 'Mavrovo', detail: 'Mountain region and national park.' },
];

const languageCards = [
    { title: 'Здраво', detail: 'Hello' },
    { title: 'Добро утро', detail: 'Good morning' },
    { title: 'Благодарам', detail: 'Thank you' },
    { title: 'Како си?', detail: 'How are you?' },
];

const historyCards = [
    { title: 'Places', detail: 'Explore cities, museums, and monuments.' },
    { title: 'Stories', detail: 'Learn beginner-friendly cultural context.' },
    { title: 'Memory', detail: 'Connect history with family traditions.' },
];

const cultureCards = [
    { title: 'Family', detail: 'Celebrations and connection.' },
    { title: 'Oro', detail: 'Traditional dance and community.' },
    { title: 'Holidays', detail: 'Customs, food, and music.' },
];

const foodMusicCards = [
    { title: 'Tavce gravce', detail: 'Classic bean dish.' },
    { title: 'Ajvar', detail: 'Pepper spread and family preparation.' },
    { title: 'Folk music', detail: 'Rhythm for weddings and celebrations.' },
];

const genericCards = computed(() => {
    if (isLanguage.value) return languageCards;
    if (isHistory.value) return historyCards;
    if (isCulture.value) return cultureCards;
    if (isFoodMusic.value) return foodMusicCards;

    return [
        { title: 'Read', detail: 'Build context with a short bilingual lesson.' },
        { title: 'Practise', detail: 'Take the related quiz when you are ready.' },
        { title: 'Review', detail: 'Return to the lesson from your results.' },
    ];
});

onMounted(async () => {
    try {
        const response = await getLesson(lessonSlug);
        lesson.value = response.data;

        if (lesson.value.category?.slug !== categorySlug) {
            error.value = 'This lesson belongs to a different Learn category.';
        }
    } catch (caughtError) {
        error.value = caughtError.status === 404
            ? 'This lesson could not be found.'
            : caughtError.message || 'Lesson content could not be loaded.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-8 md:py-12">
            <section v-if="isLoading" class="mx-auto max-w-6xl">
                <div class="soft-card min-h-96 animate-pulse p-8">
                    <div class="h-6 w-32 rounded-full bg-heritage-panel" />
                    <div class="mt-8 h-12 w-3/4 rounded-full bg-heritage-panel" />
                    <div class="mt-4 h-5 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-10 h-40 rounded-[2rem] bg-heritage-panel" />
                </div>
            </section>

            <section v-else-if="error" class="section-panel mx-auto max-w-4xl text-center">
                <AppBadge variant="red">Lesson unavailable</AppBadge>
                <h1 class="mt-4 text-3xl font-black text-heritage-ink">We could not load this lesson</h1>
                <p class="mx-auto mt-3 max-w-2xl text-heritage-muted">{{ error }}</p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <PrimaryButton :href="learnUrl()">Back to Learn</PrimaryButton>
                    <PrimaryButton :href="categoryHref" variant="soft">Category lessons</PrimaryButton>
                </div>
            </section>

            <template v-else>
                <div class="mb-6 flex flex-wrap items-center gap-2 text-sm font-bold text-heritage-muted">
                    <a :href="categoryHref" class="hover:text-heritage-red">Back to {{ categoryName }}</a>
                    <span>/</span>
                    <span class="text-heritage-ink">{{ title }}</span>
                </div>

                <section
                    :class="[
                        'relative overflow-hidden rounded-[2.5rem] border border-heritage-line/50 p-6 shadow-card md:p-10',
                        isGeography ? 'bg-heritage-navy text-white' : 'bg-heritage-panel text-heritage-ink',
                    ]"
                >
                    <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                        <div class="relative z-10">
                            <div class="flex flex-wrap gap-2">
                                <AppBadge :variant="isGeography ? 'gold' : 'red'">{{ heroBadge }}</AppBadge>
                                <AppBadge :variant="isGeography ? 'navy' : 'gold'">{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                                <span :class="['rounded-full px-4 py-2 text-xs font-black uppercase', isGeography ? 'bg-white/15 text-white' : 'bg-white text-heritage-muted shadow-card']">
                                    {{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span>
                                </span>
                            </div>
                            <h1 :class="['mt-5 max-w-3xl text-4xl font-black leading-tight md:text-6xl', isGeography ? 'text-white' : 'text-heritage-ink']">
                                {{ title }}
                            </h1>
                            <p :class="['mt-5 max-w-2xl text-lg leading-8', isGeography ? 'text-white/80' : 'text-heritage-muted']">
                                {{ summary }}
                            </p>
                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <PrimaryButton v-if="firstQuiz" :href="firstQuiz.start_url" size="lg">Take related quiz</PrimaryButton>
                                <PrimaryButton v-else href="/quizzes" size="lg">Explore quizzes</PrimaryButton>
                                <PrimaryButton v-if="isGeography" href="/map-challenge" variant="gold" size="lg">Map Challenge</PrimaryButton>
                            </div>
                        </div>

                        <div v-if="isGeography" class="rounded-[2rem] bg-black/25 p-4 shadow-soft">
                            <MacedoniaMap :x="22" :y="72" target-type="lake" variant="compact" />
                        </div>
                        <div v-else-if="isAlphabet" class="rounded-[2rem] bg-white p-5 shadow-soft">
                            <div class="grid grid-cols-5 gap-3">
                                <div v-for="item in alphabetLetters.slice(0, 10)" :key="item.letter" class="rounded-2xl bg-heritage-panel p-4 text-center">
                                    <p class="text-3xl font-black text-heritage-ink">{{ item.letter }}</p>
                                    <p class="mt-1 text-xs font-bold text-heritage-muted">{{ item.sound }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="rounded-[2rem] bg-white p-6 shadow-soft">
                            <p class="label">Learning module</p>
                            <div class="mt-5 grid gap-3">
                                <div v-for="card in genericCards.slice(0, 3)" :key="card.title" class="rounded-2xl bg-heritage-panel p-4">
                                    <h3 class="font-black text-heritage-ink">{{ card.title }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-heritage-muted">{{ card.detail }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8 grid gap-8 lg:grid-cols-[1fr_20rem]">
                    <article class="soft-card p-6 md:p-8">
                        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                            <div>
                                <AppBadge>Lesson</AppBadge>
                                <h2 class="mt-3 text-2xl font-black text-heritage-ink">Read and remember</h2>
                            </div>
                            <div class="rounded-full bg-heritage-panel p-1">
                                <button
                                    type="button"
                                    aria-label="Show English lesson text"
                                    :aria-pressed="language === 'en'"
                                    :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'en' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']"
                                    @click="language = 'en'"
                                >
                                    EN
                                </button>
                                <button
                                    type="button"
                                    aria-label="Show Macedonian lesson text"
                                    :aria-pressed="language === 'mk'"
                                    :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'mk' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']"
                                    @click="language = 'mk'"
                                >
                                    MK
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-5">
                            <p v-for="paragraph in paragraphs" :key="paragraph" class="text-base leading-8 text-heritage-muted md:text-lg md:leading-9">
                                {{ paragraph }}
                            </p>
                        </div>

                        <section v-if="isAlphabet" class="mt-8 grid gap-6">
                            <div class="rounded-[2rem] bg-heritage-panel p-6">
                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                    <h3 class="text-2xl font-black text-heritage-ink">The first steps</h3>
                                    <span class="text-sm font-bold text-heritage-muted">Letter recognition</span>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
                                    <div v-for="item in alphabetLetters" :key="`letter-${item.letter}`" class="rounded-2xl bg-white p-4 text-center shadow-card">
                                        <p class="text-4xl font-black text-heritage-ink">{{ item.letter }}</p>
                                        <p class="mt-1 text-xs font-bold text-heritage-muted">{{ item.sound }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="border-l-4 border-heritage-red pl-4 text-2xl font-black text-heritage-ink">Example words</h3>
                                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                    <div v-for="word in alphabetWords" :key="word.mk" class="overflow-hidden rounded-[1.5rem] bg-white shadow-card">
                                        <div class="flex h-28 items-center justify-center bg-heritage-panel">
                                            <p class="text-2xl font-black text-heritage-muted">{{ word.mk }}</p>
                                        </div>
                                        <div class="p-5">
                                            <p class="text-sm font-black text-heritage-red">{{ word.latin }}</p>
                                            <p class="mt-1 text-sm font-bold text-heritage-muted">{{ word.en }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section v-else-if="isGeography" class="mt-8 grid gap-6">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div v-for="card in geographyCards" :key="card.mk" class="rounded-[1.5rem] bg-heritage-panel p-6 text-center">
                                    <p class="text-3xl font-black text-heritage-ink">{{ card.mk }}</p>
                                    <p class="mt-1 text-sm font-black text-heritage-red">{{ card.en }}</p>
                                    <p class="mt-3 text-sm leading-6 text-heritage-muted">{{ card.detail }}</p>
                                </div>
                            </div>
                            <div class="rounded-[1.5rem] border-2 border-dashed border-heritage-gold bg-heritage-gold-faint p-6">
                                <h3 class="text-2xl font-black text-heritage-gold-deep">Did you know?</h3>
                                <p class="mt-3 leading-7 text-heritage-gold-deep">
                                    Lake Ohrid is one of Europe's oldest lakes, and Macedonia's mountains, valleys, and lakes shape daily life, food, travel, and local identity.
                                </p>
                            </div>
                        </section>

                        <section v-else class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div v-for="card in genericCards" :key="card.title" class="rounded-[1.5rem] bg-heritage-panel p-5">
                                <h3 class="text-xl font-black text-heritage-ink">{{ card.title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-heritage-muted">{{ card.detail }}</p>
                            </div>
                        </section>

                        <div class="mt-8 rounded-[1.5rem] bg-heritage-gold-faint p-5">
                            <p class="label text-heritage-gold-deep">Key points</p>
                            <ul class="mt-3 grid gap-2 text-sm font-bold leading-6 text-heritage-gold-deep">
                                <li v-for="point in keyPoints" :key="`key-${point}`" class="flex gap-2">
                                    <span>•</span>
                                    <span>{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                    </article>

                    <aside class="grid content-start gap-5">
                        <div v-if="isGeography" class="soft-card p-5">
                            <p class="label">Key locations</p>
                            <div class="mt-4 grid gap-3">
                                <div v-for="place in geographyLocations" :key="place.title" class="rounded-2xl bg-heritage-panel p-4">
                                    <h3 class="font-black text-heritage-ink">{{ place.title }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-heritage-muted">{{ place.detail }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="soft-card p-5">
                            <p class="label">Learning loop</p>
                            <h3 class="mt-3 text-2xl font-black text-heritage-ink">Ready to practise?</h3>
                            <p class="mt-3 text-sm leading-6 text-heritage-muted">Take the related quiz, then come back here to review anything that felt tricky.</p>
                            <PrimaryButton v-if="firstQuiz" :href="firstQuiz.start_url" class="mt-5 w-full">Take related quiz</PrimaryButton>
                            <PrimaryButton v-else href="/quizzes" class="mt-5 w-full">Explore quizzes</PrimaryButton>
                            <PrimaryButton v-if="isGeography" href="/map-challenge" variant="gold" class="mt-3 w-full">Map Challenge</PrimaryButton>
                        </div>
                        <div class="soft-card p-5">
                            <p class="label">Navigation</p>
                            <div class="mt-4 grid gap-3">
                                <PrimaryButton :href="categoryHref" variant="soft">Back to category</PrimaryButton>
                                <PrimaryButton :href="learnUrl()" variant="ghost">All lessons</PrimaryButton>
                            </div>
                        </div>
                    </aside>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
