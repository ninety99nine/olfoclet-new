<template>

    <div
        :key="key"
        class="transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        <!-- Loader -->
        <div v-if="isLoadingApps" class="flex items-center justify-center h-[calc(100vh-53px)]">
            <img class="w-10 animate-spin hover:scale-120 transition-all duration-300" :src="`/logo/swirl.png`" :alt="`${appName} Logo`">
        </div>

        <!-- Apps cards -->
        <div v-else class="max-w-7xl mx-auto px-6 pt-10">

            <!-- Header -->
            <div
                v-if="hasApps"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-10">

                <div>
                    <h1 class="text-3xl font-bold text-slate-900">My Applications</h1>
                    <p class="mt-2 text-slate-600">Manage your USSD services across networks and countries</p>
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- App cards -->
                <div
                    :key="app.id"
                    v-for="app in apps"
                    @click="navigateToAppHome(app)"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-indigo-300 transition-all overflow-hidden flex flex-col cursor-pointer group h-full">

                        <div class="p-6 flex-1 flex flex-col gap-4">

                            <!-- Name + Status row -->
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <img v-if="app.logo?.path" :src="app.logo.path" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-sm" />
                                    <div>
                                        <h3 class="font-bold text-xl text-slate-900 line-clamp-2">{{ app.name }}</h3>
                                        <p class="text-sm text-slate-500 mt-0.5">{{ app.shortcode || '—' }}</p>
                                    </div>
                                </div>
                                <span :class="statusStyles[app.status || 'live']" class="px-3 py-1 text-xs font-medium rounded-full">
                                    {{ app.statusDisplay || app.status || 'Live' }}
                                </span>
                            </div>

                            <!-- Countries + Networks -->
                            <div class="flex flex-col gap-3 mt-1 text-sm">

                                <!-- Countries -->
                                <div class="flex items-center gap-2">

                                    <div class="text-xs text-slate-500 whitespace-nowrap">{{ displayCountries(app).total == 1 ? 'Country' : 'Countries' }}:</div>

                                    <div class="flex items-center gap-1">
                                        <div class="flex -space-x-2">
                                            <img
                                                v-for="c in displayCountries(app).main"
                                                :key="c.code"
                                                :src="`/svgs/country-flags/${c.toLowerCase()}.svg`"
                                                class="w-7 h-7 rounded-full border-2 border-white shadow-sm object-cover"
                                                :title="c.name"
                                            />
                                        </div>

                                        <span
                                            v-if="displayCountries(app).extra"
                                            class="ml-1 px-2 py-0.5 bg-slate-100 text-slate-700 text-xs rounded-full font-medium cursor-help"
                                            :title="displayCountries(app).main.length < getCountries(app).length ?
                                                    getCountries(app).slice(displayCountries(app).main.length).map(c => c.name).join(', ') : ''">
                                            +{{ displayCountries(app).extra }}
                                        </span>

                                        <span
                                            v-if="getCountries(app).length === 0"
                                            class="text-slate-400 italic text-xs">
                                            None
                                        </span>
                                    </div>

                                </div>

                                <!-- Networks -->
                                <div class="flex items-center gap-2">

                                    <div class="text-xs text-slate-500 whitespace-nowrap">{{ displayNetworks(app).total == 1 ? 'Network' : 'Networks' }}:</div>

                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span
                                            v-for="n in displayNetworks(app).main"
                                            :key="n"
                                            class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs rounded-full">
                                            {{ n }}
                                        </span>

                                        <span
                                            v-if="displayNetworks(app).extra"
                                            class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 text-xs rounded-full font-medium cursor-help"
                                            :title="displayNetworks(app).main.length < getNetworks(app).length ?
                                                    getNetworks(app).slice(displayNetworks(app).main.length).join(', ') : ''">
                                            +{{ displayNetworks(app).extra }}
                                        </span>

                                        <span
                                            v-if="getNetworks(app).length === 0"
                                            class="text-slate-400 italic text-xs">
                                            None
                                        </span>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Footer / CTA -->
                        <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/70 mt-auto">
                            <div class="flex items-center justify-between text-xs">
                                <div class="text-slate-500">
                                    Last modified: {{ formattedRelativeDate(app.updated_at) }}
                                    <span v-if="!app.updated_at" class="text-slate-400 text-xs">Never {{app.updated_at}}</span>
                                </div>
                                <div class="text-indigo-600 group-hover:text-indigo-700 font-medium flex items-center gap-1.5 transition-colors">
                                    Open
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                </div>

                <!-- + New App Card -->
                <div
                    @click="navigateToCreateApp"
                    :class="[
                        { 'h-80 col-start-2' : !hasApps },
                        'group border-2 border-dashed border-slate-300 rounded-2xl flex flex-col items-center justify-center gap-5 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 hover:shadow-md transition-all duration-300'
                    ]">

                    <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>

                    <p class="text-xl font-semibold text-slate-700 group-hover:text-indigo-700 transition-colors">
                        New App
                    </p>

                    <p class="text-sm text-slate-500 text-center px-8">
                        Start building your next USSD service
                    </p>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import { formattedRelativeDate } from '@Utils/dateUtils.js';

    export default {
        inject: ['formState', 'notificationState'],
        data() {
            return {
                apps: [],
                pagination: null,
                isLoadingApps: false,
                appName: import.meta.env.VITE_APP_NAME,
            }
        },
        computed: {
            key() {
                return this.isLoadingApps;
            },
            hasApps() {
                return this.apps.length > 0;
            },
            statusStyles() {
                return {
                    live: 'bg-emerald-100 text-emerald-800',
                    building: 'bg-amber-100 text-amber-800',
                    testing: 'bg-blue-100 text-blue-800',
                    draft: 'bg-slate-100 text-slate-700'
                }
            },
            displayCountries() {
                return (app) => {
                    const countries = this.getCountries(app);
                    if (countries.length === 0) return { main: [], extra: 0 };

                    const showCount = 5;
                    return {
                        total: countries.length,
                        main: countries.slice(0, showCount),
                        extra: countries.length > showCount ? countries.length - showCount : 0
                    };
                };
            },
            displayNetworks() {
                return (app) => {
                    const nets = this.getNetworks(app);
                    if (nets.length === 0) return { main: [], extra: 0 };

                    const showCount = 3;
                    return {
                        total: nets.length,
                        main: nets.slice(0, showCount),
                        extra: nets.length > showCount ? nets.length - showCount : 0
                    };
                };
            }
        },
        methods: {
            formattedRelativeDate,
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
            getNetworks(app) {
                return [...new Set(app.deployments.map(item => item.network))];
            },
            getCountries(app) {
                return [...new Set(app.deployments.map(item => item.country))];
            },
            async showApps() {
                try {

                    this.isLoadingApps = true;

                    let config = {
                        params: {
                            _relationships: ['logo', 'deployments'].join(',')
                        }
                    };

                    const response = await axios.get('/api/apps', config);

                    const pagination = response.data;
                    this.apps = pagination.data;

                    if(this.apps.length == 0) {
                        await this.navigateToCreateApp();
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
        }
    };

</script>
