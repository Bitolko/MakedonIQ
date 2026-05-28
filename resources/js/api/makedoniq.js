const legacyCategoryAliases = {
    history: 'history-of-macedonia',
};

const legacyQuizAliases = {
    history: 'macedonia-history-basics',
};

export async function fetchJson(url) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
        },
    });

    if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
    }

    return response.json();
}

export function quizPathParts() {
    return window.location.pathname.split('/').filter(Boolean);
}

export function currentCategorySlug() {
    const parts = quizPathParts();
    const slug = parts[1] || 'history-of-macedonia';

    return legacyCategoryAliases[slug] || slug;
}

export function currentQuizSlug() {
    const parts = quizPathParts();
    const categoryPart = parts[1] || 'history';
    const quizPart = parts[2];

    if (['start', 'active', 'results'].includes(quizPart)) {
        return legacyQuizAliases[categoryPart] || 'macedonia-history-basics';
    }

    return quizPart || legacyQuizAliases[categoryPart] || 'macedonia-history-basics';
}

export function categoryUrl(categorySlug) {
    return `/quizzes/${categorySlug}`;
}

export function quizStartUrl(categorySlug, quizSlug) {
    return `/quizzes/${categorySlug}/${quizSlug}/start`;
}

export function quizActiveUrl(categorySlug, quizSlug) {
    return `/quizzes/${categorySlug}/${quizSlug}/active`;
}

export function quizResultsUrl(categorySlug, quizSlug) {
    return `/quizzes/${categorySlug}/${quizSlug}/results`;
}

export function difficultyLabel(value) {
    if (!value) {
        return 'Beginner';
    }

    return value.charAt(0).toUpperCase() + value.slice(1);
}
