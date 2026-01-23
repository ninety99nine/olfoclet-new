<template>

    <header class="sticky top-0 z-30 bg-gray-50 shadow-sm border-b border-gray-200 py-2.5 px-4">

        <div class="flex items-center justify-end gap-2">

            <Button
                :leftIcon="Sparkle"
                v-if="!showingMyApps"
                :action="() => navigateToShowApps()" type="primary" size="xs" buttonClass="rounded-full">
                <span class="ml-1">My Apps</span>
            </Button>

            <Dropdown
                position="left"
                dropdownClasses="w-72">

                <template #trigger="props">

                    <div
                        @click="props.toggleDropdown"
                        class="flex items-center justify-center space-x-2 pl-4 cursor-pointer text-sm bg-gray-50 rounded-full focus:ring-4 focus:ring-gray-250">
                        <span class="text-xs font-semibold text-gray-700">{{ authUser.first_name }}</span>
                        <div class="flex items-center justify-center w-8 h-8 px-2 border border-gray-300 text-gray-500 rounded-full p-2 hover:scale-110 transition-all duration-300">
                            <UserRound size="12"></UserRound>
                        </div>
                    </div>

                </template>

                <template #content="props">

                    <!-- Profile Menu -->
                    <div class="max-h-60 overflow-auto">

                        <div
                            v-if="authUser"
                            class="p-4 space-y-2 border-b border-gray-100 cursor-pointer hover:bg-gray-100 group"
                            @click="(event) => navigateToAccountSettings(() => props.toggleDropdown(event))">

                            <!-- Name -->
                            <p class="text-sm text-gray-900 font-medium truncate w-4/5">
                                {{ authUser.name }}
                            </p>

                            <!-- Email -->
                            <p v-if="authUser.email" class="text-xs text-gray-500 truncate w-4/5">
                                {{ authUser.email }}
                            </p>

                        </div>

                        <!-- Profile Menu Items -->
                        <template
                            :key="index"
                            v-for="(navMenu, index) in profileNavMenus">

                            <div @click="(event) => navMenu.name == 'Sign Out' ? logout() : navMenu.action(() => props.toggleDropdown(event))" class="cursor-pointer flex space-x-2 items-center py-2.5 px-4 text-gray-900 hover:bg-gray-100 group">

                                <Loader v-if="navMenu.name == 'Sign Out' && isLoggingOut"></Loader>

                                <span class="text-sm text-gray-500 group-hover:text-gray-900">
                                    {{ navMenu.name }}
                                </span>

                            </div>

                        </template>

                    </div>

                </template>

            </Dropdown>

        </div>

    </header>

</template>

<script>

import Button from '@Partials/Button.vue';
import Loader from '@Partials/Loader.vue';
import Dropdown from '@Partials/Dropdown.vue';
import { User, LogOut, Bell, Mail, UserRound, Sparkle } from 'lucide-vue-next';

export default {
    inject: ['authState', 'notificationState'],
    components: { User, LogOut, Bell, Mail, UserRound, Button, Loader, Dropdown },
    data() {
        return {
            Sparkle,
            isLoggingOut: false,
        };
    },
    computed: {
        authUser() {
            return this.authState.user;
        },
        isLoadingAuthUser() {
            return this.authState.isLoadingUser;
        },
        showingMyApps() {
            return this.$route.name == 'show-apps';
        },
        profileNavMenus() {

            let menus =  [];

            if(!this.showingMyApps) {

                menus.push({
                    name: 'My Apps',
                    action: this.navigateToShowApps
                });

            }

            menus.push({
                name: 'Sign Out',
                routeName: null
            });

            return menus;

        },
    },
    methods: {
        navigateToShowApps(toggleDropdown = null) {
            this.$router.push({
                name: 'show-apps'
            });
            if(toggleDropdown) toggleDropdown();
        },
        async logout() {
            try {

                this.isLoggingOut = true;
                await axios.post('/api/auth/logout');

                this.authState.unsetUser();
                this.authState.unsetToken();
                this.$router.replace({ name: 'login' });

            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Something went wrong while signing out';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to sign out:', error);
            } finally {
                this.isLoggingOut = false;
            }
        },
    }
};
</script>
