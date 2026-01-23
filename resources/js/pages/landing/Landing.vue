<template>

    <div class="relative bg-linear-to-b from-slate-50 via-white to-slate-100 min-h-screen">

        <!-- Floating gradient blobs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-400/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-400/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Navigation -->
        <nav class="fixed top-0 inset-x-0 z-50 bg-white/70 backdrop-blur-lg border-b border-slate-200/60">

            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 flex items-center justify-between">

                <Logo height="h-9" />

                <div class="flex items-center gap-4 sm:gap-6">

                    <a
                        href="#faqs"
                        @click.prevent="scrollToFaqs"
                        class="text-sm font-medium text-slate-700 hover:text-slate-900">
                        FAQs
                    </a>

                    <a
                        href="#contact"
                        @click.prevent="scrollToContact"
                        class="text-sm font-medium text-slate-700 hover:text-slate-900">
                        Contact Us
                    </a>

                    <Button :action="navigateToLogin" type="gradient" buttonClass="shadow-lg rounded-full">
                        Sign In
                    </Button>

                </div>

            </div>

        </nav>

        <main>

            <!-- Section 1: Hero -->
            <section class="max-w-7xl mx-auto px-6 pt-20 pb-20">

                <div class="grid lg:grid-cols-2 gap-0 lg:gap-16 h-full">

                    <!-- LEFT: Text + CTAs -->
                    <div class="text-center lg:text-left lg:pt-40 order-2 lg:order-1">

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold tracking-tight leading-tight">
                            <span class="block">Build Enterprise</span>
                            <span class="block bg-linear-to-r from-indigo-600 via-purple-600 to-indigo-700 bg-clip-text text-transparent">
                                USSD Solutions
                            </span>
                        </h1>

                        <p class="mt-6 text-lg md:text-xl lg:text-2xl text-slate-700 max-w-xl lg:mx-0 mx-auto">
                            The modern visual USSD platform for Africa. Create, test, and deploy powerful,
                            mission-critical applications designed for scale, security, and continuous availability.
                        </p>

                        <div class="mt-10 flex gap-5 justify-center lg:justify-start">

                            <Button :action="navigateToLogin" type="gradient" buttonClass="shadow-lg rounded-full">
                                Sign In
                            </Button>

                            <Button :action="scrollToContact" type="white" mode="solid" buttonClass="shadow-lg rounded-full">
                                Contact Us
                            </Button>

                        </div>

                    </div>

                    <!-- RIGHT: Image -->
                    <div class="relative flex items-end order-1 lg:order-2">

                        <img :src="'/images/agent.png'" class="scale-100 lg:scale-120 lg:-ml-8 lg:max-w-full lg:mx-0 max-w-96 mx-auto" alt="Lady phone with USSD menu" />

                    </div>

                </div>

            </section>

            <!-- Section 2: Visual Builder -->
            <section class="lg:max-w-7xl px-10 lg:px-0 mx-auto space-y-20">

                <!-- Image -->
                <div class="relative rounded-xl overflow-hidden shadow-md/30 shadow-indigo-200 border border-gray-200">

                    <img
                        class="w-full h-auto"
                        :src="'/images/telcoflo.png'"
                        alt="Telcoflo USSD Editor Interface"
                    />

                </div>

            </section>

            <!-- Section 3: Mission -->
            <section class="pt-20 text-center">

                <div class="max-w-4xl mx-auto px-6 lg:px-8">

                    <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 mb-6">
                        Our Mission
                    </h2>

                    <p class="text-lg md:text-xl lg:text-2xl text-slate-700 mb-16">
                        Telcoflo is the modern wrapper for legacy USSD — It lets governments, banks, telcos and businesses maintain reliable access for millions while freeing their teams to innovate on next-gen channels
                    </p>

                </div>

            </section>

            <!-- Section 4: Core Value Propositions -->
            <section class="lg:px-8 pt-4 pb-4 lg:pt-8 lg:pb-8 max-w-7xl mx-auto px-6 space-y-20">

                <div class="flex flex-col items-center gap-12 lg:grid lg:grid-cols-2 lg:items-center lg:gap-16">

                    <!-- Left: Text -->
                    <div class="flex flex-col items-center text-center lg:text-left lg:block">

                        <div class="inline-block mb-8">
                            <span class="bg-indigo-100 text-indigo-800 text-sm font-medium px-4 py-1.5 rounded-full">
                                Intuitive Interface
                            </span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-gray-900">
                            Build Powerful USSD Applications
                            <span class="block mt-2 text-indigo-600 text-3xl sm:text-4xl lg:text-5xl">Without Fighting Code</span>
                        </h2>

                        <p class="mt-6 text-md md:text-lg text-gray-600 leading-relaxed max-w-xl">
                            Our purpose-built visual editor lets you create complex, enterprise-grade USSD services
                            with clarity and speed — no XML nightmares, no confusing flow-chart spaghetti.
                        </p>

                        <ul class="mt-8 space-y-4 px-4 md:px-0">

                            <li class="flex md:items-center gap-2 md:gap-3 lg:gap-4 items-center">
                                <div class="shrink-0 w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full bg-indigo-100 flex items-center justify-center mt-0.5">
                                    <svg class="w-2 h-2 md:w-3 md:h-3 lg:w-4 lg:h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-700 text-md lg:text-lg text-left">Structured step-by-step interface designed for USSD logic</span>
                            </li>

                            <li class="flex md:items-center gap-2 md:gap-3 lg:gap-4 items-center">
                                <div class="shrink-0 w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full bg-indigo-100 flex items-center justify-center mt-0.5">
                                    <svg class="w-2 h-2 md:w-3 md:h-3 lg:w-4 lg:h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-700 text-md lg:text-lg text-left">Variables, conditions, validations and API calls — all visual</span>
                            </li>

                            <li class="flex md:items-center gap-2 md:gap-3 lg:gap-4 items-center">
                                <div class="shrink-0 w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full bg-indigo-100 flex items-center justify-center mt-0.5">
                                    <svg class="w-2 h-2 md:w-3 md:h-3 lg:w-4 lg:h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-gray-700 text-md lg:text-lg text-left">Instant preview of menu structure and navigation flow</span>
                            </li>

                        </ul>

                    </div>

                    <!-- Right: Image -->
                    <div class="w-full md:w-2/3 lg:w-full rounded-2xl overflow-hidden shadow-2xl shadow-indigo-200/30 border border-gray-200">

                        <img
                            class="w-full h-auto"
                            :src="'/images/telcoflo3.png'"
                            alt="Telcoflo Visual USSD Editor Interface"
                        />

                    </div>

                </div>

            </section>

            <!-- Section 5: API Integrations -->
            <section class="lg:px-8 pt-12 pb-10 lg:pt-12 lg:pb-8">

                <div class="max-w-7xl mx-auto px-6 lg:px-8">

                    <div class="flex flex-col items-center gap-12 lg:grid lg:grid-cols-2 lg:items-center lg:gap-16 pb-6 md:pb-10">

                        <!-- Image right -->
                        <div class="order-2 lg:order-1 w-full md:w-2/3 lg:w-full rounded-3xl overflow-hidden shadow-2xl shadow-purple-200/30 border border-gray-200">
                            <img
                                class="w-full h-auto"
                                :src="'/images/telcoflo5.png'"
                                alt="Telcoflo API integration panel in visual editor"
                            />
                        </div>

                        <!-- Text left -->
                        <div class="order-1 lg:order-2 text-center lg:text-left">

                            <div class="inline-block mb-6">
                                <span class="bg-purple-100 text-purple-800 text-sm font-medium px-4 py-1.5 rounded-full">
                                    Seamless Integrations
                                </span>
                            </div>

                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-gray-900">
                                Connect to Your World
                                <span class="block mt-2 text-purple-600 text-3xl sm:text-4xl lg:text-5xl">Through Powerful APIs</span>
                            </h2>

                            <p class="mt-6 text-md md:text-lg text-gray-600 leading-relaxed max-w-xl">
                                Bring external data, payment gateways, CRM systems, notification services
                                and any modern API directly into your USSD flows — all without leaving the editor.
                            </p>

                        </div>

                    </div>

                    <!-- Four feature cards -->
                     <div class="flex flex-col items-center">

                        <div class="w-full md:w-2/3 lg:w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-8 lg:gap-10">

                            <!-- REST API -->
                            <div class="flex flex-col items-center bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">

                                <div class="w-8 h-8 md:w-10 md:h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-4">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>

                                <h4 class="font-semibold text-gray-900 text-md md:text-lg text-center mb-2">REST API</h4>
                                <p class="max-w-72 md:max-w-full text-gray-600 text-center text-xs md:text-sm leading-relaxed">
                                    GET, POST, PUT, DELETE to fetch data, update records and process actions in real-time
                                </p>

                            </div>

                            <!-- GraphQL API -->
                            <div class="flex flex-col items-center bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">

                                <div class="w-8 h-8 md:w-10 md:h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-4">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <h4 class="font-semibold text-gray-900 text-md md:text-lg text-center mb-2">GraphQL API</h4>
                                <p class="max-w-72 md:max-w-full text-gray-600 text-center text-xs md:text-sm leading-relaxed">
                                    Ask for exactly the data you need for efficient, precise, single request queries
                                </p>

                            </div>

                            <!-- Secure Authentication -->
                            <div class="flex flex-col items-center bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">

                                <div class="w-8 h-8 md:w-10 md:h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-4">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>

                                <h4 class="font-semibold text-gray-900 text-md md:text-lg text-center mb-2">Secure Authentication</h4>
                                <p class="max-w-72 md:max-w-full text-gray-600 text-center text-xs md:text-sm leading-relaxed">
                                    API keys, Bearer tokens, OAuth, Basic Auth stored safely and applied when needed
                                </p>

                            </div>

                            <!-- Customization -->
                            <div class="flex flex-col items-center bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">

                                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-4">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>

                                <h4 class="font-semibold text-gray-900 text-md md:text-lg text-center mb-2">Customization</h4>
                                <p class="max-w-72 md:max-w-full text-gray-600 text-center text-xs md:text-sm leading-relaxed">
                                    Add your own headers, adjust parameters, and easily shape the data you send or receive
                                </p>

                            </div>

                        </div>

                     </div>

                </div>

            </section>

            <section class="lg:px-8 pt-12 pb-10 lg:pt-12 lg:pb-8">

                <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">

                    <div class="grid lg:grid-cols-12 gap-12 lg:gap-20">

                        <!-- Text column -->
                        <div class="flex flex-col items-center text-center lg:block lg:text-left lg:col-span-7">

                            <div class="inline-flex items-center gap-3 mb-6">
                                <span class="bg-indigo-100 text-indigo-800 text-sm font-semibold px-4 py-1.5 rounded-full shadow-sm">
                                    Developer Freedom
                                </span>
                            </div>

                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
                                Visual first.<br>
                                <p class="max-w-xl text-2xl sm:text-3xl lg:text-6xl bg-linear-to-r from-indigo-600 via-purple-600 to-indigo-700 bg-clip-text text-transparent">
                                    Code when you need ultimate control
                                </p>
                            </h2>

                            <p class="mt-6 text-md md:text-lg text-slate-700 leading-relaxed max-w-2xl">
                                Build 90%+ of flows visually. When business rules get complex — dynamic multi-language menus,
                                personalized offers, real-time decisions — drop real PHP, Python or JavaScript right inside the flow.
                            </p>

                            <ul class="mt-8 space-y-2 text-left text-sm md:text-md lg:text-lg text-slate-700">
                                <li class="flex items-center gap-5">
                                    <div class="shrink-0 w-8 h-8 rounded-md bg-indigo-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 lg:w-5 lg:h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <span>Full access to session, user input, variables & previous API results</span>
                                </li>
                                <li class="flex items-center gap-5">
                                    <div class="shrink-0 w-8 h-8 rounded-md bg-indigo-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 lg:w-5 lg:h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                        </svg>
                                    </div>
                                    <span>Intelligent conditional navigation & flow control</span>
                                </li>
                                <li class="flex items-center gap-5">
                                    <div class="shrink-0 w-8 h-8 rounded-md bg-indigo-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 lg:w-5 lg:h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <span>Sandboxed • Secure • Monitored execution</span>
                                </li>
                            </ul>

                        </div>

                        <!-- Visual column -->
                        <div class="flex flex-col items-center lg:block lg:col-span-5 lg:pt-8">

                            <div class="w-full md:w-2/3 lg:w-full rounded-2xl overflow-hidden shadow-2xl shadow-indigo-200/30 border border-slate-200">

                                <img
                                    class="w-full h-auto"
                                    :src="'/images/code.png'"
                                    alt="Telcoflo light theme code editor with PHP/Python/JavaScript support" />

                            </div>

                            <div class="flex flex-wrap whitespace-nowrap gap-4 mt-4">

                                <!-- PHP -->
                                <div class="grow group relative flex items-center gap-4 px-5 py-3 bg-white rounded-2xl shadow-sm
                                            border border-transparent bg-linear-to-r from-indigo-50/50 to-purple-50/30
                                            hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                                    <div class="relative">
                                        <div class="w-14 h-14 rounded-xl bg-linear-to-br from-indigo-600 to-purple-700
                                                    flex items-center justify-center text-white font-black text-xl
                                                    shadow-md group-hover:shadow-xl transition-shadow">
                                            PHP
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">PHP</div>
                                        <div class="text-xs text-slate-500">Classic & Reliable</div>
                                    </div>
                                </div>

                                <!-- Python -->
                                <div class="grow group relative flex items-center gap-4 px-5 py-3 bg-white rounded-2xl shadow-sm
                                            border border-transparent bg-linear-to-r from-amber-50/60 to-yellow-50/30
                                            hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                                    <div class="relative">
                                        <div class="w-14 h-14 rounded-xl bg-linear-to-br from-yellow-500 via-amber-500 to-orange-500
                                                    flex items-center justify-center text-white font-black text-xl
                                                    shadow-md group-hover:shadow-xl transition-shadow">
                                            Py
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">Python</div>
                                        <div class="text-xs text-slate-500">Clean & Powerful</div>
                                    </div>
                                </div>

                                <!-- JavaScript -->
                                <div class="grow group relative flex items-center gap-4 px-5 py-3 bg-white rounded-2xl shadow-sm
                                            border border-transparent bg-linear-to-r from-yellow-50/60 to-amber-50/30
                                            hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                                    <div class="relative">
                                        <div class="w-14 h-14 rounded-xl bg-linear-to-br from-yellow-400 to-amber-300
                                                    flex items-center justify-center text-gray-900 font-black text-xl
                                                    shadow-md group-hover:shadow-xl transition-shadow">
                                            JS
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">JavaScript</div>
                                        <div class="text-xs text-slate-500">Fast & Flexible</div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <!-- Section 7: Simulator -->
            <section class="lg:px-8 pt-12 pb-10 lg:pt-12 lg:pb-8">

                <div class="max-w-7xl mx-auto px-6 lg:px-8">

                    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20">

                        <!-- Image left (reversed order) -->
                        <div class="order-2 lg:order-1 flex flex-col items-center">

                            <img
                                class="w-60 md:w-80 lg:w-80 lg:-mt-10"
                                :src="'/images/telcoflo-simulator.png'"
                                alt="Telcoflo real-time USSD simulator"
                            />

                        </div>

                        <!-- Text right -->
                        <div class="flex flex-col items-center text-center lg:block lg:text-left order-1 lg:order-2">

                            <div class="inline-block mb-6 lg:mt-10">
                                <span class="bg-indigo-100 text-indigo-800 text-sm font-medium px-4 py-1.5 rounded-full">
                                    Instant Testing
                                </span>
                            </div>

                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-gray-900">
                                Test Like Real Users
                                <span class="block mt-2 text-indigo-600">Powerful Built-in Simulator</span>
                            </h2>

                            <p class="mt-6 text-md md:text-lg text-gray-600 leading-relaxed max-w-xl">
                                No need to deploy to see how your service behaves. Our high-fidelity simulator
                                lets you test full sessions, timeouts, network conditions and different phone numbers — instantly.
                            </p>

                            <ul class="mt-8 space-y-4">

                                <li class="flex md:items-center gap-2 md:gap-3 lg:gap-4 items-center">
                                    <div class="shrink-0 w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-2 h-2 md:w-3 md:h-3 lg:w-4 lg:h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700 text-md lg:text-lg text-left">Real-time session simulation</span>
                                </li>

                                <li class="flex md:items-center gap-2 md:gap-3 lg:gap-4 items-center">
                                    <div class="shrink-0 w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-2 h-2 md:w-3 md:h-3 lg:w-4 lg:h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700 text-md lg:text-lg text-left">Quick changes & instant previews</span>
                                </li>

                                <li class="flex md:items-center gap-2 md:gap-3 lg:gap-4 items-center">
                                    <div class="shrink-0 w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-2 h-2 md:w-3 md:h-3 lg:w-4 lg:h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700 text-md lg:text-lg text-left">Session history & debugging tools</span>
                                </li>

                                <li class="flex md:items-center gap-2 md:gap-3 lg:gap-4 items-center">
                                    <div class="shrink-0 w-4 h-4 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-2 h-2 md:w-3 md:h-3 lg:w-4 lg:h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700 text-md lg:text-lg text-left">Test across multiple phone numbers</span>
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>

            </section>

            <!-- Section 6: Analytics -->
            <section class="lg:px-8 pt-12 pb-10 lg:pt-12 lg:pb-8">

                <div class="max-w-7xl mx-auto px-6 lg:px-8">

                    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                        <!-- Text left -->
                        <div class="flex flex-col items-center text-center lg:block lg:text-left">

                            <div class="inline-block mb-6">
                                <span class="bg-emerald-100 text-emerald-800 text-sm font-medium px-4 py-1.5 rounded-full">
                                    Full Visibility
                                </span>
                            </div>

                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-gray-900">
                                Understand Every Session
                                <span class="block mt-2 text-emerald-600">Advanced Analytics & Monitoring</span>
                            </h2>

                            <p class="mt-6 text-md md:text-lg text-gray-600 leading-relaxed max-w-xl">
                                Get real-time and historical insights into user behavior, drop-off points,
                                performance bottlenecks and business metrics — everything you need to optimize and scale.
                            </p>

                            <ul class="mt-8 space-y-2 text-left text-sm md:text-md lg:text-lg text-slate-700">

                                <li class="flex items-center gap-4">
                                    <div class="shrink-0 w-8 h-8 rounded-md bg-emerald-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 lg:w-5 lg:h-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Session trends & drop-off insights</span>
                                </li>

                                <li class="flex items-center gap-4">
                                    <div class="shrink-0 w-8 h-8 rounded-md bg-emerald-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Real-time dashboard & alerts</span>
                                </li>

                                <li class="flex items-center gap-4">
                                    <div class="shrink-0 w-8 h-8 rounded-md bg-emerald-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 lg:w-5 lg:h-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Business KPIs & reports</span>
                                </li>

                            </ul>

                        </div>

                        <!-- Image right -->
                        <div class="flex flex-col items-center lg:block">
                            <div class="w-full md:w-2/3 lg:w-full rounded-3xl overflow-hidden shadow-2xl shadow-indigo-200/30 border border-gray-200">

                                <img
                                    class="w-full h-auto"
                                    :src="'/images/telcoflo-analytics-2.png'"
                                    alt="Telcoflo USSD analytics and monitoring dashboard" />

                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!-- Section 8: Hosting -->
            <section class="lg:px-8 pt-10 pb-10 lg:pt-12 lg:pb-8">

                <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">

                    <div class="text-center mb-16">

                        <div class="inline-block mb-8">
                            <span class="bg-indigo-100 text-indigo-800 text-sm font-semibold px-5 py-2 rounded-full shadow-sm">
                                Deployment Flexibility
                            </span>
                        </div>

                        <h2 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900">
                            Host <span class="text-indigo-600">Your Way</span>
                            <span class="text-xl sm:text-4xl lg:text-6xl block mt-3">Cloud • On-Prem • Private Cloud</span>
                        </h2>

                        <p class="mt-6 text-md md:text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
                            Zero-maintenance global cloud or complete data sovereignty —
                            Telcoflo gives enterprise teams real choice without compromise.
                        </p>

                    </div>

                    <!-- Cards with improved visual hierarchy -->
                    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 xl:gap-16 items-stretch">

                        <!-- CLOUD CARD -->
                        <div class="group bg-white rounded-3xl overflow-hidden shadow-xl border border-indigo-100/60 transition-all duration-400 hover:shadow-2xl hover:-translate-y-2 hover:border-indigo-200">

                            <div class="bg-linear-to-r from-indigo-600 via-indigo-700 to-purple-700 text-white p-8">
                                <h3 class="text-xl md:text-2xl lg:text-3xl font-bold">Cloud Hosted</h3>
                                <p class="mt-3 text-gray-300 text-md md:text-lg font-medium">Launch fast. We take care of everything.</p>
                            </div>

                            <div class="p-8 flex flex-col grow">

                                <div class="w-fit mb-8 md:inline-flex md:text-center items-center gap-2 px-8 py-2.5 md:px-5 bg-indigo-50 text-indigo-800 rounded-full text-sm">
                                    <span>Best for </span>
                                    <span class="font-semibold">Startups • Fintech • NGOs • Speed-focused teams</span>
                                </div>

                                <ul class="space-y-6 text-sm md:text-base text-gray-700 grow">
                                    <li class="flex items-start gap-5">
                                        <div class="shrink-0 w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
                                            </svg>
                                        </div>
                                        <span class="leading-relaxed">Zero server management — automatic scaling, updates, backups & security</span>
                                    </li>
                                </ul>

                            </div>

                        </div>

                        <!-- ON-PREMISE CARD -->
                        <div class="group bg-white rounded-3xl overflow-hidden shadow-xl border border-gray-200 transition-all duration-400 hover:shadow-2xl hover:-translate-y-2 hover:border-gray-300">

                            <div class="bg-linear-to-r from-gray-700 via-gray-800 to-slate-900 text-white p-8">
                                <h3 class="text-xl md:text-2xl lg:text-3xl font-bold">On-Premise / Private Cloud</h3>
                                <p class="mt-3 text-gray-300 text-md md:text-lg font-medium">Total control. Your infrastructure. Your rules.</p>
                            </div>

                            <div class="p-8 flex flex-col grow">

                                <div class="w-fit mb-8 md:inline-flex md:text-center items-center gap-2 px-8 py-2.5 md:px-5 bg-gray-100 text-gray-800 rounded-full text-sm">
                                    <span>Best for </span>
                                    <span class="font-semibold">Banks • Government • Telcos • High-security environments</span>
                                </div>

                                <ul class="space-y-6 text-sm md:text-base text-gray-700 grow">
                                    <li class="flex items-start gap-5">
                                        <div class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                        </div>
                                        <span>Deploy anywhere — your servers, private cloud, AWS / GCP / Azure VPC</span>
                                    </li>
                                </ul>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <!-- Section 9: FAQs -->
            <section id="faqs" class="lg:px-8 pt-10 pb-10 lg:pt-12 lg:pb-8">

                <div class="max-w-5xl mx-auto px-6 lg:px-8">

                    <!-- Header -->
                    <div class="text-center mb-12">

                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900">
                            Frequently Asked Questions
                        </h2>

                        <p class="lg:max-w-md mt-4 text-md md:text-lg text-gray-600 max-w-2xl mx-auto">
                            Everything you need to know about building, deploying and scaling USSD applications with Telcoflo
                        </p>

                    </div>

                    <!-- Tabs -->
                    <div class="flex flex-wrap justify-center mb-12">

                        <Tabs v-model="activeTab" :tabs="tabs" size="lg" class="justify-center"></Tabs>

                    </div>

                    <!-- FAQ Items with smooth transition -->
                    <Transition name="faq-list" mode="out-in">

                        <div class="space-y-4" :key="activeTab">

                            <div
                                :key="faq.id"
                                :style="{ '--order': index }"
                                v-for="(faq, index) in filteredFaqs"
                                class="group rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all overflow-hidden">

                                <button
                                    @click="toggleFaq(faq.id)"
                                    class="flex w-full items-center justify-between px-6 py-5 text-left">

                                    <div class="flex items-center gap-8">

                                        <!-- Category badge -->
                                        <span
                                            v-if="activeTab === 'all'"
                                            class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium uppercase">
                                            {{ faq.category.charAt(0) }}
                                        </span>

                                        <span class="text-md md:text-lg font-semibold text-gray-900">{{ faq.question }}</span>

                                    </div>

                                    <svg
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        :class="{ 'rotate-180': openFaq === faq.id }"
                                        class="h-6 w-6 text-slate-500 transition-transform duration-200">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>

                                </button>

                                <div
                                    v-if="openFaq === faq.id"
                                    class="text-sm md:text-base px-6 py-6 text-gray-600 leading-relaxed border-t border-slate-100 bg-slate-50/40">
                                    <p v-html="faq.answer"></p>
                                </div>

                            </div>

                        </div>

                    </Transition>

                    <!-- No results / Help message -->
                    <div v-if="filteredFaqs.length === 0" class="text-center py-16 text-gray-500">

                        No questions found in this category yet.<br />
                        <a href="mailto:bonolo@telcoflo.com" class="text-indigo-600 hover:underline">
                            Contact us with your question →
                        </a>

                    </div>

                </div>

            </section>

            <!-- Section 10: Contacts -->
            <section id="contact" class="md:pt-10">

                <div class="text-center">

                    <p class="text-2xl font-bold text-gray-900 mb-3">
                        Still have questions?
                    </p>

                    <p class="max-w-sm md:max-w-2xl text-md md:text-lg text-gray-600 mb-10 mx-auto">
                        Reach out via email for detailed questions or WhatsApp for quick chats — we're here to help you launch your USSD service fast.
                    </p>

                    <div class="text-left max-w-4xl mx-auto grid md:grid-cols-2 gap-8 px-6">

                        <!-- Email Card -->
                        <div class="bg-white rounded-2xl shadow-xl border border-indigo-100/60 overflow-hidden flex flex-col">

                            <div class="bg-linear-to-r from-indigo-600 to-indigo-700 px-6 py-5 text-white">

                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg">Talk to Bonolo</h3>
                                        <p class="text-sm text-indigo-100/90">Best for detailed questions & attachments</p>
                                    </div>
                                </div>

                            </div>

                            <div class="p-6 flex flex-col items-center grow">

                                <!-- Personal profile section -->
                                <div class="flex items-center gap-4 mb-6">

                                    <img
                                        :src="'/images/bonolo.jpeg'"
                                        alt="Bonolo - Support Specialist"
                                        class="w-16 h-16 rounded-full object-cover border-4 border-indigo-300 shadow-sm"
                                    />

                                    <div>
                                        <p class="font-medium text-gray-900 text-lg">Bonolo</p>
                                        <p class="text-sm text-gray-600">Solutions Specialist</p>
                                        <a
                                            @click="openGmail"
                                            href="mailto:bonolo@telcoflo.com"
                                            class="text-indigo-600 hover:underline text-sm font-medium">
                                        bonolo@telcoflo.com
                                        </a>
                                    </div>

                                </div>

                                <div class="text-sm text-gray-600 mb-6">
                                    Typical response: <strong>within 24 hours</strong><br>
                                </div>

                                <div class="mt-auto flex justify-center gap-4">

                                    <Button :action="copyEmail" type="primary" buttonClass="shadow-lg rounded-lg">
                                        {{ copied ? 'Copied!' : 'Copy Email' }}
                                    </Button>

                                    <Button :action="openGmail" mode="outline" type="white" buttonClass="shadow-lg rounded-lg">
                                        Contact Us
                                    </Button>

                                </div>

                            </div>

                        </div>

                        <!-- WhatsApp Card -->
                        <div class="bg-white rounded-2xl shadow-xl border border-green-100/60 overflow-hidden flex flex-col">

                            <div class="bg-linear-to-r from-green-600 to-green-700 px-6 py-5 text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                                        <WhatsappIcon size="w-6 h-6" color="#ffffff" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg">Whatsapp Support</h3>
                                        <p class="text-sm text-green-100/90">Best for quick answers & real-time support</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 flex flex-col items-center justify-center grow space-y-6">

                                <div class="text-center">
                                    <p class="text-gray-700 font-medium mb-1">Join our WhatsApp group</p>
                                    <p class="text-sm text-gray-500">(Quick support, updates & discussions)</p>
                                </div>

                                <a
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    href="https://chat.whatsapp.com/HpEql4wNFxcJc84sgpUKFC"
                                    class="inline-flex items-center gap-3 px-8 py-4 bg-green-600 text-white rounded-full font-medium hover:bg-green-700 transition-all shadow-lg transform hover:scale-105 active:scale-100">
                                    <WhatsappIcon size="w-6 h-6" color="#ffffff" />
                                    Join Telcoflo Group
                                </a>

                                <p class="text-xs text-gray-500 text-center">
                                    Tap to join instantly — available on mobile WhatsApp app
                                </p>

                            </div>

                        </div>

                    </div>

                    <p class="mt-10 text-sm text-gray-500">
                        Choose whichever feels easiest — we look forward to hearing from you!
                    </p>

                </div>

            </section>

            <!-- Section 11: Image -->
            <section>

                <div class="relative flex items-end order-1 lg:order-2">
                    <img :src="'/images/customer.png'" class="w-4/5 md:w-full max-w-xl mx-auto" alt="Lady phone with USSD menu" />
                </div>

            </section>

        </main>

        <footer class="border-t border-slate-200 bg-slate-50/70 py-12 text-center text-sm text-slate-600">
            © {{ currentYear }} Telcoflo • Built for Africa’s digital future
        </footer>

    </div>

