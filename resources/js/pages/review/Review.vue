<template>
  <div
    :key="sessionActive"
    :class="[
        'min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 pb-8',
        'transition-opacity opacity-100 duration-1000 lg:grow starting:opacity-0'
    ]">

    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-20 shadow-sm">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <button @click="$router.back()" class="p-2 text-slate-600 hover:text-indigo-600 rounded-lg hover:bg-white/80 transition-all">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>

            <div>
              <h1 class="text-xl font-bold text-slate-900">QA Simulator</h1>
              <p class="text-xs text-slate-600">Test • Debug • Report</p>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 bg-white/80 px-3 py-1.5 rounded-lg border border-slate-200/70">
              <span class="text-xs text-slate-600 font-medium">Device:</span>
              <select v-model="deviceType" class="bg-transparent border-none text-xs font-medium focus:ring-0 text-slate-800">
                <option value="feature">Feature</option>
                <option value="smart">Smart</option>
              </select>
            </div>

            <button
                v-if="sessionActive"
                @click="exportSession"
                    class="text-xs px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Export
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Simulator + Flow -->
        <div :class="[sessionActive ? 'lg:col-span-8' : 'lg:col-span-12', 'space-y-6']">

          <!-- Simulator -->
          <div>

            <div class="flex justify-center">
                <div class="mockup-phone">
                    <div class="mockup-phone-camera"></div>
                    <div class="mockup-phone-display bg-slate-50">
                        <div class="h-137.5 w-[275px] pt-40 p-4">

                            <div
                                v-if="sessionActive"
                                class="bg-white p-4 rounded-xl border border-slate-200">
                                <p class="text-sm text-slate-800 whitespace-pre-line">Welcome to Mobile Banking USSD
    * Dial *123# to continue *

    Enter your PIN:</p>

<input type="text" class="mockup-phone-input text-sm"/>
<div class="w-3/4 m-auto text-sm flex justify-between mt-4">
    <button class="text-gray-600 hover:text-rose-500 active:text-rose-600 cursor-pointer hover:scale-110 transition-all duration-300">Cancel</button>
    <span class="text-gray-600">|</span>
    <button class="text-gray-600 hover:text-indigo-500 active:text-indigo-600 cursor-pointer hover:scale-110 transition-all duration-300">Send</button></div>
