<template>
    <Teleport to="body" :disabled="!isFullscreen">
        <transition name="fullscreen-fade">
            <div
                v-if="true"
                @mousedown.stop
                @click.self="isFullscreen ? toggleFullscreen() : null"
                :key="isFullscreen ? 'full' : 'inline'"
                :class="[
                    isFullscreen
                        ? 'fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm p-4 md:p-10 flex flex-col cursor-pointer'
                        : 'w-full h-full flex flex-col',
                    customClass
                ]">
                <div class="bg-slate-950 rounded-xl border border-slate-800 shadow-lg flex flex-col overflow-hidden h-full cursor-default">

                    <div class="flex items-center justify-between bg-slate-900 px-4 py-2 border-b border-slate-800 shrink-0">
                        <div class="flex gap-4">
                            <button @click="activeView = 'editor'" type="button"
                                :class="['text-[10px] font-bold uppercase tracking-widest cursor-pointer transition-colors', activeView === 'editor' ? 'text-amber-400' : 'text-slate-500 hover:text-slate-300']">
                                Editor
                            </button>
                            <button @click="activeView = 'guide'" type="button"
                                :class="['text-[10px] font-bold uppercase tracking-widest cursor-pointer transition-colors', activeView === 'guide' ? 'text-amber-400' : 'text-slate-500 hover:text-slate-300']">
                                Guide
                            </button>
                        </div>

                        <div class="flex items-center gap-4">

                            <select v-if="activeView === 'editor'" v-model="fontSize" class="bg-slate-800 text-[10px] font-bold text-white px-3 py-1 rounded border-none cursor-pointer outline-none">
                                <option v-for="s in [10, 12, 14, 16, 18, 20]" :key="s" :value="s">{{ s }}px</option>
                            </select>

                            <div class="flex items-center gap-1 bg-slate-800 px-3 py-1 rounded">
                                <Terminal size="14" class="text-slate-500" />
                                <select v-if="!hideLanguageSelector" :value="language" @change="handleLanguageChange"
                                    class="bg-transparent text-[10px] font-bold text-white border-none outline-none cursor-pointer pr-2">
                                    <option value="php">PHP</option>
                                    <option value="javascript">JS</option>
                                    <option value="python">PY</option>
                                    <option value="json">JSON</option>
                                </select>
                                <span v-else class="text-[10px] font-bold text-slate-300 uppercase">{{ language }}</span>
                            </div>

                            <div class="flex items-center gap-1">
                                <button v-if="showFullscreenToggle" type="button" @click.stop="toggleFullscreen"
                                    class="p-1.5 hover:bg-slate-800 rounded text-slate-400 hover:text-white transition-colors cursor-pointer">
                                    <component :is="isFullscreen ? Minimize2 : Maximize2" size="14" />
                                </button>
                                <button type="button" @click.stop="handleExit" class="p-1.5 hover:bg-slate-800 rounded text-slate-400 hover:text-white transition-colors cursor-pointer">
                                    <X size="14"></X>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-hidden relative">
                        <transition name="fade-fast" mode="out-in">
                            <div v-if="activeView === 'editor'" key="editor" :class="isFullscreen ? 'h-full' : (activeView === 'editor' ? height: null)">
                                <CodeEditor :language="language" :model-value="modelValue"
                                    @update:modelValue="$emit('update:modelValue', $event)" :options="{
                                        fontSize: fontSize,
                                        padding: { top: 20 },
                                        automaticLayout: true,
                                        scrollBeyondLastLine: false,
                                        minimap: { enabled: isFullscreen },
                                        ...options
                                    }" />
                            </div>

                            <div v-else key="guide" class="p-5 md:p-10 space-y-6 h-full overflow-y-auto bg-slate-950">
                                <div class="max-w-4xl mx-auto space-y-6 text-white">
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <button @click="prevTemplate" type="button" class="p-1.5 hover:bg-slate-800 rounded text-slate-400 cursor-pointer">
                                                    <ChevronLeft size="20" />
                                                </button>
                                                <div :class="[isFullscreen ? 'w-96' : 'w-60', 'text-center']">
                                                    <h3 :class="[isFullscreen ? 'text-xl' : 'text-xs', 'font-bold text-amber-400 uppercase tracking-widest']">{{ currentTemplate.title }}</h3>
                                                    <p :class="[isFullscreen ? 'text-sm' : 'text-[10px]', 'text-slate-500 mt-0.5']">{{ currentTemplate.desc }}</p>
                                                </div>
                                                <button @click="nextTemplate" type="button" class="p-1.5 hover:bg-slate-800 rounded text-slate-400 cursor-pointer">
                                                    <ChevronRight size="20" />
                                                </button>
                                            </div>
                                            <button
                                                @click="useTemplate"
                                                type="button"
                                                class="px-3 py-1.5 bg-amber-500/10 text-amber-500 border border-amber-500/20 rounded-lg text-[11px] font-bold hover:bg-amber-500 hover:text-white transition-all cursor-pointer">
                                                Use Template
                                            </button>
                                        </div>

                                        <div class="relative overflow-hidden h-84 bg-slate-900/50 border border-slate-800 rounded-xl shadow-inner group">
                                            <transition :name="slideDirection" mode="out-in">
                                                <pre
                                                    :key="currentTemplateIndex"
                                                    :class="[isFullscreen ? 'text-xs' : 'text-[11px]', 'absolute inset-0 m-0 p-6 select-text font-mono text-indigo-200/80 overflow-auto leading-relaxed whitespace-pre']"
                                                >{{ currentTemplate.code }}</pre>
                                            </transition>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 rounded-xl border border-slate-800 bg-slate-900/30">
                                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Requirement</p>
                                            <p v-if="type === 'content_feature'" class="text-[11px] text-slate-300">Return a <b class="text-amber-400">String</b>.<br> The returned text is displayed to the user.</p>
                                            <p v-if="type === 'api_event_raw_json'" class="text-[11px] text-slate-300">Return a <b class="text-amber-400">JSON Object</b>.<br> Define the keys and values for the payload.</p>
                                            <p v-if="type === 'select_feature'" class="text-[11px] text-slate-300">Return an <b class="text-amber-400">Array</b>.<br> Define label, trigger, and next_step_id for each option.</p>
                                            <p v-else-if="type === 'api_event'" class="text-[11px] text-slate-300">Return an <b class="text-amber-400">Array/Object</b>.<br> Define the method, url, headers, and body.</p>
                                        </div>
                                        <div class="p-4 rounded-xl border border-slate-800 bg-slate-900/30">
                                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Variables</p>
                                            <p class="text-[11px] text-slate-300">Use <span class="text-indigo-400 font-mono">@</span> to inject data like <span class="text-indigo-300">@firstName</span> or <span class="text-indigo-300">@balance</span>.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script>
