<template>

    <div class="w-60 h-full fixed z-40 bg-indigo-50 border-e border-gray-200">

        <div class="relative flex flex-col h-full max-h-full">

            <div class="flex items-center space-x-2 pt-4 px-6 pb-8">
                <img v-if="app?.logo" :src="app.logo.path" class="max-w-8 max-h-8 rounded-full" />

                <h1 class="font-semibold text-xl truncate">
                    {{ app ? app.name : '' }}
                </h1>
            </div>

            <nav class="h-full overflow-y-auto px-4 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300">

                <ul class="space-y-1">

                    <li
                        :class="{ 'border-b border-gray-200 pb-4 mb-4' : menu.gap}"
                        v-for="(menu, index) in menus" :key="index">

                        <div>

                            <button
                                v-if="menu.subMenus"
                                @click="toggleMenu(index)"
                                :class="[
                                    'w-full flex items-center justify-between gap-x-2.5 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden cursor-pointer',
                                    (isActive(menu) || isSubMenuActive(menu))
                                        ? (menu.subMenus && expandedMenus[index] ? 'hover:bg-indigo-50 hover:shadow-sm hover:text-indigo-500 border border-transparent hover:border-indigo-300 transition-all duration-300' : 'bg-linear-to-r from-indigo-600 to-purple-600 shadow-sm text-white')
                                        : 'hover:bg-indigo-50 hover:shadow-sm hover:text-indigo-500 border border-transparent hover:border-indigo-300 transition-all duration-300'
                                    ]">

                                <div class="flex items-center gap-x-2.5">

                                    <component v-if="menu.icon" :is="menu.icon" :size="16" :class="{ 'text-red-600' : (menu.name == 'Virtual Agents' && !isActive(menu) && !isSubMenuActive(menu)) }" />

                                    <span :class="{ 'bg-linear-to-r from-red-600 to-indigo-600 bg-clip-text text-transparent' : menu.name == 'Virtual Agents' && !isActive(menu) && !isSubMenuActive(menu) }">
                                        {{ menu.name }}
                                    </span>

                                </div>

                                <component :is="expandedMenus[index] ? ChevronUp : ChevronDown" :size="16" />

                            </button>

                            <component
                                v-else
                                @click="closeAllMultiMenus"
                                :is="app ? 'router-link' : 'span'"
                                :to="app ? { name: menu.route, params: { app_id: app.id } } : null"
                                :class="[
                                    { 'opacity-20' : !app },
                                    'flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden',
                                    isActive(menu)
                                        ? 'bg-linear-to-r from-indigo-600 to-purple-600 shadow-sm text-white'
                                        : 'hover:bg-indigo-50 hover:shadow-sm hover:text-indigo-500 border border-transparent hover:border-indigo-300 transition-all duration-300'
                                ]">

                                <component v-if="menu.icon" :is="menu.icon" :size="16" :class="{ 'text-red-600' : (menu.name == 'Virtual Agents' && !isActive(menu)) }" />

                                <span :class="{ 'bg-linear-to-r from-red-600 to-indigo-600 bg-clip-text text-transparent' : menu.name == 'Virtual Agents' && !isActive(menu) }">
                                    {{ menu.name }}
                                </span>

                            </component>

                            <vue-slide-up-down :active="menu.subMenus && expandedMenus[index]">

                                <ul
                                    class="pl-6 space-y-1 mt-1"
                                    v-if="menu.subMenus && expandedMenus[index]">

                                    <li v-for="(subMenu, subIndex) in menu.subMenus" :key="subIndex">

                                        <component
                                            :is="app ? 'router-link' : 'span'"
                                            :to="app ? { name: subMenu.route, params: { app_id: app.id } } : null"
                                            :class="[
                                                { 'opacity-20' : !app },
                                                'flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden',
                                                isActive(subMenu)
                                                ? 'bg-linear-to-r from-indigo-600 to-purple-600 shadow-sm text-white'
                                                : 'hover:bg-indigo-50 hover:shadow-sm hover:text-indigo-500 border border-transparent hover:border-indigo-300 transition-all duration-300'
                                            ]">

                                            <component v-if="subMenu.icon" :is="subMenu.icon" :size="16" />
                                            <span>{{ subMenu.name }}</span>

                                        </component>

                                    </li>

                                </ul>

                            </vue-slide-up-down>

                        </div>

                    </li>

                </ul>

            </nav>

            <div class="p-8">
                <Logo height="h-6"></Logo>
            </div>

        </div>

    </div>

