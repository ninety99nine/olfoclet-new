<template>
  <div class="min-h-screen bg-slate-50 pb-12">

<!-- Header -->
<div class="bg-white border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-6 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <button @click="$router.back()" class="p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>

        <div class="flex items-center gap-3">
          <div>
            <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-3">
              Session #{{ session.id || '145,012' }}
            <span :class="statusStyles[session.status]" class="text-xs font-medium px-3 py-1 rounded-full">
            {{ session.status === 'success' ? 'Success' : 'Failed' }}
            </span>
              <!-- Pending Review Badge -->
              <span v-if="hasUnresolvedReviews"
                    class="text-xs font-medium px-3 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                {{ unresolvedReviewCount }} Flag{{ unresolvedReviewCount !== 1 ? 's' : '' }}
              </span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
              Started {{ formattedRelativeDate(session.started_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Right side -->
    <span :to="{ name: 'account-detail', params: { msisdn: session.msisdn } }"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        View Account
    </span>
    </div>
  </div>
</div>

    <!-- Failure Banner -->
    <div v-if="true" class="max-w-7xl mx-auto px-6 pt-4">
      <div class="bg-rose-50 border border-rose-200 rounded-xl p-5 flex items-start gap-4">
        <svg class="w-6 h-6 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="flex-1">
          <h3 class="font-medium text-rose-800">Session Failed</h3>
          <p v-if="session.error_code" class="text-sm text-rose-700 mt-1 font-mono">{{ session.error_code }}</p>
          <p v-else class="text-rose-700 mt-1">{{ session.error_message || 'An unexpected error occurred during the session.' }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-4">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Left Column -->
        <div class="lg:col-span-8 space-y-4">

          <!-- Quick Stats -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
              <p class="text-sm text-slate-500">Duration</p>
              <p class="text-2xl font-bold text-slate-900 mt-2">{{ session.duration }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
              <p class="text-sm text-slate-500">Steps</p>
              <p class="text-2xl font-bold text-indigo-600 mt-2">{{ session.steps }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
              <p class="text-sm text-slate-500">Avg Wait</p>
              <p class="text-2xl font-bold mt-2"
                 :class="{
                   'text-emerald-600': avgResponseTime <= 5,
                   'text-amber-600': avgResponseTime > 5 && avgResponseTime <= 15,
                   'text-rose-600': avgResponseTime > 15
                 }">
                {{ avgResponseTime }}s
              </p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
              <p class="text-sm text-slate-500">Device</p>
              <p class="text-xl font-medium text-slate-900 mt-2 flex items-center gap-2">
                <span :class="session.device === 'mobile' ? 'text-indigo-600' : 'text-amber-600'">
                  {{ session.device === 'mobile' ? 'Mobile' : 'Simulator' }}
                </span>
              </p>
            </div>
          </div>

          <!-- USSD Flow Timeline -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
              <h2 class="text-lg font-semibold text-slate-900">Session Flow</h2>
              <div class="text-sm text-slate-500">
                Slowest response: <span class="font-medium">{{ maxWaitTime }}s</span>
              </div>
            </div>

            <div class="p-6">
              <div class="relative">
                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-slate-200"></div>

                <div v-for="(step, index) in session.flow" :key="index" class="relative mb-10 last:mb-0">
                  <div class="flex items-start gap-6">
                    <div class="flex-shrink-0 z-10 w-16 h-16 rounded-full bg-white border-4 border-slate-200 flex items-center justify-center font-bold text-xl text-slate-700">
                      {{ index + 1 }}
                    </div>

                    <div class="flex-1 bg-slate-50 rounded-xl p-5 border border-slate-200 relative">
                      <div class="flex justify-between items-start gap-4">
                        <p class="font-medium text-slate-900">{{ step.menu_title || 'Menu Step' }}</p>
                        <span v-if="step.status !== 'success'" class="bg-rose-100 text-rose-800 px-3 py-1 text-xs font-medium rounded-full shrink-0">
                          {{ step.status }}
                        </span>
                      </div>

                      <div class="pt-2">
                        <div class="bg-white border border-slate-300 rounded-lg p-4 font-mono text-sm text-slate-800 whitespace-pre-line leading-relaxed relative">
                          {{ step.response || 'No response recorded' }}

                          <div class="absolute -bottom-3 right-4 flex items-center gap-2">
                            <!-- Character count + pagination -->
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium bg-slate-100 text-slate-700 rounded-full shadow-sm"
                                 :class="{ 'bg-indigo-100 text-indigo-800': step.isPaginated }">
                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                              {{ (step.response || '').length }}

                              <template v-if="step.isPaginated">
                                <span class="opacity-75 ml-1">·</span>
                                <span class="font-medium">
                                  page {{ step.pageNumber }}<span v-if="step.totalPages" class="opacity-70">/{{ step.totalPages }}</span>
                                </span>
                              </template>
                            </div>

                            <!-- Wait time -->
                            <div v-if="step.wait_time" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full shadow-sm"
                                 :class="waitTimeClass(step.wait_time)">
                              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                              {{ step.wait_time }}
                              <span v-if="isLongestWait(step, index)" class="opacity-75 text-xs ml-1">
                                (slowest)
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div v-if="step.input" class="grid grid-cols-2 gap-4 mt-6">
                        <div class="flex items-center space-x-3">
                          <span class="text-slate-500 text-xs">Replied</span>
                          <p class="bg-white border border-slate-300 font-mono px-4 py-2 rounded-lg text-slate-800 text-sm">
                            {{ step.input }}
                          </p>
                        </div>
                      </div>

                      <div v-if="index === session.flow.length - 1 && isSystemEndedSession()"
                           class="mt-6 border border-amber-200 text-sm text-amber-700 bg-amber-50 py-2 px-4 rounded-lg">
                        <span class="font-medium">Session terminated by system</span>
                        <span class="ml-2 opacity-80">({{ systemEndReason() || 'timeout / forced end' }})</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Sidebar -->
        <div class="lg:col-span-4 space-y-4">
          <!-- Session Details -->
          <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-5">Session Details</h3>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between">
                <dt class="text-slate-500">Phone Number</dt>
                <dd class="font-medium text-indigo-800 hover:underline transition-colors">{{ session.msisdn }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Application</dt>
                <dd class="font-medium text-slate-900">{{ session.app_name || 'Mobile Banking USSD' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Shortcode</dt>
                <dd class="font-medium text-slate-900">{{ session.shortcode || '*123#' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Network</dt>
                <dd class="font-medium text-slate-900">{{ session.network || '—' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Country</dt>
                <dd class="flex items-center gap-2">
                  <img :src="`/svgs/country-flags/${session.country?.toLowerCase()}.svg`" alt="" class="w-6 h-6 rounded-full" />
                  {{ session.country_name || 'Botswana' }}
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Started</dt>
                <dd class="font-medium text-slate-900">{{ formattedRelativeDate(session.started_at) }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">IP Address</dt>
                <dd class="font-medium text-slate-900">{{ session.ip || '—' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Session ID</dt>
                <dd class="font-medium text-slate-900 truncate max-w-[180px]">{{ session.session_id }}</dd>
              </div>
            </dl>
          </div>

<!-- Flags Section -->
<div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
  <h3 class="text-lg font-semibold text-slate-900 mb-5 flex items-center justify-between flex-wrap gap-3">
    <span>Flags ({{ session.reviews?.length || 0 }})</span>
    <button @click="showMarkReviewModal = true"
            class="text-sm px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors flex items-center gap-1.5 shrink-0">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      New Flag
    </button>
  </h3>

  <div v-if="session.reviews?.length" class="space-y-6 max-h-112.5 overflow-y-auto pr-2">
    <div v-for="review in session.reviews" :key="review.id"
         class="border rounded-lg p-4"
         :class="review.status === 'resolved'
                ? 'bg-slate-50 border-slate-200'
                : 'bg-white border-slate-300 shadow-sm'">

      <!-- Header -->
      <div class="mb-3">
        <div>
            <div class="flex items-center justify-between">

                <p class="font-medium text-slate-900 truncate max-w-40">
                    {{ review.user }}
                </p>

                <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                {{ formattedRelativeDate(review.timestamp) }}
                </span>
            </div>
          <div class="flex flex-wrap items-center gap-1.5 mt-2">
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
              {{ review.category }}
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                  :class="{
                    'bg-emerald-100 text-emerald-800': review.priority === 'low',
                    'bg-amber-100 text-amber-800': review.priority === 'medium',
                    'bg-orange-100 text-orange-800': review.priority === 'high',
                    'bg-rose-100 text-rose-800': review.priority === 'critical'
                  }">
              {{ review.priority }}
            </span>
            <span v-if="review.status == 'resolved'" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
              Resolved
            </span>
          </div>
        </div>

      </div>

      <!-- Comment -->
      <p class="text-sm text-slate-700 leading-relaxed break-words">
        {{ review.comment }}
      </p>

      <!-- Actions / Resolved info -->
      <div class="mt-4">
        <div v-if="review.status === 'open'" class="flex justify-end">
          <button @click="resolveReview(review.id)"
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg transition-colors">
            Mark as Resolved
          </button>
        </div>

        <div v-if="review.status === 'resolved'"
             class="text-sm text-slate-600 bg-slate-100/70 p-3 rounded-lg border border-slate-200">
          <p v-if="review.resolvedComment" class="mb-2 italic">
            "{{ review.resolvedComment }}"
          </p>
          <p>
            Resolved by <span class="font-medium text-slate-800">{{ review.resolvedBy || 'Team' }}</span>
          </p>
            <p class="text-xs text-slate-500">
              {{ review.resolvedAt ? formattedRelativeDate(review.resolvedAt) : '' }}
            </p>
        </div>
      </div>
    </div>
  </div>

  <p v-else class="text-sm text-slate-500 italic text-center py-4">
    No flags or reviews yet.
  </p>
</div>


          <!-- Actions -->
          <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-5">Actions</h3>
            <div class="space-y-3">

              <button class="w-full px-5 py-3 bg-white border border-slate-300 hover:border-slate-400 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Copy Raw Data
              </button>
              <button class="w-full px-5 py-3 bg-white border border-slate-300 hover:border-slate-400 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export JSON
              </button>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals (basic implementation - you can replace with better modal component) -->
<!-- Mark for Review Modal -->
<div v-if="showMarkReviewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl p-6 max-w-lg w-full">
    <h3 class="text-xl font-semibold mb-5 text-slate-900">Mark Session for Review</h3>

    <form @submit.prevent="markForReview" class="space-y-5">

      <!-- Category -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Category <span class="text-rose-500">*</span>
        </label>
        <select v-model="reviewCategory" required
                class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option value="" disabled>Select category</option>
          <option value="bug">Bug / Technical Error</option>
          <option value="ux">UX / Flow Confusion</option>
          <option value="performance">Performance / Slow Response</option>
          <option value="content">Menu Text / Wording Issue</option>
          <option value="security">Security / Compliance Concern</option>
          <option value="feature-request">Feature Suggestion / Improvement</option>
          <option value="other">Other</option>
        </select>
      </div>

      <!-- Priority / Importance -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Priority <span class="text-rose-500">*</span>
        </label>
        <div class="grid grid-cols-4 gap-2">
          <label class="flex flex-col items-center cursor-pointer">
            <input type="radio" v-model="reviewPriority" value="low" class="sr-only" />
            <div :class="reviewPriority === 'low'
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white border-slate-300 hover:bg-slate-50'"
                 class="w-full py-2.5 px-3 text-center rounded-lg border font-medium text-sm transition-colors">
              Low
            </div>
          </label>
          <label class="flex flex-col items-center cursor-pointer">
            <input type="radio" v-model="reviewPriority" value="medium" class="sr-only" checked />
            <div :class="reviewPriority === 'medium'
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white border-slate-300 hover:bg-slate-50'"
                 class="w-full py-2.5 px-3 text-center rounded-lg border font-medium text-sm transition-colors">
              Medium
            </div>
          </label>
          <label class="flex flex-col items-center cursor-pointer">
            <input type="radio" v-model="reviewPriority" value="high" class="sr-only" />
            <div :class="reviewPriority === 'high'
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white border-slate-300 hover:bg-slate-50'"
                 class="w-full py-2.5 px-3 text-center rounded-lg border font-medium text-sm transition-colors">
              High
            </div>
          </label>
          <label class="flex flex-col items-center cursor-pointer">
            <input type="radio" v-model="reviewPriority" value="critical" class="sr-only" />
            <div :class="reviewPriority === 'critical'
                        ? 'bg-rose-600 text-white border-rose-600'
                        : 'bg-white border-slate-300 hover:bg-slate-50'"
                 class="w-full py-2.5 px-3 text-center rounded-lg border font-medium text-sm transition-colors">
              Critical
            </div>
          </label>
        </div>
      </div>

      <!-- Comment -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          Comment / Description
        </label>
        <textarea v-model="reviewComment" rows="4"
                  class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                  placeholder="Describe what you noticed, which step(s) are affected, expected vs actual behavior..."></textarea>
      </div>

      <!-- Buttons -->
      <div class="flex gap-3 justify-end pt-2">
        <button type="button" @click="showMarkReviewModal = false"
                class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
          Cancel
        </button>
        <button type="submit"
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
          Submit Flag
        </button>
      </div>
    </form>
  </div>
</div>

    <!-- Add other modals similarly: Submit Review, Add Comment, Create Ticket -->
  </div>
</template>

<script>
import { formattedRelativeDate } from '@Utils/dateUtils.js'

export default {
  name: 'SessionDetail',

  data() {
    return {
      showMarkReviewModal: false,
      reviewReason: '',
      showSubmitReviewModal: false,
      reviewNotes: '',
      showAddCommentModal: false,
      commentText: '',
      showCreateTicketModal: false,
      ticketDescription: '',

      showMarkReviewModal: false,
    reviewCategory: '',      // new
    reviewPriority: 'medium', // default to medium
    reviewComment: '',       // renamed from reviewReason

      session: {
        id: '145012',
        status: 'success',
        started_at: new Date().toISOString(),
        duration: '1m 38s',
        steps: 14,
        msisdn: '+267 71 234 567',
        network: 'Orange',
        country: 'BW',
        country_name: 'Botswana',
        app_name: 'Mobile Banking USSD',
        shortcode: '*123#',
        session_id: 'sess_abc123xyz789',
        ip: '197.234.56.78',
        device: 'mobile',
        flaggedForReview: false,
        error_message: null,
        error_code: 'Notice: Undefined variable $payableAmount',

        flow: [
          {
            menu_title: 'Main Menu',
            input: '1',
            response: 'Welcome to Mobile Banking!\n1. Check Balance\n2. Transfer Money\n3. Buy Airtime\n4. Mini Statement\n0. Exit',
            status: 'success',
            wait_time: '3s',
            isPaginated: true,
            pageNumber: 2,
            totalPages: 3
          },
          {
            menu_title: 'Balance Enquiry',
            input: '1',
            response: 'Your current balance is:\nBWP 1,234.56\nAvailable: BWP 1,200.00',
            status: 'success',
            wait_time: '3s'
          },
          {
            menu_title: 'Main Menu (return)',
            input: null,
            response: 'Thank you for using our service.\nHave a great day!',
            status: 'failed',
            wait_time: null
          }
        ],

        activities: [
          // Example data - normally loaded from API
          {
            user: 'QA - Thabo',
            action: 'Marked for review',
            reason: 'Unusually high average wait time',
            timestamp: '2026-01-15T14:22:10Z'
          },
          {
            user: 'Dev - Neo',
            action: 'Added comment',
            notes: 'Investigating possible caching issue on menu rendering',
            timestamp: '2026-01-15T15:47:33Z'
          },
          {
            user: 'Neo (QA)',
            category: 'bug',
            priority: 'critical',
            action: 'Marked for review',
            comment: 'The user is failing to make a payment',
            timestamp: '2026-01-15T15:47:33Z'
          }
        ],

        reviews: [  // ← new array instead of single flaggedForReview
    {
      id: 'rev-001',                  // unique id (generate or from backend)
      user: 'Neo (QA)',
      category: 'bug',
      priority: 'critical',
      comment: 'The user is failing to make a payment',
      timestamp: '2026-01-15T15:47:33Z',
      status: 'open',                 // 'open' | 'resolved'
      resolvedBy: null,
      resolvedAt: null,
      resolvedComment: null
    },
    {
      id: 'rev-002',
      user: 'Thabo (QA)',
      category: 'security',
      priority: 'high',
      comment: 'Potential session hijacking vector detected',
      timestamp: '2026-01-16T09:12:00Z',
      status: 'open',
      resolvedBy: null,
      resolvedAt: null,
      resolvedComment: null
    }
]
      },

      statusStyles: {
        success: 'bg-emerald-100 text-emerald-800',
        failed: 'bg-rose-100 text-rose-800'
      }
    }
  },

  computed: {
    formattedRelativeDate() {
      return date => formattedRelativeDate(date)
    },

    avgResponseTime() {
      if (!this.session?.flow?.length) return 0
      const times = this.session.flow
        .filter(s => s.wait_time)
        .map(s => parseFloat(s.wait_time.replace('s', '')) || 0)
        .filter(t => t > 0)
      if (!times.length) return 0
      return (times.reduce((a, b) => a + b, 0) / times.length).toFixed(1)
    },

    maxWaitTime() {
      const times = this.session.flow
        .filter(s => s.wait_time)
        .map(s => parseFloat(s.wait_time.replace('s', '')) || 0)
      return times.length ? Math.max(...times).toFixed(1) : '—'
    },

    longestWaitIndex() {
      let max = 0
      let index = -1
      this.session.flow.forEach((step, i) => {
        const val = parseFloat(step.wait_time?.replace('s', '') || '0')
        if (val > max) {
          max = val
          index = i
        }
      })
      return index
    },hasUnresolvedReviews() {
    return this.session?.reviews?.some(r => r.status === 'open') ?? false;
  },

  unresolvedReviewCount() {
    return this.session?.reviews?.filter(r => r.status === 'open')?.length ?? 0;
  }
  },

  methods: {
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
        console.log(this.session);
        console.log(this.session.flow);
        console.log(this.session.flow.length);
      const last = this.session.flow[this.session.flow.length - 1]
      return (
        this.session.status === 'failed' ||
        last?.status === 'timeout' ||
        !last?.input ||
        last?.wait_time === null
      )
    },

    systemEndReason() {
      const last = this.session.flow[this.session.flow.length - 1]
      if (last?.status === 'timeout') return 'timeout'
      if (!last?.input) return 'no user response'
      if (this.session.status === 'failed') return 'system error'
      return null
    },

async resolveReview(reviewId) {
    const review = this.session.reviews.find(r => r.id === reviewId);
    if (!review) return;

    const resolvedComment = prompt('Optional: Add resolution comment (or leave empty):');

    // TODO: Real API call
    // await api.resolveReview(this.session.id, reviewId, { resolvedComment });

    // Update local state
    review.status = 'resolved';
    review.resolvedBy = 'Current User'; // replace with real user
    review.resolvedAt = new Date().toISOString();
    review.resolvedComment = resolvedComment || null;

    // Also log it in activities if you want
    this.session.activities.push({
      user: 'Current User',
      action: 'Resolved review',
      comment: `Resolved "${review.category}" flag: ${resolvedComment || '(no comment)'}`,
      timestamp: new Date().toISOString()
    });

    alert('Review marked as resolved!');
  },

  async markForReview() {
    if (!this.reviewCategory || !this.reviewPriority) {
      alert('Please select category and priority');
      return;
    }

    const newReview = {
      id: 'rev-' + Date.now(), // simple temp id; use uuid or backend id in real app
      user: 'Current User (QA)',
      category: this.reviewCategory,
      priority: this.reviewPriority,
      action: 'Marked for review',
      comment: this.reviewComment || '(no comment)',
      timestamp: new Date().toISOString(),
      status: 'open',
      resolvedBy: null,
      resolvedAt: null,
      resolvedComment: null
    };

    this.session.reviews.push(newReview);
    this.session.flaggedForReview = true; // still useful as global flag

    this.showMarkReviewModal = false;
    this.reviewCategory = '';
    this.reviewPriority = 'medium';
    this.reviewComment = '';

    alert('New review flag added!');
  }
  }
}
</script>
