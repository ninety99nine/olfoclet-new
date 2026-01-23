<template>

    <div class="min-h-screen bg-slate-50 pb-12">

        <!-- Header -->
        <div class="bg-white border-b border-slate-200">

            <div class="max-w-7xl mx-auto px-6 py-6">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                    <div class="flex items-center gap-4">

                        <Button
                            size="xs"
                            type="basic"
                            rightIconSize="24"
                            :rightIcon="ArrowLeft"
                            buttonClass="rounded-lg"
                            :action="() => $router.back()">
                        </Button>

                        <div>
                            <h1 class="text-2xl font-semibold text-slate-900">
                                Session Details
                            </h1>
                            <nav class="flex mt-2" aria-label="Breadcrumb">
                                <ol class="flex items-center space-x-2 text-sm text-slate-500">
                                    <li>
                                        <router-link :to="{ name: 'show-app-sessions' }" class="hover:text-slate-900 hover:underline">Sessions</router-link>
                                    </li>
                                    <li>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </li>
                                    <li class="font-medium">{{ session?.id || 'Loading...' }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <Button
                        size="md"
                        type="primary"
                        mode="outline"
                        :rightIcon="ArrowRight"
                        buttonClass="rounded-lg"
                        :action="navigateToAccount"
                        :skeleton="isLoadingSession">
                        <span class="mr-2">View Account</span>
                    </Button>

                </div>

            </div>

        </div>

        <!-- Failure Banner -->
        <transition name="fade" v-if="session">
            <div class="relative"
                v-if="!session.successful && showErrorBanner">

                <div class="max-w-7xl mx-auto px-6 pt-4">
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-5 flex items-start gap-4">
                        <svg class="w-6 h-6 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="font-medium text-rose-800">Session Failed</h3>
                            <p v-if="session.error_message" class="text-sm text-rose-700 mt-1 font-mono">{{ session.error_message }}</p>
                            <p v-else class="text-rose-700 mt-1">An unexpected error occurred during the session</p>
                        </div>
                    </div>
                </div>

                <X size="16" @click="showErrorBanner = false" class="absolute top-8 right-10 text-rose-300 hover:text-rose-700 hover:scale-125 transition-all duration-300 cursor-pointer"></X>
            </div>
        </transition>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 py-4">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- Left Column -->
                <div class="lg:col-span-8 space-y-4">

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <p class="text-sm text-slate-500">Duration</p>
                            <Skeleton v-if="isLoadingSession" width="w-16" class="shrink-0 mt-6"></Skeleton>
                            <p v-else class="text-2xl font-bold text-slate-900 mt-2">{{ formatDuration(session.total_duration_ms) }}</p>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <p class="text-sm text-slate-500">Steps</p>
                            <Skeleton v-if="isLoadingSession" width="w-16" class="shrink-0 mt-6"></Skeleton>
                            <p v-else class="text-2xl font-bold text-indigo-600 mt-2">{{ session.total_steps }}</p>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <p class="text-sm text-slate-500">Avg Wait</p>
                            <Skeleton v-if="isLoadingSession" width="w-16" class="shrink-0 mt-6"></Skeleton>
                            <p v-else class="text-2xl font-bold mt-2"
                                :class="{
                                    'text-amber-600': session.avg_wait_time_status == 'medium',
                                    'text-emerald-600': session.avg_wait_time_status == 'low',
                                    'text-rose-600': session.avg_wait_time_status == 'high',
                                }">
                                {{ formatDuration(session.avg_wait_time_ms) }}
                            </p>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <p class="text-sm text-slate-500">Device</p>
                            <Skeleton v-if="isLoadingSession" width="w-16" class="shrink-0 mt-6"></Skeleton>
                            <p v-else class="text-xl font-medium text-slate-900 mt-2 flex items-center gap-2">
                                <span :class="session.simulated ? 'text-amber-600' : 'text-indigo-600'">
                                {{ session.simulated ? 'Simulator' : 'Mobile' }}
                                </span>
                            </p>
                        </div>

                    </div>

                    <!-- USSD Flow Timeline -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden">

                        <!-- Header -->
                        <div class="bg-white px-6 py-5 border-b border-slate-100 flex items-center justify-between shadow-xs">
                            <h2 class="text-lg font-semibold text-slate-900">Session Flow</h2>

                            <div v-if="!isLoadingSession && maxWaitTimeMs > 0" class="text-sm text-slate-600 flex items-center gap-2">
                                <Timer size="16" class="text-slate-500" />
                                <span>Slowest step:</span>
                                <span class="font-medium text-slate-900">{{ formatDuration(maxWaitTimeMs, 1) }}</span>
                            </div>
                        </div>

                        <!-- Loading state -->
                        <div v-if="isLoadingSession" class="p-12 text-center text-slate-500">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                            <span class="text-sm">Loading session steps...</span>
                        </div>

                        <!-- Empty state -->
                        <div v-else-if="!session?.steps?.length" class="p-12 text-center text-slate-500">
                            <Frown size="40" class="mx-auto mb-4 text-slate-400" />
                            <p class="text-lg font-medium">No steps recorded</p>
                            <p class="mt-2 text-sm">This session may have terminated very early.</p>
                        </div>

                        <!-- Actual timeline -->
                        <div v-else class="p-8">

                            <div class="relative pl-10 md:pl-12">

                            <!-- Vertical timeline line -->
                            <div class="absolute left-5 md:left-6 top-0 bottom-0 w-0.5 bg-slate-200"></div>

                                <div v-for="(step, index) in session.steps" :key="step.id || index" class="relative mb-10 last:mb-0">

                                    <!-- Step number circle -->
                                    <div class="absolute -left-11 -top-1 w-10 h-10 rounded-full bg-white border-4 border-slate-200 flex items-center justify-center font-bold text-slate-700 shadow-sm z-10">
                                    {{ index + 1 }}
                                    </div>

                                    <!-- Card -->
                                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm ml-2 md:ml-4 transition-all hover:shadow-md group relative">

                                    <!-- Header row -->
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div>
                                            <h3 class="font-medium text-slate-900">{{ step.step_title || 'Unnamed Step' }}</h3>
                                            <p v-if="index == 0" class="text-xs text-slate-500 mt-0.5">
                                                {{ formattedRelativeDate(step.created_at) }}
                                            </p>
                                        </div>

                                        <!-- Status badges -->
                                        <div class="flex flex-wrap gap-2 justify-end">
                                            <span v-if="step.paginated" class="bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-full font-medium">
                                                Paginated · {{ step.page_number }} / {{ step.total_pages }}
                                            </span>

                                            <span v-if="isLongestWait(step, session.steps)" class="bg-amber-50 text-amber-800 text-xs px-2.5 py-1 rounded-full font-semibold">
                                                Slowest
                                            </span>

                                            <span v-if="session.steps.length == 1" class="bg-indigo-50 text-indigo-800 text-xs px-2.5 py-1 rounded-full font-semibold">
                                                Only Response
                                            </span>

                                            <span v-if="!step.successful" class="bg-rose-50 text-rose-700 text-xs px-2.5 py-1 rounded-full font-medium">
                                                Failed
                                            </span>
                                        </div>
                                        <Button
                                            size="xs"
                                            mode="solid"
                                            type="warning"
                                            buttonClass="rounded-lg"
                                            :leftIcon="FlagTriangleRight"
                                            :action="() => flagThisStep(step)"
                                            class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none group-hover:pointer-events-auto">
                                            <span class="ml-1">Flag Step</span>
                                        </Button>
                                    </div>

                                    <!-- Main content area -->
                                    <div class="space-y-4">

                                        <!-- System Response -->
                                        <div>

                                            <div v-if="index == 0" class="text-xs text-slate-500 mb-1.5 font-medium uppercase tracking-wide">First Response</div>

                                            <div
                                                :class="[
                                                    'bg-slate-50 border border-slate-200 rounded-lg p-4 font-mono text-sm text-slate-800 whitespace-pre-wrap leading-relaxed',
                                                    { 'mb-8' : isEmpty(step.reply) }
                                                ]">
                                                {{ step.step_content || 'No content recorded' }}
                                            </div>

                                            <!-- Duration badge -->
                                            <div v-if="isNotEmpty(step.reply)" class="mt-2 flex items-center gap-2 text-xs">

                                                <span v-if="index == 0 && step.total_duration_ms > 0" class="text-slate-500">
                                                    Delay
                                                </span>

                                                <div :class="[
                                                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-medium',
                                                    step.total_duration_status === 'low'    ? 'bg-emerald-100 text-emerald-800' :
                                                    step.total_duration_status === 'medium' ? 'bg-amber-100 text-amber-800' :
                                                    'bg-rose-100 text-rose-800 font-semibold'
                                                    ]">
                                                    <Timer size="14" />
                                                    {{ formatDuration(step.total_duration_ms, 1) }}
                                                </div>

                                            </div>

                                        </div>

                                        <!-- User Reply (only if exists) -->
                                        <div v-if="isNotEmpty(step.reply)" class="flex items-center space-x-4 pt-2 border-t border-slate-100">
                                            <div class="text-slate-500 text-xs">Reply</div>
                                            <code class="bg-slate-100 px-3 py-1.5 rounded font-mono text-sm text-slate-800">
                                                {{ step.reply }}
                                            </code>
                                        </div>

                                    </div>

                                    <!-- Final termination note -->
                                    <div v-if="index === session.steps.length - 1 && session.terminated_by_system"
                                        class="mt-5 bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
                                        <span class="font-medium">Session ended by system</span>
                                        <span class="ml-1 opacity-80">(timeout / forced termination)</span>
                                    </div>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Right Column -->
                <div class="lg:col-span-4 space-y-4">

                    <!-- Overview -->
                    <div
                        class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900 mb-5">Overview</h3>
                        <dl class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between space-x-8">
                                <dt class="text-slate-500">Phone Number</dt>
                                <Skeleton v-if="isLoadingSession" width="w-32" class="shrink-0"></Skeleton>
                                <dd
                                    v-else
                                    @click.stop="navigateToAccount"
                                    class="font-medium text-blue-600 cursor-pointer hover:underline transition-colors">{{ session.msisdn }}</dd>
                            </div>
                            <div class="flex justify-between space-x-8">
                                <dt class="text-slate-500">Shortcode</dt>
                                <Skeleton v-if="isLoadingSession" width="w-16" class="shrink-0"></Skeleton>
                                <dd
                                    v-else class="font-medium text-slate-900">{{ session.shortcode || '—' }}</dd>
                            </div>
                            <div class="flex justify-between space-x-8">
                                <dt class="text-slate-500">Network</dt>
                                <Skeleton v-if="isLoadingSession" width="w-24" class="shrink-0"></Skeleton>
                                <dd
                                    v-else class="font-medium text-slate-900">{{ session.network || '—' }}</dd>
                            </div>
                            <div class="flex justify-between space-x-8">
                                <dt class="text-slate-500">Country</dt>
                                <div v-if="isLoadingSession" class="flex items-center space-x-2">
                                    <Skeleton width="w-6" height="h-6" class="shrink-0"></Skeleton>
                                    <Skeleton width="w-20" class="shrink-0"></Skeleton>
                                </div>
                                <dd
                                    v-else class="flex items-center gap-2">
                                <img :src="`/svgs/country-flags/${session.country?.toLowerCase()}.svg`" alt="" class="w-6 h-6 rounded-full" />
                                {{ countryName || '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between space-x-8">
                                <dt class="text-slate-500">Started</dt>
                                <Skeleton v-if="isLoadingSession" width="w-32" class="shrink-0"></Skeleton>
                                <dd
                                    v-else class="font-medium text-slate-900">{{ formattedRelativeDate(session.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between space-x-8">
                                <dt class="text-slate-500">IP Address</dt>
                                <Skeleton v-if="isLoadingSession" width="w-36" class="shrink-0"></Skeleton>
                                <dd
                                    v-else class="font-medium text-slate-900">{{ session.ip || '—' }}</dd>
                            </div>
                            <div class="flex justify-between space-x-8">
                                <dt class="text-slate-500">Session ID</dt>
                                <Skeleton v-if="isLoadingSession" width="w-40" class="shrink-0"></Skeleton>
                                <dd
                                    v-else class="font-medium text-slate-900 font-mono truncate">{{ session.session_id }}</dd>
                            </div>
                        </dl>

                        <div class="flex justify-end">
                            <Button
                                size="sm"
                                type="light"
                                mode="outline"
                                :leftIcon="Copy"
                                buttonClass="rounded-lg"
                                :skeleton="isLoadingSession"
                                :copyContent="session?.session_id">
                                <span class="ml-1">Copy Session ID</span>
                            </Button>
                        </div>
                    </div>

                    <!-- Flags Section -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">

                        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">

                            <h3 class="text-lg font-semibold text-slate-900">Flags <template v-if="flagsPagination">({{ flagsPagination?.meta?.total }})</template></h3>

                            <Button
                                size="sm"
                                mode="solid"
                                type="warning"
                                :leftIcon="Plus"
                                :action="showFlagModal"
                                buttonClass="rounded-lg"
                                :skeleton="isLoadingSession">
                                <span class="mr-2">New Flag</span>
                            </Button>

                        </div>

                        <div v-if="flags.length" class="space-y-4 max-h-150 overflow-y-auto pr-2">
                        <div
                            v-for="flag in flags"
                            :key="flag.id"
                            class="p-4 bg-slate-50 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors duration-150 group relative">
                            <!-- Header Line: Avatar + Name + Date + Menu -->
                            <div class="flex items-center justify-between gap-4 mb-2">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <!-- Avatar -->
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold shrink-0 bg-gray-200 text-gray-700">
                                {{ flag.created_by?.first_name?.charAt(0)?.toUpperCase() || 'T' }}
                                </span>

                                <!-- Name -->
                                <p class="font-medium text-slate-900 text-sm truncate">
                                {{ flag.created_by?.first_name || 'Team' }}
                                </p>
                            </div>

                            <!-- Date + Menu (always right-aligned) -->
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-xs text-slate-500 whitespace-nowrap">
                                {{ formattedRelativeDate(flag.created_at) }}
                                </span>

                                <Dropdown position="left" dropdownClasses="w-44">
                                <template #trigger="props">
                                    <EllipsisVertical
                                    size="14"
                                    @click="props.toggleDropdown"
                                    class="text-slate-400 opacity-60 group-hover:opacity-100 transition-opacity cursor-pointer"
                                    />
                                </template>
                                <template #content="props">
                                    <div class="py-1">
                                    <div
                                        v-for="(flagMenu, index) in flagMenus(flag)"
                                        :key="index"
                                        @click="(event) => { flagMenu.action(flag, () => props.toggleDropdown(event)); }"
                                        class="px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer"
                                    >
                                        {{ flagMenu.name }}
                                    </div>
                                    </div>
                                </template>
                                </Dropdown>
                            </div>
                            </div>

                            <!-- Second line: All pills – always on their own row -->
                            <div class="flex flex-wrap items-center gap-1.5 mb-2.5">
                            <!-- Priority (first – most important) -->
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-medium whitespace-nowrap"
                                    :class="{
                                    'bg-sky-100 text-sky-800': flag.priority === 'low',
                                    'bg-amber-100 text-amber-800': flag.priority === 'medium',
                                    'bg-orange-100 text-orange-800': flag.priority === 'high',
                                    'bg-rose-100 text-rose-800': flag.priority === 'critical'
                                    }">
                                {{ flag.priority }}
                            </span>

                            <!-- Category -->
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-medium max-w-[180px] truncate">
                                {{ flag.category }}
                            </span>

                            <span v-if="flag.ussd_session_step_id"
                                    class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                                <span>step {{ getStepNumber(flag.ussd_session_step_id) }}</span>
                            </span>

                            <!-- Resolved -->
                            <span v-if="flag.resolved"
                                    class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-medium whitespace-nowrap">
                                Resolved
                            </span>
                            </div>

                            <!-- Comment -->
                            <p class="text-sm text-slate-600 leading-relaxed line-clamp-2">
                            {{ flag.comment || 'No comment provided' }}
                            </p>

                            <!-- Resolved details (if resolved) -->
                            <div v-if="flag.status === 'resolved' && (flag.resolution_comment || flag.resolved_by)"
                                class="mt-2 pt-2 border-t border-slate-200 text-xs text-slate-600">
                            <p v-if="flag.resolution_comment" class="italic">
                                "{{ flag.resolution_comment }}"
                            </p>
                            <p class="mt-1">
                                Resolved by <span class="font-medium text-slate-800">
                                {{ flag.resolved_by?.first_name || 'Team' }}
                                </span>
                                • {{ formattedRelativeDate(flag.resolved_at) }}
                            </p>
                            </div>

                            <!-- Resolve button (if open) -->
                            <div v-if="flag.status === 'open'" class="mt-2 flex justify-end">
                            <Button
                                size="xs"
                                mode="solid"
                                type="success"
                                :loading="resolvingFlags.includes(flag.id)"
                                :action="() => showResolvableFlagModal(flag)"
                                buttonClass="rounded-lg px-4 py-1.5 text-xs"
                            >
                                Resolve
                            </Button>
                            </div>
                        </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <Modal
            size="md"
            ref="flagModal"
            :showFooter="false"
            :scrollOnContent="false"
            :approveAction="createFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Flag for Review
                </p>

                <!-- Step -->
                <Select
                    label="Step"
                    class="mb-4"
                    :search="true"
                    :clearable="true"
                    placeholder="Select one"
                    secondaryLabel="(optional)"
                    :options="stepSelectOptions"
                    v-model="form.ussd_session_step_id"
                    :errorText="formState.getFormError('ussd_session_step_id')" />

                <!-- Category -->
                <Select
                    class="mb-4"
                    :search="false"
                    label="Category"
                    :clearable="true"
                    :showAsterisk="true"
                    v-model="form.category"
                    placeholder="Select one"
                    :options="categoryOptions"
                    :errorText="formState.getFormError('category')" />

                <!-- Priority -->
                <div class="flex items-center text-sm leading-6 font-medium text-gray-900 space-x-1 mb-2">
                    <span>Priority</span>
                    <span class="text-red-500">*</span>
                </div>

                <div class="grid grid-cols-4 gap-3 mb-8">

                        <div
                            :key="option.value"
                            v-for="option in priorityOptions">

                            <Button
                                size="md"
                                loaderType="primary"
                                buttonClass="rounded-lg w-full"
                                :action="() => form.priority = option.value"
                                :type="form.priority == option.value ? 'primary' : 'light'"
                                :mode="form.priority == option.value ? 'solid' : 'outline'">
                                {{ option.label }}
                            </Button>

                        </div>

                    </div>

                <Input
                    rows="4"
                    class="mb-8"
                    type="textarea"
                    v-model="form.comment"
                    secondaryLabel="(optional)"
                    label="Comment / Explanation"
                    :errorText="formState.getFormError('comment')"
                    placeholder="What you noticed • What's wrong • Expected vs Actual" />


                <Button
                    size="md"
                    mode="solid"
                    class="mx-4"
                    type="warning"
                    :action="createFlag"
                    buttonClass="rounded-lg w-full">
                    Submit
                </Button>

            </template>

        </Modal>

        <Modal
            size="md"
            :showFooter="false"
            ref="editableFlagModal"
            :scrollOnContent="false"
            :approveAction="updateFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                Edit Flag
                </p>

                <!-- Original flag info (read-only) -->
                <div class="border-b border-gray-300 border-dashed pb-4 mb-6">
                <div class="flex items-center justify-between">
                    <p class="font-medium text-slate-900 truncate max-w-40">
                    {{ selectedFlag.created_by?.first_name || 'Team' }}
                    </p>
                    <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                    {{ formattedRelativeDate(selectedFlag.created_at) }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                    {{ selectedFlag.category }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                        :class="{
                            'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                            'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                            'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                            'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                        }">
                    {{ selectedFlag.priority }}
                    </span>
                    <span v-if="selectedFlag.ussd_session_step_id"
                        class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 font-medium whitespace-nowrap">
                    Step {{ getStepNumber(selectedFlag.ussd_session_step_id) }}
                    </span>
                </div>

                <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 mt-4">
                    "{{ selectedFlag.comment }}"
                </p>
                </div>

                <!-- Editable fields -->
                <div class="space-y-6">

                <!-- Step (optional) -->
                <Select
                    label="Step"
                    secondaryLabel="(optional)"
                    :search="true"
                    :clearable="true"
                    placeholder="Select step this flag refers to"
                    :options="stepSelectOptions"
                    v-model="editForm.ussd_session_step_id"
                    :errorText="formState.getFormError('ussd_session_step_id')"
                />

                <!-- Category -->
                <Select
                    label="Category"
                    :clearable="false"
                    :showAsterisk="true"
                    placeholder="Select category"
                    :options="categoryOptions"
                    v-model="editForm.category"
                    :errorText="formState.getFormError('category')"
                />

                <!-- Priority -->
                <div class="mb-2">
                    <label class="flex items-center text-sm font-medium text-gray-900 space-x-1">
                    <span>Priority</span>
                    <span class="text-red-500">*</span>
                    </label>
                </div>
                <div class="grid grid-cols-4 gap-3 mb-6">
                    <div v-for="option in priorityOptions" :key="option.value">
                    <Button
                        size="md"
                        loaderType="primary"
                        buttonClass="rounded-lg w-full"
                        :action="() => editForm.priority = option.value"
                        :type="editForm.priority === option.value ? 'primary' : 'light'"
                        :mode="editForm.priority === option.value ? 'solid' : 'outline'">
                        {{ option.label }}
                    </Button>
                    </div>
                </div>

                <!-- Comment -->
                <Input
                    rows="4"
                    type="textarea"
                    label="Comment / Explanation"
                    secondaryLabel="(optional)"
                    v-model="editForm.comment"
                    :errorText="formState.getFormError('comment')"
                    placeholder="Update your explanation if needed..."
                />

                </div>

                <Button
                size="md"
                mode="solid"
                type="primary"
                class="mt-8 mx-4"
                :action="updateFlag"
                buttonClass="rounded-lg w-full"
                :loading="updatingFlags.includes(selectedFlag.id)">
                Save Changes
                </Button>

            </template>

        </Modal>

        <Modal
            size="md"
            :showFooter="false"
            :scrollOnContent="false"
            ref="resolvableFlagModal"
            :approveAction="resolveFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Resolve Flag
                </p>

                <div class="border-b border-gray-300 border-dashed pb-4">

                    <div>

                        <div class="flex items-center justify-between">

                            <p class="font-medium text-slate-900 truncate max-w-40">
                                {{ selectedFlag.created_by?.first_name || 'Team' }}
                            </p>

                            <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                                {{ formattedRelativeDate(selectedFlag.created_at) }}
                            </span>

                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 mt-2">

                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                                {{ selectedFlag.category }}
                            </span>

                            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                :class="{
                                    'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                                    'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                                    'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                                    'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                                }">
                                {{ selectedFlag.priority }}
                            </span>

                            <span v-if="selectedFlag.resolved" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
                                Resolved
                            </span>

                        </div>

                    </div>

                    <!-- Comment -->
                    <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 leading-relaxed wrap-break-word mt-4">
                        {{ selectedFlag.comment }}
                    </p>

                </div>

                <Input
                    rows="4"
                    class="mt-8 mb-8"
                    type="textarea"
                    label="Comment"
                    secondaryLabel="(optional)"
                    v-model="resolutionForm.comment"
                    placeholder="Your resolution feedback..."
                    :errorText="formState.getFormError('comment')" />

                <Button
                    size="md"
                    mode="solid"
                    class="mx-4"
                    type="success"
                    :action="resolveFlag"
                    buttonClass="rounded-lg w-full"
                    :loading="resolvingFlags.includes(selectedFlag.id)">
                    Resolve
                </Button>

            </template>

        </Modal>

        <Modal
            size="md"
            :showFooter="false"
            :scrollOnContent="false"
            ref="unresolvableFlagModal"
            :approveAction="unresolveFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Unresolve Flag
                </p>

                <div class="border-b border-gray-300 border-dashed pb-4 mb-8">

                    <div>

                        <div class="flex items-center justify-between">

                            <p class="font-medium text-slate-900 truncate max-w-40">
                                {{ selectedFlag.created_by?.first_name || 'Team' }}
                            </p>

                            <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                                {{ formattedRelativeDate(selectedFlag.created_at) }}
                            </span>

                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 mt-2">

                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                                {{ selectedFlag.category }}
                            </span>

                            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                :class="{
                                    'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                                    'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                                    'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                                    'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                                }">
                                {{ selectedFlag.priority }}
                            </span>

                            <span v-if="selectedFlag.resolved" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
                                Resolved
                            </span>

                        </div>

                    </div>

                    <!-- Comment -->
                    <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 leading-relaxed wrap-break-word mt-4">
                        {{ selectedFlag.comment }}
                    </p>

                </div>

                <Button
                    size="md"
                    mode="solid"
                    class="mx-4"
                    type="warning"
                    :action="unresolveFlag"
                    buttonClass="rounded-lg w-full"
                    :loading="unresolvingFlags.includes(selectedFlag.id)">
                    Unresolve
                </Button>

            </template>

        </Modal>

        <Modal
            size="md"
            :showFooter="false"
            :scrollOnContent="false"
            ref="deletableFlagModal"
            :approveAction="unresolveFlag">

            <template #content>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">
                    Delete Flag
                </p>

                <div class="border-b border-gray-300 border-dashed pb-4 mb-8">

                    <div>

                        <div class="flex items-center justify-between">

                            <p class="font-medium text-slate-900 truncate max-w-40">
                                {{ selectedFlag.created_by?.first_name || 'Team' }}
                            </p>

                            <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto">
                                {{ formattedRelativeDate(selectedFlag.created_at) }}
                            </span>

                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 mt-2">

                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                                {{ selectedFlag.category }}
                            </span>

                            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                :class="{
                                    'bg-sky-100 text-sky-800': selectedFlag.priority === 'low',
                                    'bg-amber-100 text-amber-800': selectedFlag.priority === 'medium',
                                    'bg-orange-100 text-orange-800': selectedFlag.priority === 'high',
                                    'bg-rose-100 text-rose-800': selectedFlag.priority === 'critical'
                                }">
                                {{ selectedFlag.priority }}
                            </span>

                            <span v-if="selectedFlag.resolved" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap">
                                Resolved
                            </span>

                        </div>

                    </div>

                    <!-- Comment -->
                    <p v-if="selectedFlag.comment" class="italic text-sm text-slate-700 leading-relaxed wrap-break-word mt-4">
                        {{ selectedFlag.comment }}
                    </p>

                </div>

                <Button
                    size="md"
                    mode="solid"
                    class="mx-4"
                    type="danger"
                    :action="deleteFlag"
                    buttonClass="rounded-lg w-full"
                    :loading="deletingFlags.includes(selectedFlag.id)">
                    Delete
                </Button>

            </template>

        </Modal>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import Select from '@Partials/Select.vue';
    import countries from '@Json/countries.json';
    import Dropdown from '@Partials/Dropdown.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedRelativeDate } from '@Utils/dateUtils.js';
    import { isEmpty, isNotEmpty, formatDuration } from '@Utils/stringUtils';
    import { X, Copy, Plus, Frown, Timer, FileText, ArrowLeft, ArrowRight, FlagTriangleRight, EllipsisVertical } from 'lucide-vue-next';

    export default {
        inject: ['authState', 'appState', 'formState', 'notificationState'],
        components: { X, Frown, Timer, FileText, EllipsisVertical, Input, Modal, Button, Select, Dropdown, Skeleton },
        data() {
            return {
                Copy,
                Plus,
                flags: [],
                ArrowLeft,
                ArrowRight,
                session: null,
                FlagTriangleRight,
                updatingFlags: [],
                deletingFlags: [],
                resolvingFlags: [],
                selectedFlag: null,
                unresolvingFlags: [],
                flagsPagination: null,
                isCreatingFlag: false,
                isLoadingFlags: false,
                showErrorBanner: true,
                isLoadingSession: false,
                isLoadingSessionFlags: false,
                form: {
                    comment: '',
                    category: null,
                    priority: 'low',
                    ussd_session_step_id: null
                },
                editForm: {
                    ussd_session_step_id: null,
                    category: null,
                    priority: 'low',
                    comment: '',
                },
                resolutionForm: {
                    comment: ''
                },
                priorityOptions: [
                    { value: 'low', label: 'Low' },
                    { value: 'medium', label: 'Medium' },
                    { value: 'high', label: 'High' },
                    { value: 'critical', label: 'Critical' },
                ],
                categoryOptions: [
                    { value: 'bug', label: 'Bug / Technical Error' },
                    { value: 'ux', label: 'UX / Flow Confusion' },
                    { value: 'content', label: 'Menu Text / Wording Issue' },
                    { value: 'performance', label: 'Performance / Slow Response' },
                    { value: 'security', label: 'Security / Compliance Concern' },
                    { value: 'feature-request', label: 'Feature Suggestion / Improvement' },
                    { value: 'other', label: 'Other' },
                ]
            }
        },
        computed: {
            app() {
                return this.appState.app;
            },
            authUser() {
                return this.authState.user;
            },
            steps() {
                return this.session?.steps ?? [];
            },
            sessionId() {
                return this.$route.params.session_id;
            },
            countryName() {
                if(this.session.country) {
                    return countries.find(c => c.iso === this.session.country).name;
                }
                return null;
            },
            maxWaitTimeMs() {
                if (!this.session?.steps?.length) return 0;
                return Math.max(...this.session.steps.map(s => Number(s.total_duration_ms) || 0));
            },
            stepSelectOptions() {
                if (!this.session?.steps?.length) return [];

                return this.session.steps.map((step, index) => ({
                    value: step.id,
                    label: `Step ${index + 1} — ${step.step_title || 'Unnamed'}`,
                }));
            },
        },
        methods: {
            isEmpty,
            isNotEmpty,
            formatDuration,
            formattedRelativeDate,
            async navigateToAccount() {
                await this.$router.push({
                    name: 'show-app-account',
                    params: {
                        app_id: this.app.id,
                        account_id: this.session.ussd_account_id
                    }
                });
            },
            flagMenus(flag) {

                let menus =  [];

                if (flag.created_by?.id === this.authUser.id) {
                    menus.push({
                        name: 'Edit',
                        action: this.showEditableFlagModal
                    });
                }

                if(flag.resolved) {

                    menus.push({
                        name: 'Unresolve',
                        action: this.showUnresolvableFlagModal
                    });

                }else{

                    menus.push({
                        name: 'Resolve',
                        action: this.showResolvableFlagModal
                    });

                }

                menus.push({
                    name: 'Delete',
                    action: this.showDeletableFlagModal
                });

                return menus;

            },
            getStepNumber(stepId) {
                if (!this.session?.steps?.length || !stepId) return '?';

                const index = this.session.steps.findIndex(step => step.id === stepId);
                return index !== -1 ? index + 1 : '?';
            },
            isLongestWait(step, steps) {
                if (steps?.length >= 0 || !step?.total_duration_ms) return false;
                const max = Math.max(...steps.map(s => Number(s.total_duration_ms) || 0));
                return Number(step.total_duration_ms) === max && max > 0;
            },
            showFlagModal() {
                this.$refs.flagModal.showModal();
            },
            hideFlagModal() {
                this.$refs.flagModal.hideModal();
            },
            showEditableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;

                this.editForm = {
                    category: flag.category,
                    priority: flag.priority,
                    comment: flag.comment || '',
                    ussd_session_step_id: flag.ussd_session_step_id || null,
                };

                if (toggleDropdown) toggleDropdown();
                this.$refs.editableFlagModal.showModal();
            },
            hideEditableFlagModal() {
                this.$refs.editableFlagModal.hideModal();
            },
            showResolvableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;
                if(toggleDropdown) toggleDropdown();
                this.$refs.resolvableFlagModal.showModal();
            },
            hideResolvableFlagModal() {
                this.$refs.resolvableFlagModal.hideModal();
            },
            showUnresolvableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;
                if(toggleDropdown) toggleDropdown();
                this.$refs.unresolvableFlagModal.showModal();
            },
            hideUnresolvableFlagModal() {
                this.$refs.unresolvableFlagModal.hideModal();
            },
            showDeletableFlagModal(flag, toggleDropdown = null) {
                this.selectedFlag = flag;
                if(toggleDropdown) toggleDropdown();
                this.$refs.deletableFlagModal.showModal();
            },
            hideDeletableFlagModal() {
                this.$refs.deletableFlagModal.hideModal();
            },
            flagThisStep(step) {
                this.form.ussd_session_step_id = step.id;
                this.showFlagModal();
            },
            async createFlag() {

                try {

                    if(this.isCreatingFlag) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.form.priority)) {
                        this.formState.setFormError('priority', 'Select your priority');
                    }

                    if(this.isEmpty(this.form.category)) {
                        this.formState.setFormError('category', 'Select your category');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.isCreatingFlag = true;

                    let data = {
                        app_id: this.app.id,
                        priority: this.form.priority,
                        category: this.form.category,
                        ussd_session_id: this.sessionId
                    };

                    if(this.isNotEmpty(this.form.comment)) {
                        data.comment = this.form.comment;
                    }

                    if(this.isNotEmpty(this.form.ussd_session_step_id)) {
                        data.ussd_session_step_id = this.form.ussd_session_step_id;
                    }

                    const response = await axios.post('/api/ussd-session-flags', data);

                    const flag = response.data.ussd_session_flag;
                    this.flagsPagination.meta.total += 1;

                    this.flags.shift(flag);
                    this.notificationState.showSuccessNotification('Flag created!');

                    this.showFlags();
                    this.hideFlagModal();

                    this.form.comment = '';
                    this.form.category = null;
                    this.form.priority = 'low';

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while creating flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to create flag:', error);
                } finally {
                    this.isCreatingFlag = false;
                }
            },
            async updateFlag() {

                const flagId = this.selectedFlag.id;

                if (this.updatingFlags.includes(flagId)) return;

                this.updatingFlags.push(flagId);

                try {

                    this.formState.hideFormErrors();

                    if (this.isEmpty(this.editForm.category)) {
                        this.formState.setFormError('category', 'Category is required');
                    }

                    if (this.isEmpty(this.editForm.priority)) {
                        this.formState.setFormError('priority', 'Priority is required');
                    }

                    if (this.formState.hasErrors) return;

                    const data = {
                        app_id: this.app.id,
                        category: this.editForm.category,
                        priority: this.editForm.priority,
                        comment: this.editForm.comment.trim() || null,
                        ussd_session_step_id: this.editForm.ussd_session_step_id,
                    };

                    const response = await axios.put(`/api/ussd-session-flags/${flagId}`, data);

                    const updatedFlag = response.data.ussd_session_flag;

                    this.notificationState.showSuccessNotification('Flag updated!');

                    const index = this.flags.findIndex(f => f.id === flagId);

                    if (index !== -1) {
                        this.flags[index] = {
                            ...this.flags[index],
                            ...updatedFlag
                        };
                    }

                    this.$refs.editableFlagModal.hideModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update flag:', error);
                } finally {
                    const index = this.updatingFlags.indexOf(flagId);
                    if (index !== -1) {
                        this.updatingFlags.splice(index, 1);
                    }
                }
            },
            async resolveFlag() {

                const flagId = this.selectedFlag.id;

                if (this.resolvingFlags.includes(flagId)) return;

                this.resolvingFlags.push(flagId);

                try {

                    const data = {
                        app_id: this.app.id,
                        resolution_comment: this.resolutionForm.comment
                    };

                    const response = await axios.post(`/api/ussd-session-flags/${flagId}/resolve`, data);

                    const resolvedFlag = response.data.ussd_session_flag;

                    this.notificationState.showSuccessNotification('Flag resolved!');

                    const index = this.flags.findIndex(f => f.id === flagId);

                    if (index !== -1) {
                        this.flags[index] = {
                            ...this.flags[index],
                            ...resolvedFlag,
                            resolved_by: this.authUser
                        };
                    }

                    this.showFlags();
                    this.hideResolvableFlagModal();

                    this.resolutionForm.comment = '';

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while resolving flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to resolve flag:', error);
                } finally {
                    const index = this.resolvingFlags.indexOf(flagId);

                    if (index !== -1) {
                        this.resolvingFlags.splice(index, 1);
                    }
                }
            },
            async unresolveFlag() {

                const flagId = this.selectedFlag.id;

                if (this.unresolvingFlags.includes(flagId)) return;

                this.unresolvingFlags.push(flagId);

                try {

                    const data = {
                        app_id: this.app.id
                    };

                    const response = await axios.post(`/api/ussd-session-flags/${flagId}/unresolve`, data);

                    const unresolvedFlag = response.data.ussd_session_flag;

                    this.notificationState.showSuccessNotification('Flag unresolved!');

                    const index = this.flags.findIndex(f => f.id === flagId);

                    if (index !== -1) {
                        this.flags[index] = {
                            ...this.flags[index],
                            ...unresolvedFlag
                        };
                    }

                    this.showFlags();
                    this.hideUnresolvableFlagModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while unresolving flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to unresolve flag:', error);
                } finally {
                    const index = this.unresolvingFlags.indexOf(flagId);

                    if (index !== -1) {
                        this.unresolvingFlags.splice(index, 1);
                    }
                }
            },
            async deleteFlag() {

                const flagId = this.selectedFlag.id;

                if (this.deletingFlags.includes(flagId)) return;

                this.deletingFlags.push(flagId);

                try {

                    const config = {
                        data: {
                            app_id: this.app.id
                        }
                    }

                    await axios.delete(`/api/ussd-session-flags/${flagId}`, config);

                    const index = this.flags.findIndex(f => f.id === flagId);

                    this.flags.splice(index, 1)
                    this.flagsPagination.meta.total -= 1;

                    this.notificationState.showSuccessNotification('Flag deleted!');

                    this.hideDeletableFlagModal();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while deleting flag';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to delete flag:', error);
                } finally {
                    const index = this.deletingFlags.indexOf(flagId);

                    if (index !== -1) {
                        this.deletingFlags.splice(index, 1);
                    }
                }
            },
            async showFlags(page = null) {
                try {

                    this.isLoadingSessionFlags = true;

                    let config = {
                        params: {
                            per_page: 50,
                            app_id: this.app.id,
                            ussd_session_id: this.sessionId,
                            _relationships: ['createdBy', 'resolvedBy'].join(',')
                        },
                    };

                    if (isNotEmpty(page)) {
                        params.page = page;
                    }

                    const response = await axios.get('/api/ussd-session-flags', config);

                    this.flagsPagination = response.data;
                    this.flags = this.flagsPagination.data || [];

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching flags';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch flags:', error);
                } finally {
                    this.isLoadingSessionFlags = false;
                }
            },
            async showSession() {
                try {

                    this.isLoadingSession = true;

                    let config = {
                        params: {
                            app_id: this.app.id,
                            _relationships: ['steps'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/ussd-sessions/${this.sessionId}`, config);

                    this.session = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching session';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch session:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-app-sessions',
                            params: {
                                app_id: this.app.id
                            }
                        });
                    }

                } finally {
                    this.isLoadingSession = false;
                }
            }
        },
        created() {
            this.showSession();
            this.showFlags();
        }
    };

</script>


<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>
