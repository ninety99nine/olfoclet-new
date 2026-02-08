<template>
    <div class="flex flex-col items-center w-full">
        <div class="mb-6 flex items-center gap-4">
            <div class="flex p-1 bg-slate-200/50 backdrop-blur-sm rounded-xl w-48 shadow-inner border border-slate-200/50">
                <button
                    @click="view = 'phone'"
                    :class="[view === 'phone' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
                    class="flex-1 flex items-center justify-center gap-2 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer"
                >
                    <Smartphone size="14" />
                    Device
                </button>
                <button
                    @click="view = 'logs'"
                    :class="[view === 'logs' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
                    class="flex-1 flex items-center justify-center gap-2 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer"
                >
                    <Activity size="14" />
                    Activity
                </button>
            </div>

            <div v-if="sessionId" class="flex items-center gap-1 bg-slate-200/50 p-1 rounded-xl border border-slate-200/50 animate-in fade-in zoom-in duration-300">
                <button
                    @click="resetSession"
                    title="Restart Session"
                    class="p-1.5 text-slate-600 hover:text-indigo-600 hover:bg-white rounded-lg transition-all cursor-pointer">
                    <RotateCcw size="14" />
                </button>
                <div class="h-3 w-px bg-slate-300 mx-0.5"></div>
                <button
                    @click="endSession"
                    title="Stop Session"
                    class="p-1.5 text-slate-600 hover:text-rose-600 hover:bg-white rounded-lg transition-all cursor-pointer">
                    <Square size="14" fill="currentColor" />
                </button>
            </div>
        </div>

        <div v-if="view === 'phone'" class="mockup-phone shadow-2xl transition-all duration-300" :class="{ 'scale-[0.98] opacity-90': loading }">
            <div class="mockup-phone-camera"></div>
            <div class="mockup-phone-display bg-slate-50 relative overflow-hidden">
                <div class="absolute top-0 w-full h-6 bg-slate-900/10 z-10 flex justify-between items-center px-6">
                    <span class="text-[9px] font-bold text-slate-500">9:41</span>
                    <div class="flex gap-1">
                        <div class="w-2.5 h-2.5 bg-slate-600/20 rounded-full opacity-50"></div>
                        <div class="w-2.5 h-2.5 bg-slate-600/30 rounded-full opacity-50"></div>
                        <div class="w-2.5 h-2.5 bg-slate-600/40 rounded-full"></div>
                    </div>
                </div>

                <div class="h-[550px] w-[280px] pt-12 pb-4 px-4 flex flex-col relative bg-slate-100">

                    <Transition name="fade">
                        <div v-if="loading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-20 flex items-center justify-center">
                            <div class="w-8 h-8 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    </Transition>

                    <Transition name="slide-fade" mode="out-in" @after-enter="focusInput">
                        <div v-if="sessionId" :key="'session-' + currentMessageId" class="flex-1 flex flex-col justify-center">
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                                <div class="absolute -top-3 left-4 bg-indigo-600 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider z-10 shadow-sm">
                                    {{ activeProfileLabel }}
                                </div>

                                <p class="text-sm text-slate-800 whitespace-pre-wrap leading-relaxed mb-4">
                                    {{ currentMessage }}
                                </p>

                                <div v-if="awaitingInput">
                                    <input
                                        ref="ussdInput"
                                        type="text"
                                        v-model="userInput"
                                        @keyup.enter="sendResponse"
                                        class="mockup-phone-input text-sm text-slate-900 placeholder:text-slate-300"
                                        placeholder="Reply"
                                        autofocus
                                    />
                                    <div class="flex justify-between mt-5 text-sm font-medium px-8">
                                        <button @click="endSession" class="text-slate-400 hover:text-rose-500 transition-colors px-2 py-1 -ml-2 rounded hover:bg-rose-50 cursor-pointer">Cancel</button>
                                        <button @click="sendResponse" :disabled="!userInput && awaitingInput" class="text-indigo-600 hover:text-indigo-700 transition-colors px-2 py-1 -mr-2 rounded hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">Send</button>
                                    </div>
                                </div>
                                <div v-else class="mt-4 flex justify-center">
                                    <button @click="endSession" class="text-xs bg-slate-100 text-slate-600 px-4 py-2 rounded-lg hover:bg-slate-200 transition-colors font-medium cursor-pointer">OK</button>
                                </div>
                            </div>
                        </div>

                        <div v-else :key="'setup'" class="flex-1 flex flex-col justify-between py-6">
                            <div class="space-y-6">
                                <div class="flex flex-col items-center">
                                    <div class="mb-4">
                                        <Logo height="h-6" type="swirl"></Logo>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-800 tracking-tight">Simulator</h3>
                                    <p class="text-[10px] text-slate-500 font-medium uppercase tracking-widest text-center px-4">
                                        {{ app.name }} <span class="opacity-50">•</span> {{ version.number }}
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Profiles</label>
                                                <button
                                                    v-if="profiles.length < 4"
                                                    @click="addProfile"
                                                    class="text-indigo-600 hover:text-indigo-700 transition-colors cursor-pointer"
                                                >
                                                    <PlusCircle size="12" />
                                                </button>
                                            </div>

                                            <button
                                                @click="editingProfiles = !editingProfiles"
                                                :class="[editingProfiles ? 'text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100' : 'text-slate-400 hover:text-indigo-600']"
                                                class="flex items-center gap-1 transition-all cursor-pointer"
                                            >
                                                <span v-if="editingProfiles" class="text-[9px] font-bold uppercase tracking-tighter">Done</span>
                                                <Check v-if="editingProfiles" size="12" />
                                                <Edit3 v-else size="12" />
                                            </button>
                                        </div>

                                        <div v-if="!editingProfiles" class="flex gap-2 mb-4 overflow-x-auto pb-1 no-scrollbar">
                                            <button
                                                v-for="(profile, idx) in profiles"
                                                :key="idx"
                                                @click="selectProfile(idx)"
                                                :class="[selectedProfileIndex === idx ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']"
                                                class="text-[9px] px-2.5 py-1 rounded-md font-bold transition-all whitespace-nowrap uppercase tracking-wider cursor-pointer"
                                            >
                                                {{ profile.label }}
                                            </button>
                                        </div>

                                        <div v-else class="w-full space-y-2 mb-4 animate-in slide-in-from-top-2 duration-200 max-h-[120px] overflow-y-auto no-scrollbar p-2 bg-slate-50/50 rounded-lg border border-slate-100">
                                            <div v-for="(profile, idx) in profiles" :key="idx" class="flex items-center gap-1.5 relative group pr-4">
                                                <input v-model="profile.label" class="flex-1 min-w-0 bg-white border border-slate-200 rounded px-2 py-1 text-[10px] font-bold outline-none focus:border-indigo-300" placeholder="Label">
                                                <input v-model="profile.number" class="flex-1 min-w-0 bg-white border border-slate-200 rounded px-2 py-1 text-[10px] font-mono outline-none focus:border-indigo-300" placeholder="Number">
                                                <button v-if="profiles.length > 1" @click="removeProfile(idx)" class="absolute right-0 shrink-0 opacity-0 group-hover:opacity-100 text-rose-400 hover:text-rose-600 cursor-pointer">
                                                    <XCircle size="10" />
                                                </button>
                                            </div>
                                            <button @click="editingProfiles = false" class="w-full py-1.5 mt-1 bg-white border border-slate-200 text-slate-600 rounded-md text-[9px] font-bold uppercase hover:bg-slate-100 transition-colors shadow-sm">
                                                Finish Editing
                                            </button>
                                        </div>

                                        <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100 focus-within:bg-white focus-within:border-indigo-400 transition-all">
                                            <span class="text-slate-400"><Phone size="14" /></span>
                                            <input v-model="activePhone" type="text" class="w-full bg-transparent text-sm font-mono text-slate-700 outline-none" placeholder="26771234567">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="startSession"
                                :disabled="!activePhone"
                                class="w-full py-4 bg-indigo-600 text-white rounded-2xl shadow-xl hover:bg-indigo-600 active:scale-95 transition-all flex items-center justify-center gap-3 text-sm font-bold cursor-pointer disabled:opacity-50 disabled:grayscale"
                            >
                                <Zap size="16" fill="currentColor" />
                                Start Session
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <div v-else class="w-full max-w-[320px] h-[600px] flex flex-col animate-in fade-in duration-300">
            <div class="flex items-center justify-between mb-4 px-2">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active History</h4>
                <button v-if="history.length" @click="history = []" class="text-[9px] text-indigo-600 font-bold hover:underline">CLEAR</button>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                <div v-if="!history.length" class="h-full flex flex-col items-center justify-center text-center opacity-40">
                    <Activity size="32" class="mb-2 text-slate-300" />
                    <p class="text-xs text-slate-500 font-medium">No activity recorded yet</p>
                </div>

                <TransitionGroup name="list">
                    <div
                        v-for="(log, idx) in history"
                        :key="log.timestamp.getTime()"
                        class="relative group bg-white border border-slate-200 rounded-xl shadow-sm mb-3 hover:border-indigo-300 hover:shadow-md transition-all duration-200 overflow-hidden"
                    >
                        <div class="p-3">
                            <div class="flex justify-between items-start mb-2">
                                <span v-if="idx === 0 || log.type === 'stop'" class="text-[9px] font-black px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">
                                    {{ idx === 0 ? 'Start' : 'Stop' }}
                                </span>
                                <span class="text-[9px] text-slate-400 font-medium ml-auto">{{ formatLogTime(log.timestamp) }}</span>
                            </div>

                            <p class="text-[11px] text-slate-600 leading-relaxed whitespace-pre-wrap">{{ log.response }}</p>
                            <p v-if="log.input" class="text-[11px] text-indigo-600 mt-2 font-bold font-mono">
                                <span class="text-slate-400 mr-2">↳</span>
                                <span>{{ log.input }}</span>
                            </p>
                        </div>

                        <vue-slide-up-down :active="expandedLogIndex === idx" :duration="300">
                            <div class="bg-slate-50 border-t border-slate-100 p-2 relative">

                                <button
                                    @click="expandedLogIndex = null"
                                    class="absolute top-1 right-1 p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-full transition-colors cursor-pointer z-10"
                                    title="Close Editor"
                                >
                                    <XCircle size="14" />
                                </button>

                                <div class="grid grid-cols-2 gap-2 pr-6">
                                    <template v-for="feat in getStepFeatures(log.stepId)" :key="feat.id">
                                        <button
                                            @click="openFeatureEditor(log.stepId, feat)"
                                            class="flex items-center gap-2 p-2 bg-white border border-slate-200 rounded-lg hover:border-indigo-400 hover:shadow-sm hover:text-indigo-600 text-slate-600 transition-all text-left cursor-pointer"
                                        >
                                            <component :is="getFeatureIcon(feat.type)" size="12" />
                                            <div class="flex flex-col">
                                                <span class="text-[9px] font-bold uppercase tracking-wide">{{ getFeatureLabel(feat.type) }}</span>
                                                <span class="text-[8px] opacity-60 truncate max-w-[80px]">Edit Settings</span>
                                            </div>
                                        </button>
                                    </template>

                                    <button
                                        v-if="isDecisionPoint(log.stepId)"
                                        @click="openDecisionEditor(log.stepId)"
                                        class="flex items-center gap-2 p-2 bg-amber-50 border border-amber-200 rounded-lg hover:border-amber-400 hover:shadow-sm hover:text-amber-700 text-amber-600 transition-all text-left cursor-pointer col-span-2"
                                    >
                                        <GitBranch size="12" />
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold uppercase tracking-wide">Logic Gate</span>
                                            <span class="text-[8px] opacity-60">Edit Rules</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </vue-slide-up-down>

                        <div v-if="log.stepId && expandedLogIndex !== idx" class="absolute inset-0 bg-white/95 rounded-xl backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center gap-2">
                            <button
                                @click="locateStep(log.stepId)"
                                class="flex flex-col items-center justify-center gap-1 w-16 h-16 rounded-lg hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 transition-colors cursor-pointer"
                            >
                            <MapPin size="14" />
                                <span class="text-[9px] font-bold uppercase tracking-wider">Locate</span>
                            </button>

                            <div class="w-px h-8 bg-slate-200"></div>

                            <button
                                @click="toggleEditMenu(idx, log.stepId)"
                                class="flex flex-col items-center justify-center gap-1 w-16 h-16 rounded-lg hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 transition-colors cursor-pointer"
                            >
                            <Edit3 size="14" />
                                <span class="text-[9px] font-bold uppercase tracking-wider">Edit</span>
                            </button>
                        </div>

                    </div>
                </TransitionGroup>
            </div>
        </div>

        <Transition name="fade">
            <div v-if="showEndHint" class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 bg-slate-800/90 backdrop-blur-md text-white text-[9px] font-black px-4 py-2 rounded-full shadow-2xl flex items-center gap-2 border border-slate-700 whitespace-nowrap tracking-widest">
                <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                SESSION ENDED
            </div>
        </Transition>

    </div>
