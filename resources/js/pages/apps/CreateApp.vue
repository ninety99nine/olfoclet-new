<template>

    <div class="flex flex-col items-center justify-center pb-40 bg-slate-100 min-h-screen">

        <div class="w-full max-w-lg">

            <div class="bg-white p-8 rounded-2xl shadow-lg">

                <h1 class="text-lg font-semibold mb-4">New App</h1>

                <div class="space-y-4 mb-8">

                    <Input
                        type="text"
                        label="Name"
                        v-model="appForm.name"
                        placeholder="ABC Banking"
                        description="This can be your service name"
                        :errorText="formState.getFormError('name')" />

                    <div class="grid grid-cols-2 gap-4">

                        <SelectCountry
                            required
                            label="Country"
                            v-model="appForm.country"
                            :errorText="formState.getFormError('country')">
                        </SelectCountry>

                        <Select
                            required
                            :options="networks"
                            label="Mobile Network"
                            v-model="appForm.network"
                            :errorText="formState.getFormError('network')">
                        </Select>

                    </div>

                    <Input
                        type="file"
                        label="Logo"
                        :maxFiles="1"
                        v-model="appForm.logo"
                        :imagePreviewGridCols="1"
                        secondaryLabel="(optional)"
                        :errorText="formState.getFormError('logo')">
                    </Input>

                </div>

                <Button
                    size="lg"
                    type="gradient"
                    :action="createApp"
                    loaderType="primary"
                    :loading="isCreatingApp"
                    :disabled="isCreatingApp"
                    buttonClass="w-full shadow-lg rounded-full">
                    <span>Get Started</span>
                </Button>

            </div>

        </div>

    </div>

  </template>

<script>

    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import SelectCountry from '@Partials/SelectCountry.vue';
    import { isEmpty, isNotEmpty } from '@Utils/stringUtils';

    export default {
        inject: ['appState', 'formState', 'notificationState'],
        components: {
            Input, Select, Button, SelectCountry
        },
        computed: {
            appForm() {
                return this.appState.appForm;
            },
            isCreatingApp() {
                return this.appState.isCreatingApp;
            },
            networks() {
                let options = [
                    'Orange', 'Mascom', 'BTC'
                ];

                options = options.map(option => {
                    return {
                        label: option,
                        value: option
                    }
                });

                return options;
            }
        },
        methods: {
            isEmpty,
            isNotEmpty,
            async navigateToAppBuild(app) {
                await this.$router.push({
                    name: 'show-app-build',
                    params: {
                        app_id: app.id
                    }
                });
            },
            async createApp() {

                try {

                    if(this.appState.isCreatingApp) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.appForm.name)) {
                        this.formState.setFormError('name', 'Enter app name');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.appState.isCreatingApp = true;

                    let formData = new FormData();
                    formData.append('return', 1);
                    formData.append('name', this.appForm.name);
                    formData.append('country', this.appForm.country);
                    formData.append('network', this.appForm.network);

                    if (this.appForm.logo.length) {
                        formData.append('logo', this.appForm.logo[0].file_ref);
                    }

                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    };

                    const response = await axios.post('/api/apps', formData, config);

                    const app = response.data.app;
                    this.navigateToAppBuild(app);
                    this.notificationState.showSuccessNotification('App created!');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating app';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create app:', error);
                } finally {
                    this.appState.isCreatingApp = false;
                }
            }
        },
        created() {
            this.appState.setApp(null);
            this.appState.setAppForm(null);
            this.appForm.network = this.networks[0]['value'];
        }
    };

</script>