</div>
                            <div
                                v-else
                                class="flex flex-col text-center mb-4">

                                <p class="text-sm font-semibold mb-2">Test • Debug • Report</p>
                                <p class="text-xs text-slate-500 mb-4">Enter mobile number to simulate</p>

                                <Input
                                    type="text"
                                    v-model="msisdn"
                                    class="w-full mb-8"
                                    :showOutline="false"
                                    placeholder="+267 71 234 567" />

                                    <Button
                                        size="md"
                                        type="gradient"
                                        :loading="loading"
                                        loaderType="primary"
                                        :action="startSession"
                                        :disabled="!msisdn || loading"
                                        buttonClass="w-full shadow-lg rounded-full">
                                        <span class="mr-2">Start</span>
                                    </Button>

                            </div>


                        </div>
                    </div>
                </div>
            </div>

          </div>

          <!-- Session Flow with inline actions -->
          <div
            v-if="sessionActive"
            class="bg-white rounded-xl border border-slate-200/70 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
              <h2 class="text-lg font-semibold text-slate-900">Session Flow</h2>
              <div class="text-xs text-slate-600">
                Slowest: <span class="font-medium">{{ maxWaitTime }}s</span>
              </div>
            </div>

            <div class="p-5">
              <div class="relative">
                <div class="absolute left-7 top-1 bottom-1 w-0.5 bg-slate-200"></div>

                <div v-for="(step, index) in sessionFlow" :key="index" class="relative mb-6 last:mb-0 group">
                  <div class="flex items-start gap-5">
                    <div class="flex-shrink-0 z-10 w-12 h-12 rounded-full bg-white border-4 border-slate-200 flex items-center justify-center font-bold text-xl text-slate-700 shadow-sm">
                      {{ index + 1 }}
                    </div>

                    <div class="flex-1 bg-slate-50/50 rounded-xl p-4 border border-slate-200/50 relative">
                      <div class="flex justify-between items-start gap-3 mb-3">
                        <p class="font-medium text-slate-900 text-base">{{ step.menu_title || 'Menu Step' }}</p>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                          <button @click="captureScreenshot(index)"
                                  class="text-xs p-1.5 bg-white hover:bg-gray-100 rounded-md border border-slate-300 shadow-sm transition-colors"
                                  title="Take screenshot">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                          </button>
                          <button @click="flagThisStep(index)"
                                  class="text-xs p-1.5 bg-white hover:bg-amber-50 rounded-md border border-slate-300 shadow-sm transition-colors text-amber-600 hover:text-amber-700"
                                  title="Flag this step">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                          </button>
                        </div>
                      </div>

                      <div
                        :id="`step-screen-${index}`"
                        class="bg-white border border-slate-200 rounded-lg p-3.5 font-mono text-xs whitespace-pre-line">
                        {{ step.response || 'No response recorded' }}
                      </div>

                      <div v-if="step.input" class="mt-3 flex items-center gap-2">
                        <span class="text-slate-500 text-xs">Replied:</span>
                        <p class="bg-slate-100 px-3 py-1 rounded text-xs">
                          {{ step.input }}
                        </p>
                      </div>

                      <div v-if="index === sessionFlow.length - 1 && isSystemEndedSession()"
                           class="mt-4 text-xs text-amber-700 bg-amber-50/50 p-2 rounded border border-amber-200/50">
                        Session ended by system ({{ systemEndReason() || 'timeout' }})
                      </div>
                    </div>
                  </div>
                  {{step}}

<!-- API Metrics & Insights -->
<div v-if="step.apiMetrics" class="mt-3 pt-3 border-t border-slate-200 text-xs">
  <div class="grid grid-cols-2 gap-2">
    <div>
      <span class="text-slate-500">API</span>
      <p class="font-medium truncate">{{ step.apiMetrics.url }}</p>
    </div>
    <div>
      <span class="text-slate-500">Status</span>
      <p :class="step.apiMetrics.status >= 400 ? 'text-rose-600 font-bold' : 'text-emerald-600 font-bold'">
        {{ step.apiMetrics.status }}
      </p>
    </div>
    <div>
      <span class="text-slate-500">Duration</span>
      <p :class="step.apiMetrics.duration > 1500 ? 'text-amber-600' : 'text-emerald-600'">
        {{ step.apiMetrics.duration }} ms
      </p>
    </div>
    <div>
      <span class="text-slate-500">Time</span>
      <p>{{ new Date(step.apiMetrics.timestamp).toLocaleTimeString() }}</p>
    </div>
  </div>

  <div v-if="step.apiMetrics.error" class="mt-2 p-2 bg-rose-50 text-rose-700 rounded">
    Error: {{ step.apiMetrics.error }}
  </div>

  <div v-if="step.apiInsights" class="mt-2 p-2 bg-blue-50 text-blue-700 rounded italic">
    {{ step.apiInsights }}
  </div>
