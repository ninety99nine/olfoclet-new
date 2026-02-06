<template>
    <Modal
        size="lg"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false"
        :backdropClass="{ 'opacity-0 pointer-events-none' : isFullscreen }">

        <template #content>
            <div v-if="event" class="flex flex-col h-full bg-white select-none">

                <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6 pt-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-indigo-600 rounded-xl text-white shadow-lg shadow-indigo-100">
                            <Globe size="20" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight">API Event</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                Triggered {{ event.timing }} step
                            </p>
                        </div>
                    </div>
                    <Tabs design="1" class="mr-20" v-model="event.source" :tabs="[{label: 'Visual Builder', value: 'manual'}, {label: 'Code Mode', value: 'code'}]" />
                </div>

                <div class="flex-1 overflow-y-auto pr-2 pb-10 overflow-x-hidden">
                    <Transition name="fade-slide" mode="out-in">

                        <div v-if="event.source === 'manual'" key="manual" class="space-y-6">

                            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5 space-y-5">
                                <div class="flex gap-3">
                                    <div class="w-32">
                                        <Select
                                            :search="false"
                                            label="Method"
                                            v-model="event.method"
                                            :options="['GET','POST','PUT','DELETE','PATCH'].map(m => ({ label: m, value: m }))"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <Input label="Endpoint URL" v-model="event.url" placeholder="https://api.example.com/v1/resource" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <Input label="Response Variable" v-model="event.response_variable" placeholder="apiData">
                                        <template #prefix><span class="text-sm font-black text-indigo-500 pr-2 border-r border-slate-300 pl-2.5">@</span></template>
                                    </Input>
                                    <Input label="Status Variable" v-model="event.status_variable" placeholder="apiStatus">
                                        <template #prefix><span class="text-sm font-black text-indigo-500 pr-2 border-r border-slate-300 pl-2.5">@</span></template>
                                    </Input>
                                </div>
                            </div>

                            <div v-if="['POST', 'PUT', 'PATCH'].includes(event.method)" class="rounded-2xl border border-slate-200 overflow-hidden">
                                <div class="flex items-center justify-between px-5 py-3 bg-slate-100/50 border-b border-slate-200">
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500">Request Body</div>
                                    <Tabs size="sm" v-model="event.body_type" :tabs="[{label: 'Form Fields', value: 'form'}, {label: 'Raw JSON', value: 'raw'}]" />
                                </div>
                                <div class="p-5">
                                    <div v-if="event.body_type === 'form'">
                                        <KeyValueEditor v-model="event.body_form" emptyText="No payload parameters" />
                                    </div>
                                    <div v-else class="rounded-xl overflow-hidden border border-slate-200">
                                        <CodeEditorWrapper
                                            language="json"
                                            height="h-[350px]"
                                            v-model="event.body"
                                            type="api_event_raw_json"
                                            :hideLanguageSelector="true"
                                            v-model:isFullscreen="isFullscreen"
                                            @exit="$refs.modal.hideModal()"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                <div class="px-5 py-3 bg-slate-100/50 border-b border-slate-200">
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500">Request Headers</div>
                                </div>
                                <div class="p-5">
                                    <KeyValueEditor v-model="event.headers" emptyText="No custom headers" />
                                </div>
                            </div>
                        </div>

                        <div v-else key="code" class="h-full">
                            <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-xl">
                                <CodeEditorWrapper
                                    type="api_event"
                                    height="h-[520px]"
                                    v-model="event.code_content"
                                    v-model:language="event.code_language"
                                    v-model:isFullscreen="isFullscreen"
                                    @exit="$refs.modal.hideModal()"
                                />
                            </div>
                        </div>

                    </Transition>
                </div>

                <div class="flex justify-end pt-5 border-t border-slate-100">
                    <Button type="primary" mode="solid" buttonClass="rounded-lg px-8 font-bold" :action="() => $refs.modal.hideModal()">
                        Done
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '@Partials/Modal.vue';
import Tabs from '@Partials/Tabs.vue';
import Input from '@Partials/Input.vue';
import Select from '@Partials/Select.vue';
import Button from '@Partials/Button.vue';
import { Globe } from 'lucide-vue-next';
import KeyValueEditor from '@Pages/versions/_components/modals/api-event-modal/_components/KeyValueEditor.vue';
import CodeEditorWrapper from '@Pages/versions/_components/editors/CodeEditor.vue';

export default {
    name: 'ApiEventModal',
    components: { Modal, Tabs, Input, Select, Button, KeyValueEditor, CodeEditorWrapper, Globe },
    inject: ['versionState'],
    data() { return { isFullscreen: false }; },
    computed: {
        event() {
            const currentEvent = this.versionState.builder?.events?.[this.versionState.currentEventId];
            if (currentEvent && !currentEvent.code_language) {
                currentEvent.code_language = 'php';
            }
            return currentEvent;
        }
    }
}
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-slide-enter-from { opacity: 0; transform: translateY(10px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
