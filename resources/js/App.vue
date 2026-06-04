<script setup>
import { computed, watchEffect } from 'vue';
import Home from './Pages/Home.vue';
import About from './Pages/About.vue';
import Contact from './Pages/Contact.vue';
import Dashboard from './Pages/Dashboard.vue';
import Progress from './Pages/Progress.vue';
import Profile from './Pages/Profile.vue';
import Login from './Pages/Auth/Login.vue';
import Register from './Pages/Auth/Register.vue';
import LearnIndex from './Pages/Learn/Index.vue';
import LearnCategory from './Pages/Learn/Category.vue';
import LearnShow from './Pages/Learn/Show.vue';
import MapChallenge from './Pages/MapChallenge.vue';
import QuizIndex from './Pages/Quizzes/Index.vue';
import QuizCategory from './Pages/Quizzes/Category.vue';
import QuizStart from './Pages/Quizzes/Start.vue';
import ActiveQuiz from './Pages/Quizzes/Active.vue';
import QuizResults from './Pages/Quizzes/Results.vue';
import AdminDashboard from './Pages/Admin/Dashboard.vue';
import AdminCategories from './Pages/Admin/Categories.vue';
import AdminLessons from './Pages/Admin/Lessons.vue';
import AdminQuizzes from './Pages/Admin/Quizzes.vue';
import AdminQuestions from './Pages/Admin/Questions.vue';
import AdminAttempts from './Pages/Admin/Attempts.vue';
import AdminAttemptResult from './Pages/Admin/AttemptResult.vue';

const props = defineProps({
    initialPage: {
        type: String,
        required: true,
    },
});

const pages = {
    Home: { component: Home, title: 'MakedonIQ | Learn Macedonian through fun quizzes' },
    About: { component: About, title: 'About | MakedonIQ' },
    Contact: { component: Contact, title: 'Contact | MakedonIQ' },
    'Learn.Index': { component: LearnIndex, title: 'Learn | MakedonIQ' },
    'Learn.Category': { component: LearnCategory, title: 'Learn Category | MakedonIQ' },
    'Learn.Show': { component: LearnShow, title: 'Lesson | MakedonIQ' },
    MapChallenge: { component: MapChallenge, title: 'Macedonia Map Challenge | MakedonIQ' },
    Dashboard: { component: Dashboard, title: 'Dashboard | MakedonIQ' },
    Progress: { component: Progress, title: 'My Progress | MakedonIQ' },
    Profile: { component: Profile, title: 'Profile | MakedonIQ' },
    'Auth.Login': { component: Login, title: 'Login | MakedonIQ' },
    'Auth.Register': { component: Register, title: 'Register | MakedonIQ' },
    'Quizzes.Index': { component: QuizIndex, title: 'Quiz Categories | MakedonIQ' },
    'Quizzes.Category': { component: QuizCategory, title: 'History Quizzes | MakedonIQ' },
    'Quizzes.Start': { component: QuizStart, title: 'Start History Quiz | MakedonIQ' },
    'Quizzes.Active': { component: ActiveQuiz, title: 'Active Quiz | MakedonIQ' },
    'Quizzes.Results': { component: QuizResults, title: 'Quiz Results | MakedonIQ' },
    'Admin.Dashboard': { component: AdminDashboard, title: 'Admin Dashboard | MakedonIQ' },
    'Admin.Categories': { component: AdminCategories, title: 'Admin Categories | MakedonIQ' },
    'Admin.Lessons': { component: AdminLessons, title: 'Admin Lessons | MakedonIQ' },
    'Admin.Quizzes': { component: AdminQuizzes, title: 'Admin Quizzes | MakedonIQ' },
    'Admin.Questions': { component: AdminQuestions, title: 'Admin Questions | MakedonIQ' },
    'Admin.Attempts': { component: AdminAttempts, title: 'Admin Attempts | MakedonIQ' },
    'Admin.AttemptResult': { component: AdminAttemptResult, title: 'Admin Attempt Result | MakedonIQ' },
};

const page = computed(() => pages[props.initialPage] || pages.Home);

watchEffect(() => {
    document.title = page.value.title;
});
</script>

<template>
    <component :is="page.component" />
</template>
