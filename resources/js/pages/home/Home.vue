<template>
  <div class="min-h-screen bg-slate-50">

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Main Area -->
        <div class="lg:col-span-9 space-y-8">

          <!-- Quick Stats -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200">
              <p class="text-sm text-slate-500">Total Sessions</p>
              <p class="text-2xl font-semibold text-slate-900 mt-1">142,380</p>
              <p class="text-xs text-emerald-600 mt-1">+18% this week</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200">
              <p class="text-sm text-slate-500">Avg. Duration</p>
              <p class="text-2xl font-semibold text-slate-900 mt-1">1m 42s</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200">
              <p class="text-sm text-slate-500">Success Rate</p>
              <p class="text-2xl font-semibold text-emerald-600 mt-1">98.4%</p>
            </div>
          </div>
<!-- Latest Sessions -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
    <h2 class="text-lg font-semibold text-slate-900">Latest Sessions</h2>
    <button class="px-4 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
      View All Sessions →
    </button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead class="bg-slate-50/70">
        <tr>
          <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
            Network
          </th>
          <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
            Mobile
          </th>
          <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
            Duration
          </th>
          <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
            When
          </th>
          <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
            Status
          </th>
          <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
            Steps
          </th>
          <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
            Device
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 whitespace-nowrap">
        <!-- Row 1 -->
        <tr class="hover:bg-slate-50/60 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-2 text-sm text-slate-600">
              <img
                :src="'/svgs/country-flags/bw.svg'"
                alt=""
                class="w-5 h-5 rounded-full object-cover border border-slate-200/70 shadow-sm"
              />
              <span>Orange</span>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
            +267 71 234 567
          </td>
          <td class="px-6 py-4 text-sm text-slate-500">1m 42s</td>
          <td class="px-6 py-4 text-sm text-slate-500">2 min ago</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full">
              Success
            </span>
          </td>
          <td class="px-6 py-4 text-center text-sm text-slate-500">18</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
              Mobile
            </span>
          </td>
        </tr>

        <!-- Row 2 -->
        <tr class="hover:bg-slate-50/60 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-2 text-sm text-slate-600">
              <img
                :src="'/svgs/country-flags/bw.svg'"
                alt=""
                class="w-5 h-5 rounded-full object-cover border border-slate-200/70 shadow-sm"
              />
              <span>Mascom</span>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
            +267 75 987 654
          </td>
          <td class="px-6 py-4 text-sm text-slate-500">58s</td>
          <td class="px-6 py-4 text-sm text-slate-500">7 min ago</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full">
              Success
            </span>
          </td>
          <td class="px-6 py-4 text-center text-sm text-slate-500">9</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
              Mobile
            </span>
          </td>
        </tr>

        <!-- Row 3 - Failed -->
        <tr class="hover:bg-slate-50/60 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-2 text-sm text-slate-600">
              <img
                :src="'/svgs/country-flags/za.svg'"
                alt=""
                class="w-5 h-5 rounded-full object-cover border border-slate-200/70 shadow-sm"
              />
              <span>MTN</span>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
            +27 82 456 789
          </td>
          <td class="px-6 py-4 text-sm text-slate-500">2m 15s</td>
          <td class="px-6 py-4 text-sm text-slate-500">14 min ago</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-rose-50 text-rose-700 text-xs font-medium rounded-full">
              Failed
            </span>
          </td>
          <td class="px-6 py-4 text-center text-sm text-slate-500">4</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full">
              Simulator
            </span>
          </td>
        </tr>

        <!-- Row 4 -->
        <tr class="hover:bg-slate-50/60 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-2 text-sm text-slate-600">
              <img
                :src="'/svgs/country-flags/bw.svg'"
                alt=""
                class="w-5 h-5 rounded-full object-cover border border-slate-200/70 shadow-sm"
              />
              <span>Orange</span>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
            +267 77 111 222
          </td>
          <td class="px-6 py-4 text-sm text-slate-500">3m 10s</td>
          <td class="px-6 py-4 text-sm text-slate-500">31 min ago</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full">
              Success
            </span>
          </td>
          <td class="px-6 py-4 text-center text-sm text-slate-500">24</td>
          <td class="px-6 py-4">
            <span class="inline-flex px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
              Mobile
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

    <div class="mt-8 px-8">
        <p class="font-bold mb-2">About flags</p>
        <p class="mb-4">Flags can be raised for different issues</p>
        <ul class="list-disc">
            <li>Failed API calls</li>
            <li>Runtime code errors e.g syntax errors</li>
            <li>Validation Errors (e.g after 3 failed validations on the same session we flag)</li>
        </ul>
    </div>

        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-3 space-y-8">
<!-- Deployments -->
<div class="bg-white rounded-xl border border-slate-200">
  <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
    <h3 class="text-base font-semibold text-slate-900">Deployments</h3>
    <button class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
      View All →
    </button>
  </div>
  <div class="divide-y divide-slate-100 py-2">
    <div class="px-6 py-3.5 flex items-center justify-between text-sm">
      <div class="flex items-center gap-3">
        <img
          :src="'/svgs/country-flags/bw.svg'"
          alt="Botswana"
          class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-sm"
        />
        <span class="font-medium text-slate-900">Orange</span>
      </div>
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" class="sr-only peer" checked />
        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
      </label>
    </div>

    <div class="px-6 py-3.5 flex items-center justify-between text-sm">
      <div class="flex items-center gap-3">
        <img
          :src="'/svgs/country-flags/bw.svg'"
          alt="Botswana"
          class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-sm"
        />
        <span class="font-medium text-slate-900">Mascom</span>
      </div>
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" class="sr-only peer" checked />
        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
      </label>
    </div>

    <!-- Example for different country -->
    <div class="px-6 py-3.5 flex items-center justify-between text-sm">
      <div class="flex items-center gap-3">
        <img
          :src="'/svgs/country-flags/za.svg'"
          alt="South Africa"
          class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-sm"
        />
        <span class="font-medium text-slate-900">MTN</span>
      </div>
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" class="sr-only peer" />
        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
      </label>
    </div>
  </div>
</div>

          <!-- Quick Actions -->
          <div class="space-y-2">
            <button class="w-full px-5 pr-3 py-3 bg-white border border-slate-200 rounded-xl text-left hover:scale-105 hover:shadow-sm transition-all duration-300 cursor-pointer flex justify-between items-center text-sm font-medium">
              Build App
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
            <button class="w-full px-5 pr-3 py-3 bg-white border border-slate-200 rounded-xl text-left hover:scale-105 hover:shadow-sm transition-all duration-300 cursor-pointer flex justify-between items-center text-sm font-medium">
              Review App
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
            <button class="w-full px-5 pr-3 py-3 bg-white border border-slate-200 rounded-xl text-left hover:scale-105 hover:shadow-sm transition-all duration-300 cursor-pointer flex justify-between items-center text-sm font-medium">
              View Analytics
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'AppHome'
}
</script>
