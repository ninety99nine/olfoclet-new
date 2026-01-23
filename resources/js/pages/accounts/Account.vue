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
                                Account Details
                            </h1>
                            <nav class="flex mt-2" aria-label="Breadcrumb">
                                <ol class="flex items-center space-x-2 text-sm text-slate-500">
                                    <li>
                                        <router-link :to="{ name: 'show-app-accounts' }" class="hover:text-slate-900 hover:underline">Accounts</router-link>
                                    </li>
                                    <li>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </li>
                                    <li class="font-medium">{{ account?.id || 'Loading...' }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <span
                        v-if="account"
                        class="inline-flex px-3.5 py-1.5 text-sm font-medium rounded-full"
                        :class="[
                            'inline-flex px-3.5 py-1.5 text-sm font-medium rounded-full',
                            {
                                'bg-emerald-100 text-emerald-700': account.status === 'Active',
                                'bg-amber-100 text-amber-700': account.status === 'Inactive',
                                'bg-rose-100 text-rose-700': account.status === 'Blocked',
                            }
                        ]">
                        {{ account.status || 'Unknown' }}
                    </span>

                </div>

            </div>

        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 py-4">

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-4">

                <!-- Card 1: Total Sessions + Success Rate -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm text-slate-500 font-medium">Total Sessions</p>
                    <Skeleton v-if="isLoadingAccount" width="w-20" class="shrink-0 mt-2 h-10"></Skeleton>
                    <p v-else class="text-4xl font-bold text-slate-900 mt-2">
                    {{ accountSummary?.total_sessions ?? 0 }}
                    </p>
                    <div class="mt-4 flex items-center gap-3">
                    <span
                        :class="[
                            'text-xl font-bold',
                            accountSummary?.success_rate >= 90
                                ? 'text-emerald-600'
                                : accountSummary?.success_rate >= 80
                                    ? 'text-amber-600'
                                    : 'text-rose-600'
                        ]">
                        {{ accountSummary?.success_rate ?? '—' }}%
                    </span>
                    <span
                        :class="[
                            'text-xs px-2.5 py-1 rounded-full font-medium',
                            accountSummary?.success_rate >= 90
                                ? 'text-emerald-700 bg-emerald-50'
                                : accountSummary?.success_rate >= 80
                                    ? 'text-amber-700 bg-amber-50'
                                    : 'text-rose-700 bg-rose-50'
                        ]">
                        Success Rate
                    </span>
                    </div>
                </div>

                <!-- Card 2: First Seen + Last Activity -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm text-slate-500 font-medium">First Seen</p>
                    <Skeleton v-if="isLoadingAccount" width="w-32" class="shrink-0 mt-2 h-10"></Skeleton>
                    <p v-else class="text-3xl font-bold text-slate-900 mt-2">
                    {{ accountSummary?.first_seen || '—' }}
                    </p>
                    <p class="text-sm text-slate-500 mt-2">
                    Last Activity: <strong>{{ accountSummary?.last_activity || '—' }}</strong>
                    </p>
                </div>

                <!-- Card 3: Avg Session Duration + Avg Steps -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm text-slate-500 font-medium">Avg Session Duration</p>
                    <Skeleton v-if="isLoadingAccount" width="w-24" class="shrink-0 mt-2 h-10"></Skeleton>
                    <p v-else class="text-4xl font-bold text-slate-900 mt-2">
                    {{ formatDuration(accountSummary?.avg_duration_ms) || '—' }}
                    </p>
                    <p class="text-sm text-slate-500 mt-2">
                    Avg Steps: <strong>{{ accountSummary?.avg_steps ?? '—' }}</strong>
                    </p>
                </div>

                <!-- Card 4: Device Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm text-slate-500 font-medium">Device Breakdown</p>
                    <Skeleton v-if="isLoadingAccount" class="mt-4 h-20"></Skeleton>
                    <div v-else class="mt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Mobile</span>
                            <span class="font-medium">{{ accountSummary?.mobile_percentage ?? '—' }}%</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div
                            class="h-full bg-indigo-600 rounded-full transition-all duration-500"
                            :style="{ width: (accountSummary?.mobile_percentage || 0) + '%' }"
                            ></div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Simulator</span>
                            <span class="font-medium">{{ accountSummary?.simulator_percentage ?? '—' }}%</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div
                            class="h-full bg-amber-600 rounded-full transition-all duration-500"
                            :style="{ width: (accountSummary?.simulator_percentage || 0) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Loading State -->
            <div v-if="isLoadingAccount" class="p-12 text-center text-slate-500">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                <span class="text-sm">Loading sessions...</span>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- Left Column -->
                <div class="lg:col-span-8 space-y-4">

                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">

                        <div class="px-6 py-5 border-b border-slate-100">
                            <h2 class="text-lg font-semibold text-slate-900">
                                Last 3 Sessions
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                The final output the user saw in their last 3 sessions
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100">

                            <div
                                class="p-6 hover:bg-slate-50 transition-colors"
                                v-for="(session, index) in account.last_few_sessions" :key="session.id">

                                <div class="flex items-start justify-between gap-6">

                                    <!-- Left: Session info + last screen -->
                                    <div class="flex-1 min-w-0">

                                        <div class="flex items-center justify-between mb-3">

                                            <div class="flex items-center gap-3">

                                                <div class="shrink-0 w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-medium text-slate-600 text-lg">
                                                    {{ index + 1 }}
                                                </div>

                                                <div>
                                                    <p class="font-medium text-slate-900">
                                                        {{ formattedRelativeDate(session.created_at) || '—' }}
                                                    <span class="ml-2 text-sm text-slate-500">({{ formatDuration(session.total_duration_ms) || '—' }})</span>
                                                    </p>
                                                    <p class="text-xs text-slate-500 mt-0.5">
                                                        {{ session.successful ? 'Successful' : 'Failed' }} •
                                                        {{ session.total_steps }} {{ session.total_steps == 1 ? 'step' : 'steps' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Right: Three actions – now INLINE (horizontal) -->
                                            <div class="flex items-center gap-2 flex-wrap mt-1">

                                                <!-- Flag button – exact style you requested -->
                                                <Button
                                                    size="xs"
                                                    mode="solid"
                                                    type="warning"
                                                    :leftIcon="FlagTriangleRight"
                                                    buttonClass="rounded-lg shadow-sm"
                                                    :action="() => flagThisSession(session)">
                                                    <span class="ml-1">Flag</span>
                                                </Button>

                                                <!-- View full session -->
                                                <Button
                                                    size="xs"
                                                    mode="solid"
                                                    type="light"
                                                    :rightIcon="ArrowRight"
                                                    buttonClass="rounded-lg shadow-sm"
                                                    :action="() => navigateToSession(session)">
                                                    <span class="mr-1">View session</span>
                                                </Button>

                                            </div>

                                        </div>

                                        <!-- The last screen output -->
                                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 font-mono text-sm text-slate-800 whitespace-pre-wrap leading-relaxed mt-4">
                                            {{ session.last_step_content || '[ No screen content recorded ]' }}
                                        </div>

                                        <!-- Error message if failed -->
                                        <div v-if="!session.successful && session.error_message"
                                            class="w-fit mt-3 text-sm text-rose-700 bg-rose-50 py-2 px-3 rounded border border-rose-200">
                                            {{ session.error_message }}
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- Empty state -->
                            <div v-if="!account.last_few_sessions.length" class="p-12 text-center text-slate-500">
                                <Frown size="40" class="mx-auto mb-4"></Frown>
                                <p class="text-lg font-medium">No sessions found</p>
                                <p class="mt-2">This account may not have completed any sessions yet.</p>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Right Column -->
                <div class="lg:col-span-4 space-y-4">

                    <!-- Quick Actions -->
                    <div
                        class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900 mb-5">Quick Actions</h3>

                        <Button
                            size="md"
                            type="light"
                            mode="outline"
                            class="w-full mb-4"
                            :leftIcon="Copy"
                            :skeleton="isLoadingAccount"
                            :copyContent="account?.msisdn"
                            buttonClass="w-full rounded-lg">
                            <span class="ml-1">Copy Phone Number</span>
                        </Button>

                        <Button
                            size="md"
                            type="success"
                            mode="outline"
                            class="w-full"
                            :leftIcon="RotateCcw"
                            v-if="account.blocked"
                            :action="unblockAccount"
                            :skeleton="isUnblockingAccount"
                            buttonClass="w-full rounded-lg">
                            <span class="ml-1">Unblock Account</span>
                        </Button>

                        <Button
                            v-else
                            size="md"
                            type="danger"
                            mode="outline"
                            class="w-full"
                            :leftIcon="Ban"
                            :action="blockAccount"
                            :skeleton="isBlockingAccount"
                            buttonClass="w-full rounded-lg">
                            <span class="ml-1">Block Account</span>
                        </Button>

                    </div>

                    <!-- Flags Section -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">

                        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">

                            <h3 class="text-lg font-semibold text-slate-900">Recent Flags</h3>

                            <div class="shrink-0 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 text-xs font-medium">
                                {{ flagsflagsPagination?.meta?.total }}
                            </div>

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
                    :search="true"
                    :clearable="true"
                    secondaryLabel="(optional)"
                    :options="stepSelectOptions"
                    v-model="editForm.ussd_session_step_id"
                    placeholder="Select step this flag refers to"
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
    import Dropdown from '@Partials/Dropdown.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import { formattedRelativeDate } from '@Utils/dateUtils.js';
    import { isEmpty, isNotEmpty, formatDuration } from '@Utils/stringUtils';
    import { X, Ban, Copy, Plus, Frown, Timer, RotateCcw, FileText, ArrowLeft, ArrowRight, FlagTriangleRight, EllipsisVertical } from 'lucide-vue-next';

    export default {
        inject: ['authState', 'appState', 'formState', 'notificationState'],
        components: { X, Frown, Timer, FileText, EllipsisVertical, Input, Modal, Button, Select, Dropdown, Skeleton },
        data() {
            return {
                Ban,
                Copy,
                Plus,
                RotateCcw,
                flags: [],
                ArrowLeft,
                ArrowRight,
                account: null,
                FlagTriangleRight,
                updatingFlags: [],
                deletingFlags: [],
                resolvingFlags: [],
                selectedFlag: null,
                accountSummary: null,
                selectedSessions: [],
                unresolvingFlags: [],
                stepSelectOptions: [],
                isCreatingFlag: false,
                isLoadingFlags: false,
                isLoadingAccount: false,
                isBlockingAccount: false,
                isUnblockingAccount: false,
                flagsflagsPagination: null,
                isLoadingSessionFlags: true,
                isLoadingAccountSummary: true,
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
            accountId() {
                return this.$route.params.account_id;
            }
        },
        methods: {
            isEmpty,
            isNotEmpty,
            formatDuration,
            formattedRelativeDate,
            async navigateToSession(session) {
                await this.$router.push({
                    name: 'show-app-session',
                    params: {
                        app_id: this.app.id,
                        session_id: session.id
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
            flagThisSession(session) {
                this.setStepSelectOptions(session);
                this.form.ussd_session_step_id = session.steps[session.steps.length - 1].id;
                this.showFlagModal();
            },
            setStepSelectOptions(session) {
                if (!session?.steps?.length) return [];

                this.stepSelectOptions = session.steps.map((step, index) => ({
                    value: step.id,
                    label: `Step ${index + 1} — ${step.step_title || 'Unnamed'}`,
                }));
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
                        ussd_session_id: this.accountId
                    };

                    if(this.isNotEmpty(this.form.comment)) {
                        data.comment = this.form.comment;
                    }

                    if(this.isNotEmpty(this.form.ussd_session_step_id)) {
                        data.ussd_session_step_id = this.form.ussd_session_step_id;
                    }

                    const response = await axios.post('/api/ussd-session-flags', data);

                    const flag = response.data.ussd_session_flag;
                    this.flagsflagsPagination.meta.total += 1;

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
                        app_id: this.app.id
                    };

                    if(isNotEmpty(this.resolutionForm.comment)) {
                        data.resolution_comment = this.resolutionForm.comment;
                    }

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
                    this.flagsflagsPagination.meta.total -= 1;

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
                            ussd_account_id: this.accountId,
                            _relationships: ['createdBy', 'resolvedBy'].join(',')
                        },
                    };

                    if (isNotEmpty(page)) {
                        params.page = page;
                    }

                    const response = await axios.get('/api/ussd-session-flags', config);

                    this.flagsflagsPagination = response.data;
                    this.flags = this.flagsflagsPagination.data || [];

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching flags';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch flags:', error);
                } finally {
                    this.isLoadingSessionFlags = false;
                }
            },
            async showAccount() {
                try {

                    this.isLoadingAccount = true;

                    let config = {
                        params: {
                            app_id: this.app.id,
                            _relationships: ['lastFewSessions.steps'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/ussd-accounts/${this.accountId}`, config);

                    this.account = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching account';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch account:', error);

                    if (error.response?.status === 404) {
                        await this.$router.replace({
                            name: 'show-app-accounts',
                            params: {
                                app_id: this.app.id
                            }
                        });
                    }

                } finally {
                    this.isLoadingAccount = false;
                }
            },
            async showAccountSummary() {
                try {

                    this.isLoadingAccountSummary = true;

                    let config = {
                        params: {
                            app_id: this.app.id,
                        },
                    };

                    const response = await axios.get(`/api/ussd-accounts/${this.accountId}/summary`, config);

                    this.accountSummary = response.data;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching account summary';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch account summary:', error);

                } finally {
                    this.isLoadingAccountSummary = false;
                }
            },
            async blockAccount() {

                try {

                    this.isBlockingAccount = true;

                    const data = {
                        app_id: this.app.id
                    };

                    const response = await axios.post(`/api/ussd-accounts/${this.accountId}/block`, data);

                    this.account = response.data.ussd_account;

                    this.notificationState.showSuccessNotification('Account blocked!');

                    this.showAccount();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while blocking account';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to blocking account:', error);
                } finally {
                    this.isBlockingAccount = false;
                }
            },
            async unblockAccount() {

                try {

                    this.isUnblockingAccount = true;

                    const data = {
                        app_id: this.app.id
                    };

                    const response = await axios.post(`/api/ussd-accounts/${this.accountId}/unblock`, data);

                    this.account = response.data.ussd_account;

                    this.notificationState.showSuccessNotification('Account unblocked!');

                    this.showAccount();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while unblocking account';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to unblocking account:', error);
                } finally {
                    this.isUnblockingAccount = false;
                }
            },
        },
        created() {
            this.showAccountSummary();
            this.showAccount();
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
