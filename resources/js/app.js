import '../css/app.css';
import { createApp } from 'vue';
import App from './App.vue';

const mount = document.getElementById('app');

if (mount) {
    createApp(App, {
        initialPage: mount.dataset.page || 'Home',
    }).mount(mount);
}
//