</template>

<script>
import Logo from '@Partials/Logo.vue';
import VueSlideUpDown from 'vue-slide-up-down';
import { BadgeAlert, Code, Users, Rocket, Logs, Database, BookOpen, HouseIcon, Smartphone, ChartArea, ChartCandlestick, ChevronUp, ChevronDown } from 'lucide-vue-next';

export default {
    inject: ['appState'],
    components: { Logo, VueSlideUpDown },
    data() {
        return {
            ChevronUp,
            ChevronDown,
            expandedMenus: {}, // Track expanded state for each menu
            menus: [
                { name: 'Home', route: 'show-app-home', icon: HouseIcon },
                { name: 'Build', route: 'show-app-build', icon: Code },
                { name: 'Review', route: 'show-app-review', icon: Smartphone },
                { name: 'Deploy', route: 'show-app-deployments', icon: Rocket },
                { name: 'Issues', route: 'show-app-issues', icon: BadgeAlert },
                { name: 'Resources', route: 'show-app-resources', icon: BookOpen, gap: true },
                { name: 'Sessions', route: 'show-app-sessions', relatedRoutes: ['show-app-session'], icon: Logs },
                { name: 'Accounts', route: 'show-app-accounts', relatedRoutes: ['show-app-account'], icon: Users },
                { name: 'Analytics', route: 'show-app-analytics', icon: ChartArea },
                { name: 'Business KPI', route: 'show-app-business-kpis', icon: ChartCandlestick },
                { name: 'Databases', route: 'show-app-databases', icon: Database },
            ],
        };
    },
    computed: {
        app() {
            return this.appState.app;
        },
        isSubMenuActive() {
            return (menu) => {
                if (!menu.subMenus) return false;
                return menu.subMenus.some(subMenu => this.isActive(subMenu));
            };
        }
    },
    methods: {
        isActive(menu) {
            // Check if the current route matches the menu's primary route
            if (this.$route.name === menu.route) {
                return true;
            }
            // Check if the current route is in the menu's relatedRoutes
            if (menu.relatedRoutes && menu.relatedRoutes.includes(this.$route.name)) {
                return true;
            }
            return false;
        },
        toggleMenu(index) {
            // Toggle the expanded state for the menu at the given index
            this.expandedMenus = {
                ...this.expandedMenus,
                [index]: !this.expandedMenus[index]
            };
            // If expanding and no submenu is active, navigate to the first submenu
            const menu = this.menus[index];
            if (this.expandedMenus[index] && !this.isSubMenuActive(menu) && menu.subMenus && menu.subMenus.length > 0) {
                this.$router.push({ name: menu.subMenus[0].route });
            }
        },
        closeAllMultiMenus() {
            // Reset expanded state for all menus with subMenus
            const newExpandedMenus = {};
            this.menus.forEach((_, index) => {
                newExpandedMenus[index] = false;
            });
            this.expandedMenus = newExpandedMenus;
        },
    },
    created() {
        // Initialize expanded state for each menu and expand if active
        this.menus.forEach((menu, index) => {
            // Check if the menu or any of its submenus are active
            const isMenuActive = this.isActive(menu) || (menu.subMenus && this.isSubMenuActive(menu));
            this.expandedMenus[index] = isMenuActive;
        });
    },
};
</script>
