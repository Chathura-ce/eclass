import './bootstrap';
import { createApp } from 'vue';
import router from './router'; // We will create this next
import App from './App.vue';   // We will create this next

createApp(App)
    .use(router)
    .mount('#app');