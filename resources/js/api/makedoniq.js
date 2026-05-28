const legacyCategoryAliases = {
    history: 'history-of-macedonia',
};

const legacyQuizAliases = {
    history: 'macedonia-history-basics',
};

export class ApiError extends Error {
    constructor(message, status, payload = null) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.payload = payload;
    }
}

function csrfToken() {
    return window.MakedonIQ?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
}

export function currentUser() {
    return window.MakedonIQ?.auth?.user || null;
}

export async function fetchJson(url, options = {}) {
    const headers = {
        Accept: 'application/json',
        ...(options.headers || {}),
    };

    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers,
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok) {
        throw new ApiError(payload?.message || `Request failed with status ${response.status}`, response.status, payload);
    }

    return payload;
}

export async function postJson(url, body = {}) {
    return fetchJson(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });
}

export async function submitQuizAttempt(quizSlug, answers) {
    return postJson(`/api/quizzes/${quizSlug}/attempts`, { answers });
}

export async function getQuizAttempt(attemptId) {
    return fetchJson(`/api/quiz-attempts/${attemptId}`);
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

export function currentAttemptId() {
    const parts = quizPathParts();

    return parts[3] === 'results' ? parts[4] || null : null;
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

export function quizResultsUrl(categorySlug, quizSlug, attemptId = null) {
    const base = `/quizzes/${categorySlug}/${quizSlug}/results`;

    return attemptId ? `${base}/${attemptId}` : base;
}

export function difficultyLabel(value) {
    if (!value) {
        return 'Beginner';
    }

    return value.charAt(0).toUpperCase() + value.slice(1);
}
