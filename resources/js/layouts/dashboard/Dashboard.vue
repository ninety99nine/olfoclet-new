<template>

    <div
        :key="key"
        class="transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        <div v-if="isLoadingApp || isLoadingApps" class="flex items-center justify-center h-screen">
            <img class="w-10 animate-spin hover:scale-120 transition-all duration-300" :src="`/logo/swirl.png`" :alt="`${appName} Logo`">
        </div>

        <template v-else>
            <Sidebar v-if="appId"></Sidebar>
            <div :class="{ 'w-full lg:ps-60' : appId }">
                <Header></Header>
                <router-view></router-view>
            </div>

        <Notifications></Notifications>
        </template>

    </div>

</template>

<script>

    import Logo from '@Partials/Logo.vue';
    import Header from '@Layouts/dashboard/components/Header.vue';
    import Sidebar from '@Layouts/dashboard/components/Sidebar.vue';
    import Notifications from '@Layouts/dashboard/components/Notifications.vue';

    export default {
        inject: ['formState', 'appState', 'notificationState'],
        components: {
            Logo, Header, Sidebar, Notifications
        },
        data() {
            return {
                apps: [],
                selectedAppId: null,
                isLoadingApps: false,
                appName: import.meta.env.VITE_APP_NAME,
            }
        },
        watch: {
            appId(newValue) {
                if(newValue) {
                    this.selectedAppId = newValue;
                    this.showApps();
                    this.showApp();
                }
            }
        },
        computed: {
            app() {
                return this.appState.app;
            },
            isLoadingApp() {
                return this.appState.isLoadingApp;
            },
            appId() {
                return this.$route.params.app_id;
            },
            key() {
                return `${this.isLoadingApp}-${this.isLoadingApps}`;
            },
        },
        methods: {
            async navigateToCreateApp() {
                await this.$router.push({
                    name: 'create-app'
                });
            },
            async navigateToAppHome(app) {
                await this.$router.push({
                    name: 'show-app-home',
                    params: {
                        app_id: app.id
                    }
                });
            },
            async showApp(silentUpdate = false) {
                try {

                    if(!this.selectedAppId) return;
                    if(!silentUpdate) this.appState.isLoadingApp = true;

                    let config = {
                        params: {
                            _relationships: ['logo'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/apps/${this.selectedAppId}`, config);
                    this.appState.setApp(response.data);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching app';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch app:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({ name: 'show-apps' });
                    }

                } finally {
                    if(!silentUpdate) this.appState.isLoadingApp = false;
                }
            },
            async showApps() {
                try {

                    this.isLoadingApps = true;
                    const response = await axios.get('/api/apps');

                    const pagination = response.data;
                    this.apps = pagination.data;

                    if(this.apps.length > 0 && this.selectedAppId == null) {
                        const app = this.apps[0];
                        this.selectedAppId = app.id;
                        //this.navigateToAppHome(app);
                    }else if(this.apps.length == 0) {
                        //await this.navigateToCreateApp();
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching apps';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch apps:', error);
                } finally {
                    this.isLoadingApps = false;
                }
            }
        },
        created() {
            this.showApps();
            if(this.appId) {
                this.selectedAppId = this.appId;
                this.showApp();
            }
            this.appState.silentUpdate = () => this.showApp(true);
        }
    };

</script>
