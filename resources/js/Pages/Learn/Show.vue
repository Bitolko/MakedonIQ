<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import LessonVisualPanel from '../../Components/LessonVisualPanel.vue';
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
const relatedQuizzes = computed(() => lesson.value?.related_quizzes || []);
const firstQuiz = computed(() => relatedQuizzes.value[0] || null);
const resolvedCategorySlug = computed(() => lesson.value?.category?.slug || categorySlug);
const categoryHref = computed(() => learnCategoryUrl(resolvedCategorySlug.value));
const categoryQuizHref = computed(() => `/quizzes/${resolvedCategorySlug.value}`);
const isGeography = computed(() => resolvedCategorySlug.value === 'geography');
const lessonBlocks = computed(() => parseLessonContent(content.value));
const lessonDetailRows = computed(() => [
    { label: 'Category', value: categoryName.value || 'Lesson' },
    { label: 'Difficulty', value: difficultyLabel(lesson.value?.difficulty) },
    { label: 'Reading time', value: lesson.value?.estimated_minutes ? `${lesson.value.estimated_minutes} min` : 'Self-paced' },
    { label: 'Languages', value: lesson.value?.content_mk ? 'English + Macedonian' : 'English' },
]);

const heroBadge = computed(() => ({
    'macedonian-language': 'Language lab',
    'macedonian-alphabet': 'Cyrillic builder',
    geography: 'Geography module',
    'history-of-macedonia': 'Story path',
    'culture-and-traditions': 'Heritage module',
    'food-and-music': 'Taste and rhythm',
})[resolvedCategorySlug.value] || 'Lesson module');

const keyPoints = computed(() => {
    const source = lessonBlocks.value
        .flatMap((block) => block.paragraphs.length ? block.paragraphs : block.items)
        .filter(Boolean);

    return (source.length ? source : [summary.value])
        .flatMap((paragraph) => String(paragraph).split(/(?<=[.!?])\s+/))
        .map((item) => item.trim())
        .filter(Boolean)
        .slice(0, 3);
});