</template>

<script>
import axios from 'axios';
import Logo from '@Partials/Logo.vue';
import VueSlideUpDown from 'vue-slide-up-down';
import {
    Smartphone, Activity, Phone, Zap, Edit3, Check, PlusCircle,
    XCircle, RotateCcw, Square, MapPin, FileText, List, Terminal, GitBranch, Cpu
} from 'lucide-vue-next';

export default {
    name: 'SimulatorPhone',
    components: {
        Smartphone, Activity, Phone, Zap, Edit3, Check, Logo,
        PlusCircle, XCircle, RotateCcw, Square, MapPin,
        FileText, List, Terminal, GitBranch, Cpu,
        VueSlideUpDown
    },
    inject: ['appState', 'versionState'],
    props: {
        versionId: { type: String, required: true },
        phoneNumber: { type: String, default: '26771234567' }
    },
    data() {
        return {
            view: 'phone',
            loading: false,
            editingProfiles: false,
            selectedProfileIndex: 0,
            activePhone: '',
            sessionId: null,
            currentMessage: '',
            currentMessageId: 0,
            userInput: '',
            awaitingInput: false,
            showEndHint: false,
            history: [],
            expandedLogIndex: null, // Tracks which log card is open for editing
            profiles: [
                { label: 'Seller', number: '26771234567' },
                { label: 'Buyer', number: '26772999999' }
            ]
        };
    },
    computed: {
        app() { return this.appState.app; },
        version() { return this.versionState.version; },
        activeProfileLabel() {
            return this.profiles[this.selectedProfileIndex]?.label || 'USSD Message';
        }
    },
    watch: {
        activePhone(newVal) {
            if (this.profiles[this.selectedProfileIndex]) {
                this.profiles[this.selectedProfileIndex].number = newVal;
            }
            this.saveToStorage();
        },
        profiles: {
            handler() { this.saveToStorage(); },
            deep: true
        },
        currentMessageId() {
            this.focusInput();
        }
    },
    methods: {
        locateStep(stepId) {
            if (!stepId || !this.versionState.vueFlowInstance) return;

            this.versionState.vueFlowInstance.fitView({
                nodes: [stepId],
                duration: 800,
                padding: 0.5
            });
            this.versionState.setCurrentStepId(stepId);
        },
        toggleEditMenu(index, stepId) {
            if (this.expandedLogIndex === index) {
                this.expandedLogIndex = null;
            } else {
                this.expandedLogIndex = index;
                // Auto-locate the step when opening the edit menu
                if (stepId) {
                    this.locateStep(stepId);
                }
            }
        },
        getStepFeatures(stepId) {
            const step = this.versionState.builder.steps[stepId];
            if (!step || !step.feature_ids) return [];
            return step.feature_ids
                .map(fid => ({ ...this.versionState.builder.features[fid], id: fid }))
                .filter(f => f.type); // Ensure validity
        },
        isDecisionPoint(stepId) {
            const step = this.versionState.builder.steps[stepId];
            return step && step.type === 'decision_point';
        },
        getFeatureIcon(type) {
            const map = {
                'basic content': 'FileText',
                'code content': 'Cpu',
                'select': 'List',
                'input': 'Terminal'
            };
            return map[type] || 'Edit3';
        },
        getFeatureLabel(type) {
             const map = {
                'basic content': 'Message',
                'code content': 'Code',
                'select': 'Menu',
                'input': 'Input'
            };
            return map[type] || 'Feature';
        },
        openFeatureEditor(stepId, feature) {
            this.versionState.setCurrentStepId(stepId);
            this.versionState.setCurrentFeatureId(feature.id);

            // Directly trigger the specific modal
            if (feature.type === 'basic content') {
                this.versionState.basicContentEditorModal?.showModal();
            } else if (feature.type === 'code content') {
                this.versionState.codeContentEditorModal?.showModal();
            } else if (feature.type === 'select') {
                this.versionState.selectFeatureModal?.showModal();
            } else if (feature.type === 'input') {
                this.versionState.inputFeatureModal?.showModal();
            } else {
                // Fallback
                this.versionState.featuresModal?.showModal();
            }
        },
        openDecisionEditor(stepId) {
            this.versionState.setCurrentStepId(stepId);
            this.versionState.decisionPointModal?.showModal();
        },
        focusInput() {
            if (this.awaitingInput) {
                this.$nextTick(() => {
                    this.$refs.ussdInput?.focus();
                    setTimeout(() => {
                        this.$refs.ussdInput?.focus();
                    }, 50);
                });
            }
        },
        triggerEndHint() {
            this.showEndHint = true;
            setTimeout(() => {
                this.showEndHint = false;
            }, 3000);
        },
        selectProfile(index) {
            this.selectedProfileIndex = index;
            this.activePhone = this.profiles[index].number;
            localStorage.setItem('ussd_sim_selected_index', index);
        },
        addProfile() {
            if (this.profiles.length < 4) {
                this.profiles.push({ label: 'New User', number: '26770000000' });
                this.saveToStorage();
            }
        },
        removeProfile(index) {
            if (this.profiles.length > 1) {
                this.profiles.splice(index, 1);
                if (this.selectedProfileIndex >= this.profiles.length) {
                    this.selectProfile(0);
                }
                this.saveToStorage();
            }
        },
        saveToStorage() {
            localStorage.setItem('ussd_sim_profiles', JSON.stringify(this.profiles));
            localStorage.setItem('ussd_sim_active_phone', this.activePhone);
        },
        loadPersistedData() {
            const savedProfiles = localStorage.getItem('ussd_sim_profiles');
            if (savedProfiles) this.profiles = JSON.parse(savedProfiles);

            const savedIndex = localStorage.getItem('ussd_sim_selected_index');
            if (savedIndex !== null) {
                const index = parseInt(savedIndex);
                if (index < this.profiles.length) this.selectedProfileIndex = index;
            }

            this.activePhone = this.profiles[this.selectedProfileIndex]?.number || this.phoneNumber;
        },
        formatLogTime(date) {
            return new Intl.DateTimeFormat('en', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(new Date(date));
        },
        async startSession() {
            this.sessionId = null;
            this.history = [];
            this.userInput = '';
            // Clear previous path visuals
            this.versionState.clearSimulatorPath();
            await this.sendRequest('');
        },
        async sendResponse() {
            if (this.loading) return;
            await this.sendRequest(this.userInput);
            this.userInput = '';
        },
        async sendRequest(text) {
            this.loading = true;

            // Capture the ID of the step the user is looking at BEFORE we send the request.
            // This is crucial for mapping the "Reply" back to the correct node.
            const sourceStepId = (this.sessionId && this.versionState.simulatorPathNodes.length)
                ? this.versionState.simulatorPathNodes[this.versionState.simulatorPathNodes.length - 1]
                : null;

            const payload = {
                version_id: this.versionId,
                phone_number: this.activePhone,
                text: text
            };

            if (this.sessionId) payload.session_id = this.sessionId;

            try {
                const response = await axios.post('/api/ussd/simulate', payload);
                const data = response.data;

                if (data.type === 'stop') {
                    this.triggerEndHint();
                    setTimeout(() => { this.endSession(); }, 4000);
                }

                this.sessionId = data.session_id;
                this.currentMessage = data.message;
                this.currentMessageId++;
                this.awaitingInput = data.type === 'continue';

                // Update the visual path in the canvas
                // We pass 'text' as the logic label and 'sourceStepId' as the origin
                this.versionState.addToSimulatorPath(
                    data.current_step_id,
                    data.type === 'stop',
                    data.message,
                    text,         // The logic/reply label
                    sourceStepId  // The node that the reply belongs to
                );

                // --- RE-ARRANGE CANVAS NODES ---
                // We wait 400ms to allow the 'slide-up' transition (approx 300ms)
                // to complete so the auto-layout can read the correct expanded height.
                setTimeout(() => {
                    this.versionState.autoLayoutNodes({
                        zoom: false,      // Prevent jarring camera movements
                        autoLayout: true  // Perform the smart stacking logic
                    });
                }, 400);

                // HISTORY FIX: Attach user input to the PREVIOUS card (the screen where it was typed)
                if (this.history.length > 0) {
                     this.history[this.history.length - 1].input = text;
                }

                const logEntry = {
                    input: null, // New card starts with no input
                    response: data.message,
                    type: data.type,
                    timestamp: new Date(),
                    stepId: data.current_step_id
                };

                this.history.push(logEntry);
                this.$emit('interaction', logEntry);

                this.focusInput();
            } catch (error) {
                this.currentMessage = "System Error: " + (error.response?.data?.message || error.message);
                this.currentMessageId++;
                this.awaitingInput = false;
            } finally {
                this.loading = false;
            }
        },
        endSession() {
            if (this.sessionId && !this.showEndHint) {
                this.triggerEndHint();
            }
            this.sessionId = null;
            this.currentMessage = '';
            this.userInput = '';
            this.awaitingInput = false;
            // Note: We don't clear the path here so users can see the final state
        },
        resetSession() {
            this.endSession();
            this.$nextTick(() => { this.startSession(); });
        }
    },
    mounted() {
        this.loadPersistedData();
    }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

.mockup-phone { background-color: #1e293b; border: 4px solid #334155; border-radius: 40px; display: inline-block; overflow: hidden; position: relative; user-select: none; }
.mockup-phone-camera { background: #0f172a; border-radius: 0 0 12px 12px; width: 40%; height: 24px; margin: 0 auto; position: absolute; top: 0; left: 30%; z-index: 20; }
.mockup-phone-display { border-radius: 32px; overflow: hidden; height: 100%; }
.mockup-phone-input { background: transparent; border: none; border-bottom: 2px solid #4f46e5 !important; border-radius: 0; box-shadow: none !important; margin: 12px 0 0 0; padding: 8px 0; width: 100%; outline: none; transition: border-color 0.2s; }
.mockup-phone-input:focus { border-bottom-color: #4338ca !important; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Transitions */
.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.2s ease-out; }
.slide-fade-enter-from { opacity: 0; transform: translateY(8px); }
.slide-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from { opacity: 0; transform: translateX(20px); }
.list-leave-to { opacity: 0; transform: scale(0.95); }
</style>
