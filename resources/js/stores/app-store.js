import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useAppStore = defineStore('app', {
    state: () => ({
        app: null,
        appForm: null,
        silentUpdate: null,
        isUploading: false,
        isLoadingApp: false,
        isCreatingApp: false,
        isUpdatingApp: false,
        isDeletingApp: false,
        showActionButtons: false,
    }),
    actions: {
        reset() {
            this.app = null;
            this.appForm = null;
            this.isUploading = false;
            this.isLoadingApp = false;
            this.isCreatingApp = false;
            this.isUpdatingApp = false;
            this.isDeletingApp = false;
            this.showActionButtons = false;
            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.appForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.appForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.appForm);
        },
        setApp(app) {
            this.app = app;
        },
        setAppForm(app = null, saveState = true) {

            this.appForm = {
                name: app?.name ?? null,
                logo: app?.logo ? [app.logo] : []
            };

            if(app == null) {
                this.appForm.country = null;
                this.appForm.network = null;
            }

            if(saveState) {
                this.saveOriginalState('Original app');
            }

        },
    },
});