const fallbackVocabulary = computed(() => {
    if (resolvedCategorySlug.value === 'food-and-music') {
        return [
            { term: 'грав', detail: 'beans' },
            { term: 'тавче', detail: 'small baking dish' },
            { term: 'храна', detail: 'food' },
            { term: 'песна', detail: 'song' },
            { term: 'оро', detail: 'dance' },
        ];
    }

    if (lessonSlug.includes('number')) {
        return [
            { term: 'еден', detail: 'one' },
            { term: 'два', detail: 'two' },
            { term: 'пет', detail: 'five' },
            { term: 'десет', detail: 'ten' },
            { term: 'дваесет', detail: 'twenty' },
        ];
    }

    if (resolvedCategorySlug.value === 'geography') {
        return [
            { term: 'град', detail: 'city' },
            { term: 'езеро', detail: 'lake' },
            { term: 'планина', detail: 'mountain' },
            { term: 'мапа', detail: 'map' },
        ];
    }

    if (resolvedCategorySlug.value === 'macedonian-alphabet') {
        return [
            { term: 'буква', detail: 'letter' },
            { term: 'звук', detail: 'sound' },
            { term: 'збор', detail: 'word' },
            { term: 'азбука', detail: 'alphabet' },
        ];
    }

    return [
        { term: 'здраво', detail: 'hello' },
        { term: 'благодарам', detail: 'thank you' },
        { term: 'семејство', detail: 'family' },
        { term: 'учење', detail: 'learning' },
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

function parseLessonContent(value) {
    return String(value || '')
        .split(/\n{2,}/)
        .map((chunk, index) => parseBlock(chunk, index))
        .filter((block) => block.heading || block.paragraphs.length || block.items.length);
}

function parseBlock(chunk, index) {
    const lines = chunk
        .trim()
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);
    const firstLine = lines[0] || '';
    const hasHeading = firstLine.endsWith(':');
    const heading = hasHeading ? firstLine.replace(/:$/, '') : '';
    const bodyLines = hasHeading ? lines.slice(1) : lines;
    const items = bodyLines
        .filter((line) => /^[-•]\s+/.test(line))
        .map((line) => line.replace(/^[-•]\s+/, '').trim());
    const paragraphs = bodyLines
        .filter((line) => !/^[-•]\s+/.test(line))
        .map((line) => line.trim());
    const type = sectionType(heading, index);

    return {
        heading: heading || fallbackHeading(type),
        type,
        bodyLines,
        items,
        paragraphs,
    };
}

function sectionType(heading, index) {
    const normalized = heading.toLowerCase();

    if (normalized.includes('what you will learn') || normalized.includes('што ќе научиш')) return 'learn';
    if (normalized.includes('explanation') || normalized.includes('објаснување')) return 'explanation';
    if (normalized.includes('examples') || normalized.includes('примери')) return 'examples';
    if (normalized.includes('vocabulary') || normalized.includes('facts') || normalized.includes('клучни')) return 'vocabulary';
    if (normalized.includes('practice') || normalized.includes('вежбање')) return 'practice';
    if (normalized.includes('remember') || normalized.includes('запомни')) return 'remember';
    if (normalized.includes('introduction') || normalized.includes('вовед') || index === 0) return 'introduction';

    return 'default';
}

function fallbackHeading(type) {
    return {
        introduction: 'Introduction',
        learn: 'What you will learn',
        explanation: 'Explanation',
        examples: 'Examples',
        vocabulary: 'Key words and facts',
        practice: 'Practice',
        remember: 'Remember',
    }[type] || 'Lesson section';
}

function sectionMeta(type) {
    return {
        introduction: {
            badge: 'Overview',
            marker: '01',
            card: 'border-heritage-line/50 bg-white',
            badgeVariant: 'navy',
            accent: 'bg-heritage-navy text-white',
        },
        learn: {
            badge: 'Goals',
            marker: '02',
            card: 'border-heritage-gold/50 bg-heritage-gold-faint',
            badgeVariant: 'gold',
            accent: 'bg-heritage-gold text-heritage-navy',
        },
        explanation: {
            badge: 'Explain',
            marker: '03',
            card: 'border-heritage-line/50 bg-white',
            badgeVariant: 'red',
            accent: 'bg-heritage-red text-white',
        },
        examples: {
            badge: 'Examples',
            marker: '04',
            card: 'border-heritage-line/50 bg-white shadow-soft',
            badgeVariant: 'navy',
            accent: 'bg-heritage-navy text-white',
        },
        vocabulary: {
            badge: 'Key words',
            marker: '05',
            card: 'border-heritage-gold/60 bg-white',
            badgeVariant: 'gold',
            accent: 'bg-heritage-gold text-heritage-navy',
        },
        practice: {
            badge: 'Practice',
            marker: '06',
            card: 'border-heritage-red/20 bg-heritage-red-faint',
            badgeVariant: 'red',
            accent: 'bg-heritage-red text-white',
        },
        remember: {
            badge: 'Remember',
            marker: '07',
            card: 'border-heritage-gold/60 bg-heritage-navy text-white',
            badgeVariant: 'gold',
            accent: 'bg-heritage-gold text-heritage-navy',
        },
        default: {
            badge: 'Lesson',
            marker: 'IQ',
            card: 'border-heritage-line/50 bg-white',
            badgeVariant: 'neutral',
            accent: 'bg-heritage-panel text-heritage-muted',
        },
    }[type] || sectionMeta('default');
}

function contentItems(block) {
    return block.items.length ? block.items : block.paragraphs;
}

function termCards(block) {
    const parsed = contentItems(block)
        .map((line) => {
            const parts = String(line).split(/\s+(?:=|-)\s+/);

            if (parts.length < 2) {
                return null;
            }

            return {
                term: parts[0].trim(),
                detail: parts.slice(1).join(' - ').trim(),
            };
        })
        .filter(Boolean);

    return parsed.length ? parsed : fallbackVocabulary.value;
}
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-8 md:py-12">
            <section v-if="isLoading" class="mx-auto max-w-7xl">
                <div class="soft-card min-h-96 animate-pulse p-8">
                    <div class="h-6 w-32 rounded-full bg-heritage-panel" />
                    <div class="mt-8 h-12 w-3/4 rounded-full bg-heritage-panel" />
                    <div class="mt-4 h-5 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-10 h-48 rounded-[2rem] bg-heritage-panel" />
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

                <section class="overflow-hidden rounded-[2.5rem] border border-heritage-line/50 bg-white p-5 shadow-card sm:p-6 md:p-8 lg:p-10">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.75fr)] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap gap-2">
                                <AppBadge variant="red">{{ heroBadge }}</AppBadge>
                                <AppBadge variant="gold">{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                                <AppBadge variant="navy">{{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes">&nbsp;min</span></AppBadge>
                            </div>
                            <h1 class="mt-5 max-w-4xl text-4xl font-black leading-tight text-heritage-red md:text-6xl">
                                {{ title }}
                            </h1>
                            <p class="mt-5 max-w-3xl text-lg leading-8 text-heritage-muted">
                                {{ summary }}
                            </p>
                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <PrimaryButton v-if="firstQuiz" :href="firstQuiz.start_url" size="lg" class="w-full sm:w-auto">Take related quiz</PrimaryButton>
                                <PrimaryButton v-else href="/quizzes" size="lg" class="w-full sm:w-auto">Explore quizzes</PrimaryButton>
                                <PrimaryButton :href="categoryQuizHref" variant="soft" size="lg" class="w-full sm:w-auto">Category quizzes</PrimaryButton>
                                <PrimaryButton v-if="isGeography" href="/map-challenge" variant="gold" size="lg" class="w-full sm:w-auto">Map Challenge</PrimaryButton>
                            </div>
                        </div>

                        <LessonVisualPanel :category-slug="resolvedCategorySlug" :lesson-slug="lessonSlug" />
                    </div>
                </section>

                <section class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] xl:grid-cols-[minmax(0,1fr)_24rem]">
                    <article class="min-w-0">
                        <div class="mb-5 flex flex-col justify-between gap-4 rounded-[2rem] border border-heritage-line/50 bg-white p-4 shadow-card sm:flex-row sm:items-center">
                            <div>
                                <AppBadge variant="gold">Lesson module</AppBadge>
                                <h2 class="mt-3 text-2xl font-black text-heritage-ink">Read, practise, remember</h2>
                            </div>
                            <div class="inline-flex rounded-full bg-heritage-panel p-1">
                                <button
                                    type="button"
                                    aria-label="Show English lesson text"
                                    :aria-pressed="language === 'en'"
                                    :class="['rounded-full px-4 py-2 text-xs font-black transition', language === 'en' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']"
                                    @click="language = 'en'"
                                >
                                    EN
                                </button>
                                <button
                                    type="button"
                                    aria-label="Show Macedonian lesson text"
                                    :aria-pressed="language === 'mk'"
                                    :class="['rounded-full px-4 py-2 text-xs font-black transition', language === 'mk' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']"
                                    @click="language = 'mk'"
                                >
                                    MK
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <section
                                v-for="(block, index) in lessonBlocks"
                                :key="`${index}-${block.heading}`"
                                :class="['rounded-[2rem] border p-5 shadow-card md:p-7', sectionMeta(block.type).card]"
                            >
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                                    <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-black shadow-card', sectionMeta(block.type).accent]">
                                        {{ sectionMeta(block.type).marker }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <AppBadge :variant="sectionMeta(block.type).badgeVariant">{{ sectionMeta(block.type).badge }}</AppBadge>
                                            <h3 :class="['text-2xl font-black', block.type === 'remember' ? 'text-white' : 'text-heritage-ink']">{{ block.heading }}</h3>
                                        </div>

                                        <div v-if="block.type === 'learn'" class="mt-5 grid gap-3 sm:grid-cols-2">
                                            <div v-for="item in contentItems(block)" :key="item" class="rounded-2xl bg-white/80 p-4 text-sm font-bold leading-6 text-heritage-gold-deep shadow-card">
                                                {{ item }}
                                            </div>
                                        </div>

                                        <div v-else-if="block.type === 'examples'" class="mt-5 grid gap-3">
                                            <div v-for="item in contentItems(block)" :key="item" class="rounded-2xl border border-heritage-line/40 bg-heritage-panel p-4">
                                                <p class="text-sm font-bold leading-7 text-heritage-muted">{{ item }}</p>
                                            </div>
                                        </div>

                                        <div v-else-if="block.type === 'vocabulary'" class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                            <div v-for="card in termCards(block)" :key="`${card.term}-${card.detail}`" class="rounded-2xl bg-heritage-gold-faint p-4 shadow-card">
                                                <p class="text-xl font-black text-heritage-ink">{{ card.term }}</p>
                                                <p class="mt-2 text-sm font-bold leading-6 text-heritage-gold-deep">{{ card.detail }}</p>
                                            </div>
                                        </div>

                                        <div v-else-if="block.type === 'practice'" class="mt-5 grid gap-3">
                                            <div v-for="item in contentItems(block)" :key="item" class="flex gap-3 rounded-2xl bg-white p-4 shadow-card">
                                                <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-heritage-red text-xs font-black text-white">Go</span>
                                                <p class="text-sm font-bold leading-7 text-heritage-muted">{{ item }}</p>
                                            </div>
                                        </div>

                                        <div v-else class="mt-5 grid gap-4">
                                            <p
                                                v-for="paragraph in block.paragraphs"
                                                :key="paragraph"
                                                :class="['text-base leading-8 md:text-lg md:leading-9', block.type === 'remember' ? 'text-white/85' : 'text-heritage-muted']"
                                            >
                                                {{ paragraph }}
                                            </p>
                                            <ul v-if="block.items.length" :class="['grid gap-3 text-sm font-bold leading-7', block.type === 'remember' ? 'text-white/85' : 'text-heritage-muted']">
                                                <li v-for="item in block.items" :key="item" class="rounded-2xl bg-white/10 px-4 py-3">{{ item }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <section class="mt-6 rounded-[2rem] border border-heritage-gold/50 bg-heritage-gold-faint p-5 shadow-card md:p-7">
                            <AppBadge variant="gold">Quick review</AppBadge>
                            <h2 class="mt-3 text-2xl font-black text-heritage-ink">Three things to remember</h2>
                            <div class="mt-5 grid gap-3 md:grid-cols-3">
                                <div v-for="point in keyPoints" :key="point" class="rounded-2xl bg-white p-4 shadow-card">
                                    <p class="text-sm font-bold leading-7 text-heritage-muted">{{ point }}</p>
                                </div>
                            </div>
                        </section>
                    </article>

                    <aside class="grid content-start gap-5 lg:sticky lg:top-28">
                        <section class="soft-card p-5">
                            <AppBadge variant="red">Ready to practise?</AppBadge>
                            <h2 class="mt-3 text-2xl font-black text-heritage-ink">Check your learning</h2>
                            <p class="mt-3 text-sm leading-6 text-heritage-muted">Use the quiz after reading, then return to this lesson for review.</p>
                            <PrimaryButton v-if="firstQuiz" :href="firstQuiz.start_url" class="mt-5 w-full">Take related quiz</PrimaryButton>
                            <PrimaryButton v-else href="/quizzes" class="mt-5 w-full">Explore quizzes</PrimaryButton>
                            <PrimaryButton v-if="isGeography" href="/map-challenge" variant="gold" class="mt-3 w-full">Map Challenge</PrimaryButton>
                        </section>

                        <section class="soft-card p-5">
                            <AppBadge variant="gold">Lesson details</AppBadge>
                            <dl class="mt-4 grid gap-3">
                                <div v-for="row in lessonDetailRows" :key="row.label" class="rounded-2xl bg-heritage-panel p-4">
                                    <dt class="label">{{ row.label }}</dt>
                                    <dd class="mt-1 font-black text-heritage-ink">{{ row.value }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="soft-card p-5">
                            <AppBadge variant="navy">Navigation</AppBadge>
                            <div class="mt-4 grid gap-3">
                                <PrimaryButton :href="categoryHref" variant="soft">Back to category</PrimaryButton>
                                <PrimaryButton :href="learnUrl()" variant="ghost">All lessons</PrimaryButton>
                            </div>
                        </section>

                        <section class="overflow-hidden rounded-[2rem] border border-heritage-line/50 bg-heritage-navy p-5 text-white shadow-card">
                            <p class="text-xs font-black uppercase text-heritage-gold">Suggested next</p>
                            <h2 class="mt-3 text-2xl font-black">{{ firstQuiz ? localizedText(firstQuiz, 'title', language) : categoryName }}</h2>
                            <p class="mt-3 text-sm leading-6 text-white/75">
                                {{ firstQuiz ? 'Try a short quiz connected to this lesson.' : 'Explore the full category path.' }}
                            </p>
                            <PrimaryButton v-if="firstQuiz" :href="firstQuiz.start_url" variant="gold" class="mt-5 w-full">Start quiz</PrimaryButton>
                            <PrimaryButton v-else :href="categoryHref" variant="gold" class="mt-5 w-full">Continue category</PrimaryButton>
                        </section>
                    </aside>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
