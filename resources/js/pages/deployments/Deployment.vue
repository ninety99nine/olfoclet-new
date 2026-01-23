<!-- resources/js/pages/deployments/Deployment.vue -->
<template>
  <div class="min-h-screen bg-slate-50 pb-12">

    <!-- Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-6 py-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <button
              @click="$router.back()"
              class="p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>

            <div>
              <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-900">
                  {{ deployment.network }}
                </h1>
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                  :class="deployment.active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                >
                  {{ deployment.active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                <span>{{ deployment.country_name || deployment.country }}</span>
                <span class="text-slate-400">•</span>
                <span>Last updated {{ formattedRelativeDate(deployment.updated_at) }}</span>
              </p>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="flex items-center gap-3 flex-wrap">
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                class="sr-only peer"
                :checked="deployment.active"
                @change="toggleActive"
              />
              <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-300
                          peer-checked:after:translate-x-full peer-checked:after:border-white
                          after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                          after:bg-white after:border-gray-300 after:border after:rounded-full
                          after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
              <span class="ml-3 text-sm font-medium text-slate-900">
                {{ deployment.active ? 'Enabled' : 'Disabled' }}
              </span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Main Content -->
        <div class="lg:col-span-8 space-y-8">

          <!-- Deployment Information - Editable -->
          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
              <h2 class="text-lg font-semibold text-slate-900">Deployment Information</h2>
              <button
                v-if="isEditingInfo"
                @click="saveInfoChanges"
                class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg transition-colors"
              >
                Save
              </button>
              <button
                v-else
                @click="isEditingInfo = true"
                class="px-4 py-1.5 bg-white border border-slate-300 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition-colors"
              >
                Edit
              </button>
            </div>

            <div class="p-6">
              <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                  <dt class="text-sm font-medium text-slate-500 mb-1">Network</dt>
                  <dd v-if="!isEditingInfo" class="text-slate-900 font-medium">{{ deployment.network }}</dd>
                  <select
                    v-else
                    v-model="deployment.network"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option v-for="net in availableNetworks" :key="net" :value="net">
                      {{ net }}
                    </option>
                  </select>
                </div>

                <div>
                  <dt class="text-sm font-medium text-slate-500 mb-1">Country</dt>
                  <dd v-if="!isEditingInfo" class="flex items-center gap-2">
                    <img
                      :src="`/svgs/country-flags/${deployment.country?.toLowerCase() || 'bw'}.svg`"
                      alt=""
                      class="w-6 h-6 rounded-full object-cover border border-slate-200"
                    />
                    <span class="font-medium">{{ deployment.country_name || deployment.country }}</span>
                  </dd>
                  <select
                    v-else
                    v-model="deployment.country"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option v-for="c in availableCountries" :key="c.code" :value="c.code">
                      {{ c.name }}
                    </option>
                  </select>
                </div>

                <div>
                  <dt class="text-sm font-medium text-slate-500">Status</dt>
                  <dd>
                    <span
                      :class="deployment.active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                      class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                    >
                      {{ deployment.active ? 'Active' : 'Inactive' }}
                    </span>
                  </dd>
                </div>

                <div class="col-span-1 sm:col-span-2 lg:col-span-3">
                  <dt class="text-sm font-medium text-slate-500">Callback URL</dt>
                  <dd class="mt-1 font-mono text-sm bg-slate-50 p-3 rounded border border-slate-200 break-all">
                    {{ deployment.callback_url }}
                  </dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-slate-500 mb-1">Max Message Length</dt>
                  <dd v-if="!isEditingInfo">
                    <span class="inline-flex px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded-full">
                      {{ deployment.max_message_length }} characters
                    </span>
                  </dd>
                  <select
                    v-else
                    v-model="deployment.max_message_length"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="160">160 characters</option>
                    <option value="182">182 characters</option>
                  </select>
                </div>

                <div>
                  <dt class="text-sm font-medium text-slate-500">Request Format</dt>
                  <dd>
                    <span class="inline-flex px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded-full">
                      {{ deployment.request_format }}
                    </span>
                  </dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-slate-500">Response Format</dt>
                  <dd>
                    <span class="inline-flex px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded-full">
                      {{ deployment.response_format }}
                    </span>
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Transformation Section -->
          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
              <h2 class="text-lg font-semibold text-slate-900">Transformation</h2>
              <button
                @click="saveTransformations"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors"
              >
                Save Changes
              </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-slate-200">
              <nav class="flex -mb-px">
                <button
                  @click="activeTab = 'incoming'"
                  :class="activeTab === 'incoming' ? activeTabClass : inactiveTabClass"
                >
                  Incoming
                </button>
                <button
                  @click="activeTab = 'outgoing'"
                  :class="activeTab === 'outgoing' ? activeTabClass : inactiveTabClass"
                >
                  Outgoing
                </button>
              </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">

              <!-- Code - always editable -->
              <div class="space-y-10">
                <!-- Incoming -->
                <div v-if="activeTab === 'incoming'">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium flex items-center gap-3">
                      <span class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">IN</span>
                      Mobile Network → Platform
                    </h3>

                      <select v-model="incomingLang" class="px-3 py-1.5 border border-slate-300 rounded-lg">
                        <option value="javascript">JavaScript</option>
                        <option value="python">Python</option>
                        <option value="php">PHP</option>
                      </select>
                  </div>

                    <div class="border border-slate-300 rounded-lg overflow-hidden shadow-sm h-80 mb-4">
                        <CodeEditor
                        v-model:value="incoming"
                        language="javascript"
                        theme="vs-dark"
                        :options="editorOptions"
                        />
                    </div>
              <!-- Sample -->
                  <div class="bg-slate-50 rounded-lg p-5">
                    <p class="text-sm font-medium text-slate-700 mb-3 flex items-center gap-2">
                      The platform requires the mobile network data to be transformed as follows:
                    </p>
                    <pre class="bg-slate-900 text-emerald-300 font-mono text-sm p-4 rounded-lg overflow-x-auto">
{
  "text": "",
  "sessionId": "123",
  "serviceCode": "*123#",
  "phoneNumber": "+26772882239",
  "mode": "start"    // or "continue"
}
                    </pre>
                  </div>
                </div>

                <!-- Outgoing -->
                <div v-if="activeTab === 'outgoing'">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium flex items-center gap-3">
                      <span class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs">OUT</span>
                      Platform → Mobile Network
                    </h3>
                    <select v-model="outgoingLang" class="px-3 py-1.5 border border-slate-300 rounded-lg">
                      <option value="javascript">JavaScript</option>
                      <option value="python">Python</option>
                      <option value="php">PHP</option>
                    </select>
                  </div>

                    <div class="border border-slate-300 rounded-lg overflow-hidden shadow-sm h-80 mb-4">
                        <CodeEditor
                        v-model:value="outgoing"
                        language="javascript"
                        theme="vs-dark"
                        :options="editorOptions"
                        />
                    </div>
                  <div class="bg-slate-50 rounded-lg p-5">
                    <p class="text-sm font-medium text-slate-700 mb-3 flex items-center gap-2">
                      The platform provides the output follows:
                    </p>
                    <pre class="bg-slate-900 text-emerald-300 font-mono text-sm p-4 rounded-lg overflow-x-auto">
{
  "message": "Welcome to BankABC"
}
                    </pre>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance & Usage (unchanged) -->
          <!-- ... -->

        </div>

        <!-- Sidebar (Quick Stats, History, Danger Zone) -->
        <!-- ... keep as is ... -->

      </div>
    </div>

    <!-- Delete Modal -->
    <!-- ... keep as is ... -->

  </div>
</template>

<script>
import { CodeEditor } from 'monaco-editor-vue3';
import { formattedRelativeDate } from '@Utils/dateUtils.js'

export default {
  name: 'DeploymentView',
    components: {CodeEditor},
  data() {
    return {
      deployment: {
        id: 42,
        network: "Orange Botswana",
        country: "BW",
        country_name: "Botswana",
        active: true,
        callback_url: "https://ussd.platform.example.com/callback/orange-bw",
        max_message_length: 160,
        created_at: "2025-11-12T08:45:22Z",
        updated_at: "2026-01-18T14:30:11Z",
        total_sessions: 1842,
        success_rate: 96.8,
        avg_response_time: 1.24,
        active_sessions: 7,
        errors_24h: 3,
        last_error_time: "2026-01-19T09:12:45Z",
        history: []
      },

      editorOptions: {
        fontSize: 14,
        minimap: { enabled: false },
        automaticLayout: true
    },

    incoming: `// Example incoming transformation
return {
  text: incoming.text || incoming.message || "",
  sessionId: incoming.sessionId || incoming.session_id || incoming.transactionId,
  serviceCode: incoming.serviceCode || incoming.shortCode || "*123#",
  phoneNumber: incoming.phoneNumber || incoming.msisdn || incoming.from,
  mode: incoming.type === "1" || incoming.newSession ? "start" : "continue"
};`,
        response_transformation: `// Example outgoing transformation
return {
  message: input.message || "Welcome! Please select an option",
  continue: input.continue !== false
};`,

    outgoing: `// Example outgoing transformation
return {
  text: input.text || input.message || "",
  sessionId: input.sessionId || input.session_id || input.transactionId,
  serviceCode: input.serviceCode || input.shortCode || "*123#",
  phoneNumber: input.phoneNumber || input.msisdn || input.from,
  mode: input.type === "1" || input.newSession ? "start" : "continue"
};`,
        response_transformation: `// Example outgoing transformation
return {
  message: input.message || "Welcome! Please select an option",
  continue: input.continue !== false
};`,

      isEditingInfo: false,

      activeTab: 'incoming',           // default to code since it's always editable
      incomingLang: 'javascript',
      outgoingLang: 'javascript',

      incomingEditor: null,
      outgoingEditor: null,

      availableNetworks: [
        'Orange Botswana',
        'Mascom',
        'BTC Mobile',
        'Orange Lesotho',
        'Vodacom Lesotho'
      ],

      availableCountries: [
        { code: 'BW', name: 'Botswana' },
        { code: 'LS', name: 'Lesotho' },
        { code: 'NA', name: 'Namibia' },
        { code: 'ZA', name: 'South Africa' },
        { code: 'ZM', name: 'Zambia' }
      ]
    }
  },

  computed: {
    activeTabClass() {
      return 'px-6 py-4 text-sm font-medium border-b-2 border-indigo-600 text-indigo-700'
    },
    inactiveTabClass() {
      return 'px-6 py-4 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
    }
  },

  watch: {
    incomingLang(newLang) {
      if (this.incomingEditor) {
        monaco.editor.setModelLanguage(this.incomingEditor.getModel(), newLang)
      }
    },
    outgoingLang(newLang) {
      if (this.outgoingEditor) {
        monaco.editor.setModelLanguage(this.outgoingEditor.getModel(), newLang)
      }
    }
  },

  mounted() {
    this.$nextTick(() => {
      this.initEditors()
    })
  },

  beforeDestroy() {
    if (this.incomingEditor) this.incomingEditor.dispose()
    if (this.outgoingEditor) this.outgoingEditor.dispose()
  },

  methods: {
    initEditors() {
      if (!this.$refs.incomingEditorRef || !this.$refs.outgoingEditorRef) return

      this.incomingEditor = monaco.editor.create(this.$refs.incomingEditorRef, {
        value: this.deployment.request_transformation || '// Write your incoming transformation...',
        language: this.incomingLang,
        theme: 'vs-dark',
        automaticLayout: true,
        fontSize: 14,
        minimap: { enabled: false },
        wordWrap: 'on',
        lineNumbers: 'on',
        scrollBeyondLastLine: false
      })

      this.outgoingEditor = monaco.editor.create(this.$refs.outgoingEditorRef, {
        value: this.deployment.response_transformation || '// Write your outgoing transformation...',
        language: this.outgoingLang,
        theme: 'vs-dark',
        automaticLayout: true,
        fontSize: 14,
        minimap: { enabled: false },
        wordWrap: 'on',
        lineNumbers: 'on',
        scrollBeyondLastLine: false
      })
    },

    saveTransformations() {
      if (this.incomingEditor && this.outgoingEditor) {
        this.deployment.request_transformation = this.incomingEditor.getValue()
        this.deployment.response_transformation = this.outgoingEditor.getValue()
        this.deployment.updated_at = new Date().toISOString()
        alert('Transformation changes saved! (local only)')
      }
    },

    saveInfoChanges() {
      this.isEditingInfo = false
      this.deployment.updated_at = new Date().toISOString()
      alert('Basic information updated! (local only)')
    },

    toggleActive() {
      this.deployment.active = !this.deployment.active
      alert(`Deployment is now ${this.deployment.active ? 'Active' : 'Inactive'} (local demo)`)
    },

    confirmDelete() {
      if (confirm('Are you sure you want to delete this deployment?')) {
        alert('Deployment deleted (demo mode)')
        this.$router.push('/deployments')
      }
    },

    formattedRelativeDate(date) {
      return formattedRelativeDate(date)
    }
  }
}
</script>