</template>

<script>

    import Logo from '@Partials/Logo.vue';
    import Tabs from '@Partials/Tabs.vue';
    import Button from '@Partials/Button.vue';
    import WhatsappIcon from '@Partials/WhatsappIcon.vue';

    export default {
        name: 'LandingPage',
        components: {
            Logo, Tabs, Button, WhatsappIcon
        },
        data() {
            return {
            copied: false,
            openFaq: null,
            activeTab: 'all',

            tabs: [
                { label: 'All', value: 'all' },
                { label: 'Platform', value: 'platform' },
                { label: 'Technology', value: 'technology' },
                { label: 'Deployment', value: 'deployment' },
                { label: 'Integrations', value: 'integrations' },
                { label: 'Support', value: 'support' }
            ],

            faqs: [
                {
                id: 1,
                category: 'platform',
                question: 'Do I need coding experience to build USSD applications?',
                answer: 'Absolutely not! Our visual drag-and-drop interface is specifically designed for businesses, product managers, and non-technical teams. You can create sophisticated USSD flows complete with complex menus, conditions, validations and integrations without writing a single line of code.'
                },
                {
                id: 2,
                category: 'platform',
                question: 'How quickly can I build and launch a USSD service?',
                answer: 'Most services go from idea to production in <strong>1–4 weeks</strong>. Simple menu-based services can be launched in just a few days thanks to the visual editor and instant simulator.'
                },
                {
                id: 3,
                category: 'integrations',
                question: 'What kind of APIs and systems can I connect to?',
                answer: 'Telcoflo supports <strong>REST</strong>, <strong>GraphQL</strong> and most common authentication methods (API Keys, Bearer Tokens, OAuth2, Basic Auth, JWT). You can also call SOAP services via REST wrappers.'
                },
                {
                id: 4,
                category: 'deployment',
                question: 'What hosting options do you provide?',
                answer: 'You have full flexibility:<br>• Fully managed global cloud (fastest to launch)<br>• Private cloud (AWS VPC, Azure, GCP, etc.)<br>• On-premise / self-hosted'
                },
                {
                id: 5,
                category: 'technology',
                question: 'What USSD protocols and short codes are supported?',
                answer: 'We support both <strong>session-based</strong> and <strong>non-session</strong> USSD. Works with all major African mobile operators and short codes (3–8 digits), including shared & dedicated codes.<br><br><strong>Session-based</strong>: This is the interactive, multi-step mode (e.g., *123# → menu → choose 1 → next step).<br><br><strong>Non-session / freeflow</strong>: This is a single-request, one-shot interaction — you dial a code (e.g *123*amount#), get one immediate response, and that\'s it. No ongoing menu or back-and-forth. It\'s simpler but limited.'
                },
                {
                id: 6,
                category: 'support',
                question: 'What kind of support do you offer?',
                answer: '• Email & chat support (business hours)<br>• Priority support & dedicated success manager (Enterprise plans)<br>• Comprehensive documentation, video tutorials & community forum'
                }
            ]
            }
        },
        watch: {
            filteredFaqs: {
                handler(newList) {
                    if (newList.length > 0) {
                        this.openFaq = newList[0].id
                    } else {
                        this.openFaq = null
                    }
                },
                immediate: true
            }
        },
        computed: {
            filteredFaqs() {
                if (this.activeTab === 'all') {
                    return this.faqs
                }
                return this.faqs.filter(faq => faq.category === this.activeTab)
            },

            currentYear() {
                return new Date().getFullYear()
            }
        },
        methods: {
            toggleFaq(id) {
                this.openFaq = this.openFaq === id ? null : id
            },
            async copyEmail() {

                try {

                    await navigator.clipboard.writeText('bonolo@telcoflo.com');

                    this.copied = true;

                    setTimeout(() => {
                        this.copied = false;
                    }, 2500)

                } catch (err) {
                    console.error('Failed to copy:', err)
                    // Fallback
                    window.location.href = 'mailto:bonolo@telcoflo.com'
                }

            },
            scrollToContact() {

                const element = document.getElementById('contact');
                if (!element) return;

                const navbarHeight = 80
                const desiredOffsetFromTop = window.innerHeight / 2 - 350;

                const topPos = element.getBoundingClientRect().top + window.scrollY;

                window.scrollTo({
                    top: topPos - navbarHeight - desiredOffsetFromTop,
                    behavior: 'smooth'
                });

            },
            scrollToFaqs() {

                const element = document.getElementById('faqs');
                if (!element) return;

                const navbarHeight = 80;
                const desiredOffsetFromTop = window.innerHeight / 2 - 350;

                const topPos = element.getBoundingClientRect().top + window.scrollY;

                window.scrollTo({
                    top: topPos - navbarHeight - desiredOffsetFromTop,
                    behavior: 'smooth'
                });

            },
            openGmail() {
                const email = 'bonolo@telcoflo.com'
                const subject = 'Question about Telcoflo USSD'
                const body = 'Hi Bonolo,\n\nI have a question about...'

                const url = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`

                window.open(url, '_blank', 'noopener,noreferrer')
            },
            navigateToLogin() {
                this.$router.push({ name: 'login' });
            }
        }
    }

</script>

<style scoped>

    .faq-list-enter-active,
    .faq-list-leave-active {
        transition: opacity 0.4s ease, transform 0.4s ease;
    }

    .faq-list-enter-from,
    .faq-list-leave-to {
        opacity: 0;
        transform: translateY(16px);
    }

    .faq-list-enter-active > div {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        transition-delay: calc(var(--order) * 50ms);
    }

</style>
