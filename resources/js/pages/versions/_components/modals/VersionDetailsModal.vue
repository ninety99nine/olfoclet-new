<template>
    <Modal
        size="sm"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false">
        <template #content>
            <div>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-6">
                    Edit Version Details
                </p>
                <div class="space-y-6">
                    <Input
                        label="Version Number"
                        v-model="versionState.versionForm.number"
                        placeholder="e.g. v1.0, beta-2"
                        :errorText="formState.getFormError('number')"
                        required
                    />
                    <Input
                        label="Description (optional)"
                        type="textarea"
                        rows="3"
                        v-model="versionState.versionForm.description"
                        placeholder="Short description of what changed in this version"
                    />
                </div>
                <div class="mt-8 flex justify-end space-x-3">
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        :action="() => versionState.versionDetailsModal?.hideModal()"
                        buttonClass="rounded-lg">
                        Cancel
                    </Button>
                    <Button
                        size="md"
                        type="primary"
                        mode="solid"
                        :loading="versionState.isUpdatingVersion"
                        :action="updateVersionDetails"
                        buttonClass="rounded-lg">
                        Save Changes
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '@Partials/Modal.vue';
import Input from '@Partials/Input.vue';
import Button from '@Partials/Button.vue';

export default {
    name: 'VersionDetailsModal',
    components: { Modal, Button, Input },
    inject: ['versionState', 'formState'],
    computed: {
        version() {
            return this.versionState.version;
        },
        versionForm() {
            return this.versionState.versionForm;
        },
    },
    methods: {
        async updateVersionDetails() {

            if (this.versionState.isUpdatingVersion) return;

            try {

                this.formState.hideFormErrors();

                const number = (this.versionForm?.number ?? '').trim();

                if (!number) {
                    this.formState.setFormError('number', 'Version number is required');
                    return;
                }

                this.isUpdatingVersion = true;

                const data = {
                    number,
                    description: (this.versionForm?.description ?? '').trim() || null
                };

                const response = await axios.put(`/api/apps/${this.version.app_id}/versions/${this.version.id}`, data);
                const version = response.data.version;

                this.versionState.setVersion(version);
                this.versionState.setVersionForm(version);

                this.notificationState.showSuccessNotification('Version details updated!');

                this.versionState.versionDetailsModal?.hideModal();

            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Failed to update version';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to update version', error);
            } finally {
                this.versionState.isUpdatingVersion = false;
            }
        }
    }
};
</script>
