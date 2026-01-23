<template>

    <div class="select-none min-h-screen grid grid-cols-1 lg:grid-cols-2 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0 bg-slate-100">

        <div class="flex items-center justify-end p-6">

            <div class="w-full max-w-md">

                <Logo height="h-10" class="mx-auto mb-4"></Logo>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">

                    <h1 class="text-2xl font-semibold leading-none tracking-tight mb-4">Sign in</h1>

                    <h1 class="text-sm text-gray-500 mb-4">Enter your credentials to access the dashboard</h1>

                    <form @submit.prevent="submit" class="space-y-4 mb-4">

                        <Input
                            type="email"
                            placeholder="Email"
                            v-model="form.email"
                            :errorText="formState.getFormError('email')" />

                        <Input
                            type="password"
                            placeholder="Password"
                            v-model="form.password"
                            :errorText="formState.getFormError('password')" />

                        <div v-if="formState.generalErrorText" class="text-red-500 text-sm mb-3">
                            {{ formState.generalErrorText }}
                        </div>

                        <Button
                            type="gradient"
                            :action="submit"
                            buttonClass="w-full shadow-lg rounded-full">
                            <span class="ml-1">{{ loading ? 'Signing in...' : 'Continue' }}</span>
                        </Button>

                        <div class="text-sm text-center mb-4">
                            <router-link :to="{ name: 'login' }" class="text-blue-600 hover:underline">Create account</router-link>
                            <span class="text-gray-300 mx-4">|</span>
                            <router-link :to="{ name: 'login' }" class="text-blue-600 hover:underline">Forgot password?</router-link>
                        </div>

                    </form>

                    <SocialLinks></SocialLinks>

                </div>

                <p class="text-sm text-center text-gray-500">© {{ currentYear }} {{ appName }}. All rights reserved.</p>

            </div>

        </div>

        <!-- Right: Full Height Image (hidden on small screens) -->
        <div class="hidden lg:block h-screen overflow-hidden">

            <img :src="'/images/agent.png'" class="w-full" alt="Lady phone with USSD menu" />

        </div>

    </div>

</template>

<script>

    import axios from 'axios';
    import Pill from '@Partials/Pill.vue';
    import Logo from '@Partials/Logo.vue';
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import SocialLinks from '@Pages/auth/_components/SocialLinks.vue';

    export default {
        name: 'Login',
        components: { Pill, Logo, Input, Button, SocialLinks },
        inject: ['authState', 'formState', 'notificationState'],
        data() {
            return {
                form: {
                    email: '',
                    password: ''
                },
                loading: false,
                currentYear: new Date().getFullYear(),
                appName: import.meta.env.VITE_APP_NAME
            };
        },
        methods: {
            isEmpty,
            async submit() {

                if (this.loading) return;

                this.formState.hideFormErrors();

                if (this.isEmpty(this.form.email)) {
                    this.formState.setFormError('email', 'Enter your email');
                } else if (this.isEmpty(this.form.password)) {
                    this.formState.setFormError('password', 'Enter your password');
                }

                if (this.formState.hasErrors) {
                    return;
                }

                this.loading = true;

                try {

                    const response = await axios.post('/api/auth/login', this.form);

                    const user = response.data.user;
                    const token = response.data.token;

                    await this.authState.setUser(user);
                    this.authState.setTokenOnRequest(token);
                    this.authState.setTokenOnLocalStorage(token);

                    const redirect = this.$route.query.redirect;

                    if (redirect) {
                        this.$router.push(redirect);
                    } else {
                        this.$router.push({ name: 'show-apps' });
                    }

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while signing in';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to sign in:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    };
</script>
