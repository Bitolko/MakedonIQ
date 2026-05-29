<script setup>
import { computed, watchEffect } from 'vue';
import Home from './Pages/Home.vue';
import About from './Pages/About.vue';
import Contact from './Pages/Contact.vue';
import Dashboard from './Pages/Dashboard.vue';
import Progress from './Pages/Progress.vue';
import Login from './Pages/Auth/Login.vue';
import Register from './Pages/Auth/Register.vue';
import QuizIndex from './Pages/Quizzes/Index.vue';
import QuizCategory from './Pages/Quizzes/Category.vue';
import QuizStart from './Pages/Quizzes/Start.vue';
import ActiveQuiz from './Pages/Quizzes/Active.vue';
import QuizResults from './Pages/Quizzes/Results.vue';
import AdminDashboard from './Pages/Admin/Dashboard.vue';
import AdminCategories from './Pages/Admin/Categories.vue';
import AdminQuizzes from './Pages/Admin/Quizzes.vue';
import AdminQuestions from './Pages/Admin/Questions.vue';

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
    Dashboard: { component: Dashboard, title: 'Dashboard | MakedonIQ' },
    Progress: { component: Progress, title: 'My Progress | MakedonIQ' },
    'Auth.Login': { component: Login, title: 'Login | MakedonIQ' },
    'Auth.Register': { component: Register, title: 'Register | MakedonIQ' },
    'Quizzes.Index': { component: QuizIndex, title: 'Quiz Categories | MakedonIQ' },
    'Quizzes.Category': { component: QuizCategory, title: 'History Quizzes | MakedonIQ' },
    'Quizzes.Start': { component: QuizStart, title: 'Start History Quiz | MakedonIQ' },
    'Quizzes.Active': { component: ActiveQuiz, title: 'Active Quiz | MakedonIQ' },
    'Quizzes.Results': { component: QuizResults, title: 'Quiz Results | MakedonIQ' },
    'Admin.Dashboard': { component: AdminDashboard, title: 'Admin Dashboard | MakedonIQ' },
    'Admin.Categories': { component: AdminCategories, title: 'Admin Categories | MakedonIQ' },
    'Admin.Quizzes': { component: AdminQuizzes, title: 'Admin Quizzes | MakedonIQ' },
    'Admin.Questions': { component: AdminQuestions, title: 'Admin Questions | MakedonIQ' },
};

const page = computed(() => pages[props.initialPage] || pages.Home);

watchEffect(() => {
    document.title = page.value.title;
});
</script>

<template>
    <component :is="page.component" />
</template>
