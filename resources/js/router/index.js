import { useUiStore } from "@Stores/ui-store.js";
import { useAuthStore } from "@Stores/auth-store.js";
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        component: () => import('@Layouts/public/Public.vue'),
        children: [
            {
                path: '',
                name: 'landing',
                component: () => import('@/pages/landing/Landing.vue'),
                props: true
            }

        ]
    },
    {
        path: '/auth',
        component: () => import('@Layouts/auth/Auth.vue'),
        children: [
            {
                path: 'login',
                name: 'login',
                component: () => import('@Pages/auth/Login.vue'),
                props: true
            }

        ]
    },
    {
        path: '/apps',
        component: () => import('@Layouts/dashboard/Dashboard.vue'),
        meta: { requiresAuth: true },
        props: true,
        children: [
            {
                path: '',
                name: 'show-apps',
                component: () => import('@Pages/apps/Apps.vue')
            },
            {
                name: 'create-app',
                path: 'create',
                component: () => import('@Pages/apps/CreateApp.vue')
            },
            {
                path: ':app_id',
                children: [
                    {
                        path: '',
                        name: 'show-app-home',
                        component: () => import('@Pages/home/Home.vue')
                    },
                    {
                        path: 'review',
                        name: 'show-app-review',
                        component: () => import('@Pages/review/Review.vue')
                    },
                    {
                        path: 'deploy',
                        name: 'show-app-deploy',
                        component: () => import('@Pages/home/Home.vue')
                    },
                    {
                        path: 'versions',
                        children: [
                            {
                                path: '',
                                name: 'show-app-versions',
                                component: () => import('@Pages/versions/Versions.vue')
                            },
                            {
                                path: ':version_id',
                                name: 'show-app-version',
                                component: () => import('@Pages/versions/Version.vue')
                            }
                        ]
                    },
                    {
                        path: 'sessions',
                        children: [
                            {
                                path: '',
                                name: 'show-app-sessions',
                                component: () => import('@Pages/sessions/Sessions.vue')
                            },
                            {
                                path: ':session_id',
                                name: 'show-app-session',
                                component: () => import('@Pages/sessions/Session.vue')
                            },
                        ]
                    },
                    {
                        path: 'accounts',
                        children: [
                            {
                                path: '',
                                name: 'show-app-accounts',
                                component: () => import('@Pages/accounts/Accounts.vue')
                            },
                            {
                                path: ':account_id',
                                name: 'show-app-account',
                                component: () => import('@Pages/accounts/Account.vue')
                            },
                        ]
                    },
                    {
                        path: 'deployments',
                        children: [
                            {
                                path: '',
                                name: 'show-app-deployments',
                                component: () => import('@Pages/deployments/Deployments.vue')
                            },
                            {
                                path: ':deployment_id',
                                name: 'show-app-deployment',
                                component: () => import('@Pages/deployments/Deployment.vue')
                            },
                        ]
                    },
                    {
                        path: 'analytics',
                        name: 'show-app-analytics',
                        component: () => import('@Pages/analytics/Analytics.vue')
                    },
                    {
                        path: 'business-kpis',
                        name: 'show-app-business-kpis',
                        component: () => import('@Pages/business-kpis/BusinessKpis.vue')
                    },
                    {
                        path: 'business-kpis/:metric_id/records',
                        name: 'show-app-metric-records',
                        component: () => import('@Pages/business-kpis/MetricRecords.vue')
                    },
                    {
                        path: 'databases',
                        name: 'show-app-databases',
                        component: () => import('@Pages/home/Home.vue')
                    },
                    {
                        path: 'issues',
                        name: 'show-app-issues',
                        component: () => import('@Pages/issues/Issues.vue')
                    },
                    {
                        path: 'resources',
                        name: 'show-app-resources',
                        component: () => import('@Pages/issues/Issues.vue')
                    },
                ]
            },
        ]
    },
    {
        path: '/account',
        name: 'show-account',
        component: () => import('@Pages/apps/Apps.vue')
    },
    {
        path: '/:catchAll(.*)',
        name: 'notFound',
        component: () => import('@Pages/error/404.vue'),
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    const uiState = useUiStore();
    const authState = useAuthStore();

    uiState.showLoader();

    if (!authState.user) {
        const storedToken = authState.getTokenFromLocalStorage();

        if (storedToken) {
            authState.token = storedToken;
            authState.setTokenOnRequest(storedToken);

            try {
                await authState.fetchUser();
            } catch (e) {
                authState.unsetUser();
                authState.unsetToken();
            }
        }
    }

    if (to.meta?.requiresAuth === true && !authState.user) {
        return next({
            name: 'login',
            query: { redirect: to.fullPath },
            replace: true
        });
    }

    if (authState.user && to.name === 'login') {
        return next({ name: 'show-apps' });
    }

    return next();
});


router.afterEach(() => {
    const uiState = useUiStore();
    uiState.hideLoader();
});

export default router;
