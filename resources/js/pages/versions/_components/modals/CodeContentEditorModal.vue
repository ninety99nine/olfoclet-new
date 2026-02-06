<template>
    <Modal
        ref="modal"
        :bareMode="true"
        :showFooter="false"
        title="Code Editor"
        :onHide="handleReset"
        :scrollOnContent="false"
        :size="isFullscreen ? 'fullscreen' : 'lg'"
        :backdropClass="{ 'opacity-0 pointer-events-none' : isFullscreen }">
        <template #content>
            <div
                :class="[{ 'pt-10 px-20': isFullscreen }, 'select-none']"
                v-if="versionState.currentStep && versionState.currentFeature"
                class="flex flex-col h-full">
                <CodeEditorWrapper
                    class="flex-1"
                    v-model="versionState.currentFeature.content"
                    v-model:isFullscreen="isFullscreen"
                    :language="versionState.currentFeature.language"
                    type="content_feature"
                    @exit="$refs.modal.hideModal()"
                    @update:language="(lang) => { versionState.currentFeature.language = lang; }"
                />
            </div>
        </template>
    </Modal>
</template>

<script>
    import Modal from '@Partials/Modal.vue';
    import CodeEditorWrapper from '@Pages/versions/_components/editors/CodeEditor.vue';

    export default {
        inject: ['versionState'],
        name: 'CodeContentEditorModal',
        components: { Modal, CodeEditorWrapper },
        data() {
            return {
                resetTimeout: null,
                isFullscreen: false,
            }
        },
        methods: {
            handleReset() {
                this.resetTimeout = setTimeout(() => {
                    this.isFullscreen = false;
                }, 500);
            }
        }
    };
</script>