</div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Sidebar -->
        <div
            v-if="sessionActive"
            class="lg:col-span-4 space-y-4">

          <!-- Start Session -->
          <div class="bg-white rounded-xl border border-slate-200/70 p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Session</h3>
                <dd class="text-2xl font-medium">{{ sessionDuration }}</dd>
            </div>
            <div class="pb-4 mb-4 space-y-4 border-b border-slate-200/70">
              <input
                v-model="msisdn"
                type="tel"
                placeholder="+267 71 234 567"
                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
              />
              <div class="flex gap-3">
                <button
                  @click="endSession"
                  class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm rounded-lg transition-colors"
                >
                  End
                </button>
              </div>
            </div>

            <dl class="space-y-2.5 text-sm">
              <div class="flex justify-between">
                <dt class="text-slate-500">Number</dt>
                <dd class="font-medium text-indigo-700">{{ msisdn }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Device</dt>
                <dd class="font-medium">{{ deviceType === 'feature' ? 'Feature Phone' : 'Smartphone' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Network</dt>
                <dd class="font-medium">{{ simulatedNetwork || 'Orange' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Duration</dt>
                <dd class="font-medium">{{ sessionDuration }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Steps</dt>
                <dd class="font-medium text-indigo-600">{{ sessionFlow.length }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Avg Wait</dt>
                <dd class="font-medium" :class="avgResponseColor">{{ avgResponseTime }}s</dd>
              </div>
              <div class="flex justify-between font-medium">
                <dt class="text-slate-500">Flags</dt>
                <dd class="text-amber-700">{{ sessionReviews?.length || 0 }}</dd>
              </div>
            </dl>
          </div>

          <!-- Flags -->
          <div class="bg-white rounded-xl border border-slate-200/70 p-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4 flex justify-between items-center">
              <span>Flags ({{ sessionReviews?.length ?? 0 }})</span>
              <button @click="showMarkReviewModal = true"
                      class="text-xs px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New
              </button>
            </h3>

            <div v-if="sessionReviews?.length" class="space-y-4 max-h-[400px] overflow-y-auto">
              <div v-for="review in sessionReviews" :key="review.id"
                   class="border rounded-lg p-3.5 text-sm hover:bg-slate-50 transition-colors">
                <div class="flex justify-between items-start mb-2">
                  <p class="font-medium truncate pr-2">{{ review.user }}</p>
                  <span class="text-xs text-slate-500 whitespace-nowrap">
                    {{ formattedRelativeDate(review.timestamp) }}
                  </span>
                </div>

                <div class="flex flex-wrap gap-1.5 mb-2">
                  <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700">
                    {{ review.category }}
                  </span>
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                        :class="{
                          'bg-emerald-100 text-emerald-800': review.priority === 'low',
                          'bg-amber-100 text-amber-800': review.priority === 'medium',
                          'bg-orange-100 text-orange-800': review.priority === 'high',
                          'bg-rose-100 text-rose-800': review.priority === 'critical'
                        }">
                    {{ review.priority }}
                  </span>
                  <span v-if="review.status === 'resolved'" class="px-2 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700">
                    Resolved
                  </span>
                </div>

                <p class="text-slate-700 break-words text-xs">
                  {{ review.comment }}
                </p>

                <div class="mt-3 flex justify-end">
                  <button v-if="review.status === 'open'" @click="resolveReview(review.id)"
                          class="text-xs px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
                    Resolve
                  </button>
                </div>
              </div>
            </div>

            <p v-else class="text-xs text-slate-500 italic text-center py-6">
              No flags yet — Report an issue
            </p>
          </div>

<!-- API Health Summary + Detailed Calls List -->
<div v-if="sessionActive && sessionFlow.some(s => s.apiMetrics)" class="bg-blue-50 rounded-xl overflow-hidden border border-blue-200 shadow-sm">

    <div class="p-4 pb-0">

<h4 class="text-lg font-semibold text-slate-900 mb-4">API Health</h4>

  <!-- Summary Stats -->
  <div class="grid grid-cols-2 gap-4 mb-6">
    <div class="space-y-1">
      <p class="text-slate-500 text-sm">Total Calls</p>
      <p class="font-medium text-base">{{ sessionFlow.filter(s => s.apiMetrics).length }}</p>
    </div>
    <div class="space-y-1">
      <p class="text-slate-500 text-sm">Errors</p>
      <p class="font-medium text-base text-rose-600">
        {{ sessionFlow.filter(s => s.apiMetrics?.status >= 400).length }}
      </p>
    </div>
    <div class="space-y-1">
      <p class="text-slate-500 text-sm">Avg Duration</p>
      <p class="font-medium text-base">
        {{
          Math.round(
            sessionFlow
              .filter(s => s.apiMetrics)
              .reduce((sum, s) => sum + (s.apiMetrics.duration || 0), 0) /
              (sessionFlow.filter(s => s.apiMetrics).length || 1)
          )
        }} ms
      </p>
    </div>
    <div class="space-y-1">
      <p class="text-slate-500 text-sm">Slowest</p>
      <p class="font-medium text-base">
        {{ Math.max(...sessionFlow.filter(s => s.apiMetrics).map(s => s.apiMetrics.duration || 0), 0) }} ms
      </p>
    </div>
  </div>
    </div>

  <!-- Detailed API Calls List -->
<div class="space-y-4 text-sm p-2">
  <div
    v-for="(step, idx) in sessionFlow.filter(s => s.apiMetrics)"
    :key="idx"
    class="bg-white rounded-lg p-4 shadow-sm"
  >
    <!-- Full URL + Method – prominent top line -->
    <div class="flex justify-between gap-3 mb-3">
      <div class="text-xs h-fit font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 whitespace-nowrap">
        {{ step.apiMetrics.method || 'POST' }}
      </div>
      <div class="w-60 text-sm text-slate-500 wrap-break-word">
        {{ step.apiMetrics.fullUrl || step.apiMetrics.url }}
      </div>
    </div>

    <!-- Details grid below -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
      <div>
        <p class="text-slate-500">Status</p>
        <p class="font-medium mt-0.5" :class="{
          'text-emerald-700': step.apiMetrics.status < 400,
          'text-amber-700': step.apiMetrics.status >= 400 && step.apiMetrics.status < 500,
          'text-rose-700 font-bold': step.apiMetrics.status >= 500
        }">
          {{ step.apiMetrics.status }}
        </p>
      </div>

      <div>
        <p class="text-slate-500">Duration</p>
        <p class="font-medium mt-0.5" :class="step.apiMetrics.duration > 1500 ? 'text-amber-700' : 'text-slate-700'">
          {{ step.apiMetrics.duration }} ms
        </p>
      </div>

      <div v-if="step.apiMetrics.timestamp">
        <p class="text-slate-500">Time</p>
        <p class="font-medium mt-0.5">
          {{ new Date(step.apiMetrics.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
        </p>
      </div>
    </div>

    <!-- Error message -->
    <div v-if="step.apiMetrics.error" class="mt-3 text-xs text-rose-700 bg-rose-50 p-2.5 rounded border border-rose-200">
      Error: {{ step.apiMetrics.error }}
    </div>

    <!-- Insight -->
    <div v-if="step.apiInsights" class="mt-3 text-xs italic text-blue-700 bg-blue-50 p-2.5 rounded">
      {{ step.apiInsights }}
    </div>
  </div>

  <!-- No calls message -->
  <p v-if="!sessionFlow.some(s => s.apiMetrics)" class="text-center text-slate-500 italic py-6">
    No API calls recorded yet
  </p>
</div>
</div>

        </div>

      </div>
    </div>

    <!-- Add Flag Modal – now supports step prefill -->
    <div v-if="showMarkReviewModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl p-7 max-w-lg w-full shadow-2xl border border-slate-100">
        <h3 class="text-2xl font-bold mb-5 text-slate-900">
          {{ editingStep !== null ? `Flag Step ${editingStep + 1}` : 'Add Flag' }}
        </h3>

        <form @submit.prevent="addFlag" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Category *</label>
            <select v-model="reviewCategory" required class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm">
              <option value="" disabled>Select category</option>
              <option value="bug">Bug / Technical Error</option>
              <option value="ux">UX / Flow Confusion</option>
              <option value="performance">Performance / Slow Response</option>
              <option value="content">Menu Text / Wording Issue</option>
              <option value="security">Security / Compliance Concern</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Priority *</label>
            <div class="grid grid-cols-4 gap-2">
              <label class="cursor-pointer">
                <input type="radio" v-model="reviewPriority" value="low" class="sr-only" />
                <div :class="reviewPriority === 'low' ? 'bg-indigo-600 text-white' : 'bg-white border-slate-300 hover:bg-slate-50'"
                     class="py-2 px-3 text-center rounded-lg border text-sm transition-colors">
                  Low
                </div>
              </label>
              <!-- ... repeat for medium, high, critical ... -->
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Comment</label>
            <textarea v-model="reviewComment" rows="4"
                      class="w-full border border-slate-300 rounded-xl p-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                      placeholder="Describe the issue..."></textarea>
          </div>

          <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="showMarkReviewModal = false; editingStep = null"
                    class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
              Submit Flag
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
    import Input from '@Partials/Input.vue';
    import Button from '@Partials/Button.vue';
import { formattedRelativeDate } from '@Utils/dateUtils.js'
import html2canvas from 'html2canvas' // npm install html2canvas

export default {
  name: 'Review',
        components: {
            Input, Button
        },
  data() {
    return {
        msisdn: '',
      deviceType: 'feature',
      loading: false,
      sessionActive: false,
      sessionStart: null,
      sessionFlow: [],
      currentScreen: '',
      awaitingInput: false,
      userInput: '',
      showMarkReviewModal: false,
      reviewCategory: '',
      reviewPriority: 'medium',
      reviewComment: '',
      sessionReviews: [],
      editingStep: null, // ← new: which step is being flagged
      screenshots: {}    // stepIndex → dataURL
    }
  },

  computed: {
    simulatedNetwork() {
      return this.deviceType === 'feature' ? 'Orange 2G' : 'Orange 4G'
    },
    sessionDuration() {
      if (!this.sessionStart) return '00:00'
      const diff = Math.floor((new Date() - new Date(this.sessionStart)) / 1000)
      const min = Math.floor(diff / 60).toString().padStart(2, '0')
      const sec = (diff % 60).toString().padStart(2, '0')
      return `${min}:${sec}`
    },

    avgResponseTime() {
      if (!this.sessionFlow.length) return '0.0'
      const times = this.sessionFlow
        .filter(s => s.wait_time)
        .map(s => parseFloat(s.wait_time?.replace('s', '') || '0') || 0)
      return times.length ? (times.reduce((a, b) => a + b, 0) / times.length).toFixed(1) : '0.0'
    },

    maxWaitTime() {
      const times = this.sessionFlow
        .filter(s => s.wait_time)
        .map(s => parseFloat(s.wait_time?.replace('s', '') || '0') || 0)
      return times.length ? Math.max(...times).toFixed(1) : '—'
    },

    longestWaitIndex() {
      let max = 0
      let index = -1
      this.sessionFlow.forEach((step, i) => {
        const val = parseFloat(step.wait_time?.replace('s', '') || '0') || 0
        if (val > max) {
          max = val
          index = i
        }
      })
      return index
    },

    avgResponseColor() {
      const avg = parseFloat(this.avgResponseTime)
      return {
        'text-emerald-600': avg <= 5,
        'text-amber-600': avg > 5 && avg <= 15,
        'text-rose-600': avg > 15
      }
    }
  },

  methods: {
    formattedRelativeDate,

    async captureScreenshot(stepIndex) {
      try {
        const phoneElement = document.querySelector(`#step-screen-${stepIndex}`) // adjust selector if needed
        if (!phoneElement) return alert('Could not find phone screen')

        const canvas = await html2canvas(phoneElement, { scale: 2 })
        const dataUrl = canvas.toDataURL('image/png')

        this.$set(this.screenshots, stepIndex, dataUrl)
        alert('Screenshot captured! (ready for flag or export)')
      } catch (err) {
        console.error(err)
        alert('Failed to capture screenshot')
      }
    },

    flagThisStep(stepIndex) {
      this.editingStep = stepIndex
      // Pre-fill comment with step info
      const step = this.sessionFlow[stepIndex]
      this.reviewComment = `Issue in step ${stepIndex + 1} (${step.menu_title || 'Untitled'}):\n${step.response?.substring(0, 120) || ''}...`
      this.showMarkReviewModal = true
    },
    async exportSession() {
      const data = {
        msisdn: this.msisdn,
        started: this.sessionStart,
        duration: this.sessionDuration,
        steps: this.sessionFlow.length,
        avgWait: this.avgResponseTime,
        flow: this.sessionFlow,
        flags: this.sessionReviews,
        screenshots: this.screenshots
      }

      const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `qa-session-${this.msisdn || 'unknown'}-${new Date().toISOString().split('T')[0]}.json`
      link.click()
      URL.revokeObjectURL(url)

      alert('Session exported! (screenshots are included as base64)')
    },
    isLongestWait(step, index) {
      return index === this.longestWaitIndex && this.longestWaitIndex !== -1
    },

    waitTimeClass(waitTimeStr) {
      if (!waitTimeStr) return ''
      const seconds = parseFloat(waitTimeStr.replace('s', '')) || 0
      if (seconds <= 4) return 'bg-emerald-100 text-emerald-800'
      if (seconds <= 10) return 'bg-amber-100 text-amber-800'
      return 'bg-rose-100 text-rose-800 font-medium'
    },

    isSystemEndedSession() {
      if (!this.sessionFlow.length) return false
      const last = this.sessionFlow[this.sessionFlow.length - 1]
      return (
        last?.status === 'failed' ||
        last?.status === 'timeout' ||
        !last?.input ||
        last?.wait_time === null
      )
    },

    systemEndReason() {
      if (!this.sessionFlow.length) return null
      const last = this.sessionFlow[this.sessionFlow.length - 1]
      if (last?.status === 'timeout') return 'timeout'
      if (!last?.input) return 'no user response'
      if (last?.status === 'failed') return 'system error'
      return null
    },

    async startSession() {
      if (!this.msisdn) return alert('Please enter a mobile number')
      this.loading = true
      await new Promise(r => setTimeout(r, 1200))

      this.sessionActive = true
      this.sessionStart = new Date().toISOString()
      this.sessionFlow = []
      this.sessionReviews = []
      this.currentScreen = `Welcome to Mobile Banking USSD\n* Dial *123# to continue *\n\nEnter your PIN:`
      this.awaitingInput = true
      this.loading = false

      this.sessionFlow.push({
        menu_title: 'Initial Screen',
        response: this.currentScreen,
        status: 'success',
        wait_time: '0.0',
        apiMetrics: {
      fullUrl: `https://api.bankabc.co.bw/v1/customers/1`,
        method: 'POST',
        status: 200,
        duration: 500,
        timestamp: new Date().toISOString(),
        error: null
        },
        apiInsights: 'Response time optimal'
      })
    },

    sendInput() {
      if (!this.userInput.trim()) return

      const input = this.userInput.trim()

setTimeout(() => {
  const response = this.simulateResponse(input);
  this.currentScreen = response.text;
  this.awaitingInput = response.awaitingInput;

  this.sessionFlow.push({
    menu_title: response.title || 'USSD Response',
    response: response.text,
    wait_time: response.waitTime || '2.3',
    status: response.status || 'success',
    isPaginated: response.isPaginated || false,
    pageNumber: response.pageNumber,
    totalPages: response.totalPages,
    apiMetrics: response.apiMetrics,      // ← new
    apiInsights: response.apiInsights     // ← new
  });
}, 800 + Math.random() * 1200);

      this.userInput = ''
    },

    simulateResponse(input) {
    const start = performance.now();

    // Your existing logic...
    let response;
    if (input === '1234') {
        response = {
        title: 'Main Menu',
        text: '1. Check Balance\n2. Transfer\n3. Buy Airtime\n4. Mini Statement\n0. Exit',
        awaitingInput: true,
        waitTime: '1.8'
        };
    } else if (input === '1') {
        response = {
        title: 'Balance Enquiry',
        text: 'Your balance is BWP 1,234.56\nAvailable: BWP 1,200.00',
        awaitingInput: false,
        waitTime: '2.1'
        };
    } else {
        response = {
        title: 'Error',
        text: 'Invalid input. Please try again.',
        awaitingInput: true,
        waitTime: '1.2',
        status: 'failed'
        };
    }

    const duration = Math.round(performance.now() - start);
    const status = response.status === 'failed' ? 500 : 200;

  return {
    ...response,
    apiMetrics: {
      fullUrl: `https://api.bankabc.co.bw/v1/ussd/session/${this.msisdn || 'sim'}/input?value=${encodeURIComponent(input)}`,
      method: 'POST',
      status,
      duration,
      timestamp: new Date().toISOString(),
      error: status >= 400 ? 'Simulated server error' : null
    },
    apiInsights: status >= 400
      ? 'High error rate detected — check input validation'
      : duration > 1500
        ? 'Slow response — possible backend bottleneck'
        : 'Response time optimal'
  };
    },

    endSession() {
      if (confirm('End current session?')) {
        this.sessionActive = false
      }
    },

    addFlag() {
      if (!this.reviewCategory || !this.reviewPriority) {
        alert('Please select category and priority')
        return
      }

      const newFlag = {
        id: 'rev-' + Date.now(),
        user: 'Current QA',
        category: this.reviewCategory,
        priority: this.reviewPriority,
        comment: this.reviewComment || '(no comment)',
        timestamp: new Date().toISOString(),
        status: 'open',
        resolvedBy: null,
        resolvedAt: null,
        resolvedComment: null
      }

      this.sessionReviews.push(newFlag)
      this.showMarkReviewModal = false
      this.reviewCategory = ''
      this.reviewPriority = 'medium'
      this.reviewComment = ''
      alert('Flag added!')
    },

    resolveReview(reviewId) {
      const review = this.sessionReviews.find(r => r.id === reviewId)
      if (!review) return

      const comment = prompt('Resolution comment (optional):')

      review.status = 'resolved'
      review.resolvedBy = 'Current Dev'
      review.resolvedAt = new Date().toISOString()
      review.resolvedComment = comment || null

      alert('Flag marked as resolved!')
    }
  }
}
</script>

<style scoped>
/* Tighter phone frame */
.feature-frame {
  background: #111;
  border-radius: 2rem;
  padding: 1rem;
  box-shadow: 0 15px 35px -10px rgba(0,0,0,0.4);
}

.smart-frame {
  background: #000;
  border-radius: 2.5rem;
  padding: 0.75rem;
  box-shadow: 0 15px 35px -10px rgba(0,0,0,0.5);
  position: relative;
}

.smart-frame::before {
  content: '';
  position: absolute;
  top: 0.5rem;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 25px;
  background: #000;
  border-radius: 0 0 20px 20px;
  z-index: 10;
}

/* Hover show actions */
.group:hover .opacity-0 {
  opacity: 1;
}


    .mockup-phone {
        background-color: #000;
        border: 4px solid #4e4d4d;
        border-radius: 50px;
        margin: 0 auto;
        padding: 6px;
        display: inline-block;
        overflow: hidden
    }

    .mockup-phone .mockup-phone-camera {
        background: #000;
        border-radius: 3.40282e38px;
        width: 33%;
        height: 25px;
        margin: 0 auto;
        position: relative;
        top: 5px;
        left: 0
    }

    .mockup-phone .mockup-phone-camera:after {
        content: "";
        background-color: #2a292d;
        border-radius: 5px;
        width: 12px;
        height: 12px;
        position: absolute;
        top: 25%;
        right: 8%
    }

    .mockup-phone .mockup-phone-display {
        border-radius: 40px;
        margin-top: -25px;
        overflow: hidden
    }

    .mockup-phone-input {
        background: transparent;
        border: none;
        border-bottom: 2px solid #11d8b3 !important;
        border-radius: 0;
        box-shadow: none !important;
        margin: 16px 0 0 0;
        padding: 4px 0;
        width: 100%;
        outline: none;
    }

</style>
