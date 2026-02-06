import './bootstrap';
import router from './router';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { useUiStore } from "@Stores/ui-store.js";
import { useAppStore } from "@Stores/app-store.js";
import { useAuthStore } from "@Stores/auth-store.js";
import { useFormStore } from "@Stores/form-store.js";
import { useVersionStore } from "@Stores/version-store.js";
import { useNotificationStore } from "@Stores/notification-store.js";

const app = createApp({});
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.mount('#app');

// Make Pinia States globally available
app.provide("uiState", useUiStore());
app.provide("appState", useAppStore());
app.provide("formState", useFormStore());
app.provide("authState", useAuthStore());
app.provide("versionState", useVersionStore());
app.provide("notificationState", useNotificationStore());
