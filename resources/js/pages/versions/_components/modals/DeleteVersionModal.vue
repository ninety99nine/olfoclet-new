<template>
    <Modal
        size="sm"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false">
        <template #content>
            <div>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Delete Version
                </p>
                <div class="py-2">
                    <p class="text-slate-700 leading-relaxed">
                        Are you sure you want to permanently delete
                        <strong class="text-slate-900">version {{ version?.number }}</strong>?
                    </p>
                    <p class="mt-3 text-rose-600 font-medium">
                        This action cannot be undone.
                    </p>
                    <p class="mt-2 text-slate-500 text-sm">
                        Any deployments using this version will stop working.
                    </p>
                </div>
                <div class="mt-8 flex justify-end space-x-3">
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        :action="() => versionState.deleteVersionModal?.hideModal()"
                        buttonClass="rounded-lg">
                        Cancel
                    </Button>
                    <Button
                        size="md"
                        type="danger"
                        mode="solid"
                        :loading="versionState.isDeletingVersion"
                        :action="() => versionState.deleteVersion()"
                        buttonClass="rounded-lg">
                        Delete Version
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '@Partials/Modal.vue';
import Button from '@Partials/Button.vue';

export default {
    name: 'DeleteVersionModal',
    components: { Modal, Button },
    inject: ['versionState', 'formState', 'notificationState'],
    computed: {
        version() {
            return this.versionState.version;
        },
    },
    methods: {
        async deleteVersion() {
            try {

                if (this.versionState.isDeletingVersion) return;

                this.versionState.isDeletingVersion = true;

                await axios.delete(`/api/apps/${this.version.app_id}/versions/${this.version.id}`);

                this.notificationState.showSuccessNotification('Version deleted');

                this.versionState.deleteVersionModal?.hideModal();

                await this.$router.push({ name: 'show-app-versions', params: { app_id: this.version.app_id } });

            } catch (error) {
                const message = error?.response?.data?.message || error?.message || 'Failed to delete version';
                this.notificationState.showWarningNotification(message);
                this.formState.setServerFormErrors(error);
                console.error('Failed to delete version', error);
            } finally {
                this.versionState.isDeletingVersion = false;
            }
        },
    },
};
</script>