import CodeEditor from '@Partials/CodeEditor.vue';
import { X, Terminal, Maximize2, Minimize2, ChevronLeft, ChevronRight } from 'lucide-vue-next';

export default {
    name: 'CodeEditorWrapper',
    inheritAttrs: false,
    components: { X, Terminal, ChevronLeft, ChevronRight, CodeEditor },
    props: {
        modelValue: String,
        options: { type: Object, default: () => ({}) },
        language: { type: String, default: 'php' },
        height: { type: String, default: 'h-[501px]' },
        isFullscreen: { type: Boolean, default: false },
        showFullscreenToggle: { type: Boolean, default: true },
        hideLanguageSelector: { type: Boolean, default: false },
        class: { type: String, default: '' },
        type: {
            type: String,
            default: 'content_feature',
            validator: (value) => ['content_feature', 'api_event', 'api_event_raw_json', 'select_feature'].includes(value)
        }
    },
    emits: ['update:modelValue', 'update:language', 'update:isFullscreen', 'exit'],
    data() {
        return {
            Maximize2, Minimize2,
            fontSize: 12,
            activeView: 'editor',
            currentTemplateIndex: 0,
            slideDirection: 'slide-left'
        };
    },
    watch: {
        isFullscreen(val) {
            document.body.style.overflow = val ? 'hidden' : '';
        }
    },
    computed: {
        customClass() {
            return this.class;
        },
        templateLibrary() {
            return {
                content_feature: [
                    {
                        title: "Personalized Greeting",
                        desc: "Simple dynamic string using captured variables",
                        php: "<?php\n\nreturn \"Hello @firstName, welcome to our service.\";",
                        javascript: "return `Hello @firstName, welcome to our service.`;",
                        python: "return \"Hello @firstName, welcome to our service.\""
                    },
                    {
                        title: "Conditional Response",
                        desc: "Logic based on user attributes",
                        php: "<?php\n\nif ('@memberType' === 'premium') {\n    return \"Welcome back, VIP!\";\n}\n\nreturn \"Upgrade now to get more features!\";",
                        javascript: "if ('@memberType' === 'premium') {\n    return \"Welcome back, VIP!\";\n}\n\nreturn \"Upgrade now to get more features!\";",
                        python: "if '@memberType' == 'premium':\n    return \"Welcome back, VIP!\"\n\nreturn \"Upgrade now to get more features!\""
                    }
                ],
                api_event: [
                    {
                        title: "GET Request (Fetch Data)",
                        desc: "Basic GET with Query Parameters and Headers",
                        php: "<?php\n\nreturn [\n    \"method\" => \"GET\",\n    \"url\" => \"https://api.example.com/customers/@id\",\n    \"query\" => [\n        \"include_meta\" => \"true\",\n        \"lang\" => \"@language\"\n    ],\n    \"headers\" => [\n        \"Authorization\" => \"Bearer @apiToken\",\n        \"Content-Type\" => \"application/json\"\n    ]\n];",
                        javascript: "return {\n    method: \"GET\",\n    url: \"https://api.example.com/customers/@id\",\n    query: {\n        include_meta: \"true\",\n        lang: \"@language\"\n    },\n    headers: {\n        Authorization: \"Bearer @apiToken\",\n        \"Content-Type\": \"application/json\"\n    }\n};",
                        python: "return {\n    \"method\": \"GET\",\n    \"url\": \"https://api.example.com/customers/@id\",\n    \"query\": {\n        \"include_meta\": \"true\",\n        \"lang\": \"@language\"\n    },\n    \"headers\": {\n        \"Authorization\": \"Bearer @apiToken\",\n        \"Content-Type\": \"application/json\"\n    }\n}"
                    },
                    {
                        title: "POST Request (Create)",
                        desc: "Sending JSON data to an endpoint",
                        php: "<?php\n\nreturn [\n    \"method\" => \"POST\",\n    \"url\" => \"https://api.example.com/orders\",\n    \"body\" => [\n        \"customer_id\" => \"@id\",\n        \"amount\" => \"@total\",\n        \"currency\" => \"USD\"\n    ],\n    \"headers\" => [\n        \"X-API-KEY\" => \"@secretKey\"\n    ]\n];",
                        javascript: "return {\n    method: \"POST\",\n    url: \"https://api.example.com/orders\",\n    body: {\n        customer_id: \"@id\",\n        amount: \"@total\",\n        currency: \"USD\"\n    },\n    headers: {\n        \"X-API-KEY\": \"@secretKey\"\n    }\n};",
                        python: "return {\n    \"method\": \"POST\",\n    \"url\": \"https://api.example.com/orders\",\n    \"body\": {\n        \"customer_id\": \"@id\",\n        \"amount\": \"@total\",\n        \"currency\": \"USD\"\n    },\n    \"headers\": {\n        \"X-API-KEY\": \"@secretKey\"\n    }\n}"
                    }
                ],
                api_event_raw_json: [
                    {
                        title: "Simple JSON Payload",
                        desc: "Basic key-value pairs",
                        code: "{\n  \"customer_id\": \"@id\",\n  \"status\": \"active\",\n  \"source\": \"USSD\"\n}"
                    }
                ],
                select_feature: [
                    {
                        title: "Standard Services",
                        desc: "Basic numbered menu options with direct step links",
                        php: "<?php\n\nreturn [\n\t[\n\t\t'label' => '1. Check Balance',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t],\n\t[\n\t\t'label' => '2. Buy Airtime',\n\t\t'trigger' => '2',\n\t\t'next_step_id' => '550e8400-e29b-41d4-a716-446655440000'\n\t],\n\t[\n\t\t'label' => '3. Mini Statement',\n\t\t'trigger' => '3',\n\t\t'next_step_id' => '8a7b6c5d-4e3f-21a0-b9c8-d7e6f5a4b3c2'\n\t],\n\t[\n\t\t'label' => '0. Back',\n\t\t'trigger' => '0',\n\t\t'next_step_id' => 'main_menu'\n\t]\n];",
                        javascript: "return [\n\t{\n\t\tlabel: '1. Check Balance',\n\t\ttrigger: '1',\n\t\tnext_step_id: '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\tlabel: '2. Buy Airtime',\n\t\ttrigger: '2',\n\t\tnext_step_id: '550e8400-e29b-41d4-a716-446655440000'\n\t},\n\t{\n\t\tlabel: '3. Mini Statement',\n\t\ttrigger: '3',\n\t\tnext_step_id: null\n\t},\n\t{\n\t\tlabel: '0. Back',\n\t\ttrigger: '0',\n\t\tnext_step_id: 'main_menu'\n\t}\n];",
                        python: "return [\n\t{\n\t\t'label': '1. Check Balance',\n\t\t'trigger': '1',\n\t\t'next_step_id': '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\t'label': '2. Buy Airtime',\n\t\t'trigger': '2',\n\t\t'next_step_id': '550e8400-e29b-41d4-a716-446655440000'\n\t},\n\t{\n\t\t'label': '3. Mini Statement',\n\t\t'trigger': '3',\n\t\t'next_step_id': None\n\t},\n\t{\n\t\t'label': '0. Back',\n\t\t'trigger': '0',\n\t\t'next_step_id': 'main_menu'\n\t}\n]"
                    },
                    {
                        title: "Dynamic User Data",
                        desc: "Using @variables with dynamic routing",
                        php: "<?php\n\nreturn [\n\t[\n\t\t'label' => 'Pay for @billName',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '456c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t],\n\t[\n\t\t'label' => 'View @billDate Due',\n\t\t'trigger' => '2',\n\t\t'next_step_id' => null\n\t],\n\t[\n\t\t'label' => 'Update @accountType',\n\t\t'trigger' => '3',\n\t\t'next_step_id' => null\n\t],\n\t[\n\t\t'label' => '0. Back',\n\t\t'trigger' => '0',\n\t\t'next_step_id' => 'main_menu'\n\t]\n];",
                        javascript: "return [\n\t{\n\t\tlabel: 'Pay for @billName',\n\t\ttrigger: '1',\n\t\tnext_step_id: '456c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\tlabel: 'View @billDate Due',\n\t\ttrigger: '2',\n\t\tnext_step_id: null\n\t},\n\t{\n\t\tlabel: 'Update @accountType',\n\t\ttrigger: '3',\n\t\tnext_step_id: null\n\t},\n\t{\n\t\tlabel: '0. Back',\n\t\ttrigger: '0',\n\t\tnext_step_id: 'main_menu'\n\t}\n];",
                        python: "return [\n\t{\n\t\t'label': 'Pay for @billName',\n\t\t'trigger': '1',\n\t\t'next_step_id': '456c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\t'label': 'View @billDate Due',\n\t\t'trigger': '2',\n\t\t'next_step_id': None\n\t},\n\t{\n\t\t'label': 'Update @accountType',\n\t\t'trigger': '3',\n\t\t'next_step_id': None\n\t},\n\t{\n\t\t'label': '0. Back',\n\t\t'trigger': '0',\n\t\t'next_step_id': 'main_menu'\n\t}\n]"
                    },
                    {
                        title: "Account Selection",
                        desc: "Vertical list of user accounts with UUID keys",
                        php: "<?php\n\nreturn [\n\t[\n\t\t'label' => 'Savings (@acc1)',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '789c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t],\n\t[\n\t\t'label' => 'Current (@acc2)',\n\t\t'trigger' => '2',\n\t\t'next_step_id' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'\n\t],\n\t[\n\t\t'label' => 'Credit (@acc3)',\n\t\t'trigger' => '3',\n\t\t'next_step_id' => 'd4e5f6a1-b2c3-4d5e-6f7a-8b9c0d1e2f3a'\n\t],\n\t[\n\t\t'label' => '0. Cancel',\n\t\t'trigger' => '0',\n\t\t'next_step_id' => 'exit_step'\n\t]\n];",
                        javascript: "return [\n\t{\n\t\tlabel: 'Savings (@acc1)',\n\t\ttrigger: '1',\n\t\tnext_step_id: '789c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\tlabel: 'Current (@acc2)',\n\t\ttrigger: '2',\n\t\tnext_step_id: 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'\n\t},\n\t{\n\t\tlabel: 'Credit (@acc3)',\n\t\ttrigger: '3',\n\t\tnext_step_id: 'd4e5f6a1-b2c3-4d5e-6f7a-8b9c0d1e2f3a'\n\t},\n\t{\n\t\tlabel: '0. Cancel',\n\t\ttrigger: '0',\n\t\tnext_step_id: 'exit_step'\n\t}\n];",
                        python: "return [\n\t{\n\t\t'label': 'Savings (@acc1)',\n\t\t'trigger': '1',\n\t\t'next_step_id': '789c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\t'label': 'Current (@acc2)',\n\t\t'trigger': '2',\n\t\t'next_step_id': 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'\n\t},\n\t{\n\t\t'label': 'Credit (@acc3)',\n\t\t'trigger': '3',\n\t\t'next_step_id': 'd4e5f6a1-b2c3-4d5e-6f7a-8b9c0d1e2f3a'\n\t},\n\t{\n\t\t'label': '0. Cancel',\n\t\t'trigger': '0',\n\t\t'next_step_id': 'exit_step'\n\t}\n]"
                    },
                    {
                        title: "Product/Storefront",
                        desc: "Listing dynamic bundles and their triggers",
                        php: "<?php\n\nreturn [\n\t[\n\t\t'label' => 'Bundle A (@priceA)',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '321c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t],\n\t[\n\t\t'label' => 'Bundle B (@priceB)',\n\t\t'trigger' => '2',\n\t\t'next_step_id' => 'f5e4d3c2-b1a0-4987-a654-c3b2a1098765'\n\t],\n\t[\n\t\t'label' => 'Bundle C (@priceC)',\n\t\t'trigger' => '3',\n\t\t'next_step_id' => '09876543-21ba-4cde-f012-3456789abcde'\n\t],\n\t[\n\t\t'label' => '0. Main Menu',\n\t\t'trigger' => '0',\n\t\t'next_step_id' => 'home'\n\t]\n];",
                        javascript: "return [\n\t{\n\t\tlabel: 'Bundle A (@priceA)',\n\t\ttrigger: '1',\n\t\tnext_step_id: '321c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\tlabel: 'Bundle B (@priceB)',\n\t\ttrigger: '2',\n\t\tnext_step_id: 'f5e4d3c2-b1a0-4987-a654-c3b2a1098765'\n\t},\n\t{\n\t\tlabel: 'Bundle C (@priceC)',\n\t\ttrigger: '3',\n\t\tnext_step_id: '09876543-21ba-4cde-f012-3456789abcde'\n\t},\n\t{\n\t\tlabel: '0. Main Menu',\n\t\ttrigger: '0',\n\t\tnext_step_id: 'home'\n\t}\n];",
                        python: "return [\n\t{\n\t\t'label': 'Bundle A (@priceA)',\n\t\t'trigger': '1',\n\t\t'next_step_id': '321c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\t'label': 'Bundle B (@priceB)',\n\t\t'trigger': '2',\n\t\t'next_step_id': 'f5e4d3c2-b1a0-4987-a654-c3b2a1098765'\n\t},\n\t{\n\t\t'label': 'Bundle C (@priceC)',\n\t\t'trigger': '3',\n\t\t'next_step_id': '09876543-21ba-4cde-f012-3456789abcde'\n\t},\n\t{\n\t\t'label': '0. Main Menu',\n\t\t'trigger': '0',\n\t\t'next_step_id': 'home'\n\t}\n]"
                    },
                    {
                        title: "Navigation Logic",
                        desc: "Complex routing for error handling or support",
                        php: "<?php\n\nreturn [\n\t[\n\t\t'label' => 'Try Again',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '654c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t],\n\t[\n\t\t'label' => 'Contact Support',\n\t\t'trigger' => '2',\n\t\t'next_step_id' => 'support_chat_step'\n\t],\n\t[\n\t\t'label' => '0. Back',\n\t\t'trigger' => '0',\n\t\t'next_step_id' => 'previous_step'\n\t],\n\t[\n\t\t'label' => '99. Home',\n\t\t'trigger' => '99',\n\t\t'next_step_id' => 'main_menu'\n\t]\n];",
                        javascript: "return [\n\t{\n\t\tlabel: 'Try Again',\n\t\ttrigger: '1',\n\t\tnext_step_id: '654c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\tlabel: 'Contact Support',\n\t\ttrigger: '2',\n\t\tnext_step_id: 'support_chat_step'\n\t},\n\t{\n\t\tlabel: '0. Back',\n\t\ttrigger: '0',\n\t\tnext_step_id: 'previous_step'\n\t},\n\t{\n\t\tlabel: '99. Home',\n\t\ttrigger: '99',\n\t\tnext_step_id: 'main_menu'\n\t}\n];",
                        python: "return [\n\t{\n\t\t'label': 'Try Again',\n\t\t'trigger': '1',\n\t\t'next_step_id': '654c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\t'label': 'Contact Support',\n\t\t'trigger': '2',\n\t\t'next_step_id': 'support_chat_step'\n\t},\n\t{\n\t\t'label': '0. Back',\n\t\t'trigger': '0',\n\t\t'next_step_id': 'previous_step'\n\t},\n\t{\n\t\t'label': '99. Home',\n\t\t'trigger': '99',\n\t\t'next_step_id': 'main_menu'\n\t}\n]"
                    }
                ]
            };
        },
        activeTemplates() { return this.templateLibrary[this.type] || this.templateLibrary.content_feature; },
        currentTemplates() {
            if (this.type === 'api_event_raw_json') { return this.activeTemplates; }
            const lang = (this.language || 'php').toLowerCase();
            const key = (lang === 'javascript' || lang === 'js') ? 'javascript' : (lang === 'python' || lang === 'py' ? 'python' : 'php');
            return this.activeTemplates.map(t => ({ title: t.title, description: t.desc, code: t[key] }));
        },
        currentTemplate() { return this.currentTemplates[this.currentTemplateIndex] || this.currentTemplates[0]; }
    },
    methods: {
        toggleFullscreen() {
            this.$emit('update:isFullscreen', !this.isFullscreen);
        },
        handleExit() {
            this.$emit('update:isFullscreen', false);
            this.$emit('exit');
        },
        ensureInitialValue() {
            if (!this.modelValue || this.modelValue.trim() === '') {
                this.$emit('update:modelValue', this.getSampleForLanguage(this.language));
            }
        },
        getSampleForLanguage(lang) {
            const cleanLang = (lang || 'php').toLowerCase();
            const guideHint = "Check the 'GUIDE' tab above for more examples.";

            if (this.type === 'api_event') {
                if (cleanLang === 'php') {
                    return `<?php\n\n// ${guideHint}\nreturn [\n    \"method\" => \"GET\",\n    \"url\" => \"https://api.example.com/resource\",\n    \"headers\" => [\n        \"Content-Type\" => \"application/json\",\n        \"Authorization\" => \"Bearer @apiToken\"\n    ]\n];`;
                }
                if (cleanLang === 'python' || cleanLang === 'py') {
                    return `# ${guideHint}\nreturn {\n    \"method\": \"GET\",\n    \"url\": \"https://api.example.com/resource\",\n    \"headers\": {\n        \"Content-Type\": \"application/json\",\n        \"Authorization\": \"Bearer @apiToken\"\n    }\n}`;
                }
                return `// ${guideHint}\nreturn {\n    method: \"GET\",\n    url: \"https://api.example.com/resource\",\n    headers: {\n        \"Content-Type\": \"application/json\",\n        Authorization: \"Bearer @apiToken\"\n    }\n};`;
            }

            if (this.type === 'select_feature') {
                if (cleanLang === 'php') {
                    return `<?php\n\n// ${guideHint}\n\nreturn [\n\t[\n\t\t'label' => '1. Check Balance',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t],\n\t[\n\t\t'label' => '2. Buy Airtime',\n\t\t'trigger' => '2',\n\t\t'next_step_id' => '550e8400-e29b-41d4-a716-446655440000'\n\t],\n\t[\n\t\t'label' => '3. Mini Statement',\n\t\t'trigger' => '3',\n\t\t'next_step_id' => '8a7b6c5d-4e3f-21a0-b9c8-d7e6f5a4b3c2'\n\t],\n\t[\n\t\t'label' => '0. Back',\n\t\t'trigger' => '0',\n\t\t'next_step_id' => 'main_menu'\n\t]\n];`;
                } else if (cleanLang === 'python' || cleanLang === 'py') {
                    return `# ${guideHint}\n\nreturn [\n\t{\n\t\t'label': '1. Check Balance',\n\t\t'trigger': '1',\n\t\t'next_step_id': '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\t'label': '2. Buy Airtime',\n\t\t'trigger': '2',\n\t\t'next_step_id': '550e8400-e29b-41d4-a716-446655440000'\n\t},\n\t{\n\t\t'label': '3. Mini Statement',\n\t\t'trigger': '3',\n\t\t'next_step_id': None\n\t},\n\t{\n\t\t'label': '0. Back',\n\t\t'trigger': '0',\n\t\t'next_step_id': 'main_menu'\n\t}\n]`;
                } else {
                    return `// ${guideHint}\n\nreturn [\n\t{\n\t\tlabel: '1. Check Balance',\n\t\ttrigger: '1',\n\t\tnext_step_id: '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\tlabel: '2. Buy Airtime',\n\t\ttrigger: '2',\n\t\tnext_step_id: '550e8400-e29b-41d4-a716-446655440000'\n\t},\n\t{\n\t\tlabel: '3. Mini Statement',\n\t\ttrigger: '3',\n\t\tnext_step_id: null\n\t},\n\t{\n\t\tlabel: '0. Back',\n\t\ttrigger: '0',\n\t\tnext_step_id: 'main_menu'\n\t}\n];`;
                }
            }

            // Default for content_feature
            if (cleanLang === 'php') return `<?php\n\n// ${guideHint}\nreturn \"Hello @firstName!\";`;
            if (cleanLang === 'python' || cleanLang === 'py') return `# ${guideHint}\nreturn \"Hello @firstName!\"`;
            return `// ${guideHint}\nreturn \"Hello @firstName!\";`;
        },
        handleLanguageChange(event) { this.$emit('update:language', event.target.value); },
        nextTemplate() {
            this.slideDirection = 'slide-left';
            this.$nextTick(() => { this.currentTemplateIndex = (this.currentTemplateIndex + 1) % this.currentTemplates.length; });
        },
        prevTemplate() {
            this.slideDirection = 'slide-right';
            this.$nextTick(() => { this.currentTemplateIndex = (this.currentTemplateIndex - 1 + this.currentTemplates.length) % this.currentTemplates.length; });
        },
        useTemplate() {
            this.$emit('update:modelValue', this.currentTemplate.code);
            this.activeView = 'editor';
        }
    },
    mounted() { this.ensureInitialValue(); },
    beforeUnmount() { document.body.style.overflow = ''; }
};
</script>

<style scoped>
    /* Fullscreen Minimizing/Maximizing Transition */
    .fullscreen-fade-enter-active,
    .fullscreen-fade-leave-active {
        transition: all 0.3s ease-in-out;
    }
    .fullscreen-fade-enter-from,
    .fullscreen-fade-leave-to {
        opacity: 0;
        transform: scale(0.95);
    }

    .fade-fast-enter-active, .fade-fast-leave-active { transition: opacity 0.15s ease; }
    .fade-fast-enter-from, .fade-fast-leave-to { opacity: 0; }
    .slide-left-enter-active, .slide-left-leave-active, .slide-right-enter-active, .slide-right-leave-active {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .slide-left-enter-from { opacity: 0; transform: translateX(30px); }
    .slide-left-leave-to { opacity: 0; transform: translateX(-30px); }
    .slide-right-enter-from { opacity: 0; transform: translateX(-30px); }
    .slide-right-leave-to { opacity: 0; transform: translateX(30px); }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #020617; }
    ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #334155; }
</style>
