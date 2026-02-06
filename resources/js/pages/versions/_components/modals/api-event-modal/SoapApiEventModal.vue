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
                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-600 rounded-xl text-white shadow-lg shadow-emerald-100">
                            <FileCode size="20" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight">SOAP Service Configuration</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                Protocol: SOAP {{ event.soap_version }}
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
                                            label="Version"
                                            v-model="event.soap_version"
                                            :options="[{label:'SOAP 1.1', value:'1.1'}, {label:'SOAP 1.2', value:'1.2'}]"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <Input label="WSDL / Endpoint URL" v-model="event.url" placeholder="http://example.com/service?wsdl" />
                                    </div>
                                </div>
                                <Input label="SOAP Action (Header)" v-model="event.soap_action" placeholder="http://tempuri.org/GetUserData" />
                            </div>

                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl grid grid-cols-2 gap-4">
                                <Input label="XML Response As" v-model="event.response_variable">
                                    <template #prefix><span class="text-sm font-black text-emerald-500 pr-2 border-r border-slate-300 pl-2.5">@</span></template>
                                </Input>
                                <Input label="Status Code As" v-model="event.status_variable">
                                    <template #prefix><span class="text-sm font-black text-emerald-500 pr-2 border-r border-slate-300 pl-2.5">@</span></template>
                                </Input>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">SOAP Envelope (XML)</p>
                                </div>
                                <div class="rounded-xl overflow-hidden border border-slate-200">
                                    <CodeEditorWrapper
                                        language="xml"
                                        height="h-[400px]"
                                        v-model="event.envelope"
                                        type="api_event_raw_json"
                                        :hideLanguageSelector="true"
                                        v-model:isFullscreen="isFullscreen"
                                        @exit="$refs.modal.hideModal()"
                                    />
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
import { FileCode } from 'lucide-vue-next';
import CodeEditorWrapper from '@Pages/versions/_components/editors/CodeEditor.vue';

export default {
    name: 'SoapEventModal',
    components: { Modal, Tabs, Input, Select, Button, CodeEditorWrapper, FileCode },
    inject: ['versionState'],
    data() { return { isFullscreen: false }; },
    computed: {
        event() { return this.versionState.builder.events[this.versionState.currentEventId]; }
    }
}
</script>
