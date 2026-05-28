export const categories = [
    {
        slug: 'language',
        title: 'Macedonian Language',
        description: 'Essential phrases, everyday vocabulary, and simple grammar for family conversations.',
        level: 'Beginner',
        quizzes: 12,
        icon: 'Aa',
        href: '/quizzes',
        tone: 'red',
    },
    {
        slug: 'alphabet',
        title: 'Macedonian Alphabet',
        description: 'Read and recognise the Cyrillic letters with playful bilingual prompts.',
        level: 'Fundamental',
        quizzes: 8,
        icon: 'АБ',
        href: '/quizzes',
        tone: 'gold',
    },
    {
        slug: 'history',
        title: 'History of Macedonia',
        description: 'Explore ancient kingdoms, cultural milestones, and modern Macedonian stories.',
        level: 'Intermediate',
        quizzes: 10,
        icon: '🏛',
        href: '/quizzes/history',
        tone: 'navy',
    },
    {
        slug: 'geography',
        title: 'Geography',
        description: 'Learn about mountains, lakes, cities, regions, and landmarks across Macedonia.',
        level: 'Beginner',
        quizzes: 7,
        icon: '↗',
        href: '/quizzes',
        tone: 'red',
    },
    {
        slug: 'culture',
        title: 'Culture and Traditions',
        description: 'Celebrate holidays, family customs, folklore, dance, and community traditions.',
        level: 'All levels',
        quizzes: 9,
        icon: '✦',
        href: '/quizzes',
        tone: 'gold',
    },
    {
        slug: 'food-music',
        title: 'Food and Music',
        description: 'Recognise traditional dishes, instruments, songs, and festival favourites.',
        level: 'Beginner',
        quizzes: 6,
        icon: '♪',
        href: '/quizzes',
        tone: 'navy',
    },
];

export const historyQuizzes = [
    {
        title: 'Ancient Macedonia Basics',
        description: 'Start with kings, cities, symbols, and the ancient world around Macedonia.',
        difficulty: 'Beginner',
        questions: 10,
        time: '8 min',
        progress: 80,
        status: 'Continue',
        href: '/quizzes/history/start',
    },
    {
        title: 'Macedonian Historical Figures',
        description: 'Meet the rulers, educators, artists, and leaders who shaped Macedonian identity.',
        difficulty: 'Intermediate',
        questions: 14,
        time: '12 min',
        progress: 35,
        status: 'Continue',
        href: '/quizzes/history/start',
    },
    {
        title: 'Important Events',
        description: 'Test your knowledge of turning points from ancient periods to the modern era.',
        difficulty: 'Intermediate',
        questions: 12,
        time: '10 min',
        progress: 0,
        status: 'Start',
        href: '/quizzes/history/start',
    },
    {
        title: 'Cultural Heritage',
        description: 'Connect history with language, music, stories, crafts, and community memory.',
        difficulty: 'Beginner',
        questions: 9,
        time: '7 min',
        progress: 100,
        status: 'Completed',
        href: '/quizzes/history/results',
    },
    {
        title: 'Modern Macedonia',
        description: 'A friendly overview of geography, statehood, diaspora, and contemporary culture.',
        difficulty: 'Advanced',
        questions: 15,
        time: '14 min',
        progress: 0,
        status: 'Start',
        href: '/quizzes/history/start',
    },
];

export const dashboardStats = [
    { label: 'Total points', value: '1,250', detail: '+180 this week', icon: '★', tone: 'gold' },
    { label: 'Completed quizzes', value: '12', detail: '4 categories touched', icon: '✓', tone: 'red' },
    { label: 'Current streak', value: '5 days', detail: 'Keep it rolling', icon: '↟', tone: 'navy' },
];

export const progressCategories = [
    { title: 'Language', progress: 76, completed: 9, total: 12, icon: 'Aa' },
    { title: 'Alphabet', progress: 58, completed: 5, total: 8, icon: 'АБ' },
    { title: 'History', progress: 64, completed: 6, total: 10, icon: '🏛' },
    { title: 'Geography', progress: 42, completed: 3, total: 7, icon: '↗' },
    { title: 'Culture', progress: 71, completed: 6, total: 9, icon: '✦' },
    { title: 'Food and Music', progress: 35, completed: 2, total: 6, icon: '♪' },
];

export const recentResults = [
    { quiz: 'Ancient Macedonia Basics', score: '8/10', points: 120, date: 'Today' },
    { quiz: 'Alphabet Warm-up', score: '7/8', points: 90, date: 'Yesterday' },
    { quiz: 'Macedonian Foods', score: '6/10', points: 75, date: 'Monday' },
];

export const achievements = [
    { title: 'First Quiz Finished', description: 'Completed your first MakedonIQ quiz.', icon: '✓' },
    { title: 'Alphabet Explorer', description: 'Practised 20 Cyrillic letter prompts.', icon: 'А' },
    { title: 'History Starter', description: 'Scored over 80% in a history quiz.', icon: '★' },
];

export const adminQuizzes = [
    { title: 'Ancient Macedonia Basics', category: 'History', difficulty: 'Beginner', questions: 10, status: 'Published' },
    { title: 'Daily Macedonian Phrases', category: 'Language', difficulty: 'Beginner', questions: 12, status: 'Published' },
    { title: 'Cyrillic Letter Match', category: 'Alphabet', difficulty: 'Fundamental', questions: 18, status: 'Draft' },
    { title: 'Macedonian Lakes and Cities', category: 'Geography', difficulty: 'Intermediate', questions: 11, status: 'Published' },
    { title: 'Traditional Songs', category: 'Food and Music', difficulty: 'Beginner', questions: 8, status: 'Draft' },
];

export const adminActivity = [
    'New learner registered from Melbourne',
    'Ancient Macedonia Basics reached 120 attempts',
    'Alphabet Warm-up was edited by Admin',
    'Three learners earned the History Starter badge',
];
