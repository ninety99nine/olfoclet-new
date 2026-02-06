<template>
    <Modal
        size="lg"
        ref="modal"
        :bareMode="true"
        :showFooter="false"
        title="Code Editor"
        :scrollOnContent="false">
        <template #content>
            <div v-if="versionState.currentStep && versionState.currentFeature">
                <CodeEditor
                    class="mb-4"
                    :title="versionState.currentStep?.name"
                    :close="() => versionState.codeContentEditorModal?.hideModal()"
                    v-model="versionState.currentFeature.content"
                    :language="versionState.currentFeature.language"
                    @update:language="(lang) => { versionState.currentFeature.language = lang; }"
                />
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '@Partials/Modal.vue';
import CodeEditor from '@Pages/versions/_components/editors/CodeEditor.vue';

export default {
    name: 'CodeContentEditorModal',
    components: { Modal, CodeEditor },
    inject: ['versionState'],
};
</script>
