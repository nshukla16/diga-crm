import Vue from 'vue';
import VueRouter from 'vue-router'
import store from './../vuex/store'

Vue.use(VueRouter);

const ifNotAuthenticated = (to, from, next) => {
    if (!store.getters.isAuthenticated) {
        next();
        return;
    }
    next('/dashboard');
};

const ifAuthenticatedAndSettingsLoaded = (to, from, next) => {
    if (store.getters.isAuthenticated) {
        if (store.getters.getAllSettingsStatus == 'success') {
            next();
        } else {
            if (store.getters.getAllSettingsStatus == 'not-started') {
                store.dispatch('loadInitialData').then((resp) => {
                    next();
                });
            }
        }
    } else {
        next('/');
    }
};

import LoadingComponent from './../components/loading.vue';

// PACKAGES
import calendar_routes from '../../../../packages/Rkesa/Calendar/resources/assets/js/router/routes.js'
import service_routes from '../../../../packages/Rkesa/Service/resources/assets/js/router/routes.js'
import dashboard_routes from '../../../../packages/Rkesa/Dashboard/resources/assets/js/router/routes.js'
import client_routes from '../../../../packages/Rkesa/Client/resources/assets/js/router/routes.js'
import email_routes from '../../../../packages/Rkesa/Email/resources/assets/js/router/routes.js'
import estimate_routes from '../../../../packages/Rkesa/Estimate/resources/assets/js/router/routes.js'
import estimate_fork_routes from '../../../../packages/Rkesa/EstimateFork/resources/assets/js/router/routes.js'
import hr_routes from '../../../../packages/Rkesa/Hr/resources/assets/js/router/routes.js'
import project_routes from '../../../../packages/Rkesa/Project/resources/assets/js/router/routes.js'
import gantt_routes from '../../../../packages/Rkesa/Planning/resources/assets/js/router/routes.js'
import expences_routes from '../../../../packages/Rkesa/Expences/resources/assets/js/router/routes.js'
import analytics_routes from '../../../../packages/Rkesa/Analytics/resources/assets/js/router/routes.js'
import financial_calendar_routes from '../../../../packages/Rkesa/FinancialCalendar/resources/assets/js/router/routes.js'

// COMMON
import login from './../components/auth/login.vue';
import logout from './../components/auth/logout.vue';
import password_1 from '../components/auth/reset_password.vue';
import password_2 from '../components/auth/reset_password_token.vue';
import auth0 from '../components/auth/auth0.vue';
import layout from './../components/layout/layout.vue';
import welcome_page from './../components/home/home.vue';
import notifications from './../components/home/notifications.vue';
import profile_settings from './../components/home/profile.vue';
import payments from './../components/payment/index.vue';
import subscriptions from './../components/payment/subscription/index.vue';
import hr_settings from './../components/settings/hr.vue';
import general_contractors from './../components/settings/general_contractors.vue';

// SETTINGS
const global_settings = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/settings/global_settings.vue'),
    loading: LoadingComponent,
});
const client_incoming_test = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/settings/incoming_test.vue'),
    loading: LoadingComponent,
});
const integration_settings = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/settings/integration_settings.vue'),
    loading: LoadingComponent,
});
const site_settings = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/settings/site_settings.vue'),
    loading: LoadingComponent,
});
const users_groups = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/settings/groups.vue'),
    loading: LoadingComponent,
});
const user_permissions = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/settings/user_permissions.vue'),
    loading: LoadingComponent,
});
const company_information = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/settings/company_information.vue'),
    loading: LoadingComponent,
});
const invoice_index = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/invoice/index.vue'),
    loading: LoadingComponent,
});
const invoice_form = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/invoice/_form.vue'),
    loading: LoadingComponent,
});
const file_show = () => ({
    component: import ( /* webpackChunkName: "estimate" */ './../components/home/show_pdf.vue'),
    loading: LoadingComponent,
});
const invoice_settings = () => ({
    component: import ( /* webpackChunkName: "estimate" */ './../components/settings/invoices.vue'),
    loading: LoadingComponent,
});
const iva_settings = () => ({
    component: import ( /* webpackChunkName: "estimate" */ './../components/settings/iva.vue'),
    loading: LoadingComponent,
});
const timetracker_settings = () => ({
    component: import ('./../components/settings/timetracker.vue'),
    loading: LoadingComponent,
});
const products_index = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/product/index.vue'),
    loading: LoadingComponent,
});
const products_form = () => ({
    component: import ( /* webpackChunkName: "settings" */ './../components/product/_form.vue'),
    loading: LoadingComponent,
});

const routes = [{
        path: '/',
        name: 'login',
        component: login,
        beforeEnter: ifNotAuthenticated,
    },
    {
        path: '/auth0',
        name: 'auth0',
        component: auth0,
        props: route => ({ code: route.query.code }),
        beforeEnter: ifNotAuthenticated,
    },
    {
        path: '/signoff',
        name: 'logout',
        component: logout,
    },
    // {
    //     path: '/password/reset/',
    //     name: 'password-email',
    //     component: password_1,
    //     beforeEnter: ifNotAuthenticated,
    // },
    // {
    //     path: '/password/reset/:token',
    //     name: 'password-reset',
    //     component: password_2,
    //     beforeEnter: ifNotAuthenticated,
    // },
    {
        path: '/',
        name: 'layout',
        component: layout,
        beforeEnter: ifAuthenticatedAndSettingsLoaded,
        children: [{
                    path: '/welcome',
                    name: 'welcome_page',
                    component: welcome_page,
                },
                {
                    path: '/notifications',
                    name: 'notifications',
                    component: notifications,
                },
                // SETTINGS
                {
                    path: '/profile',
                    name: 'profile_settings',
                    component: profile_settings,
                },
                {
                    path: '/settings/global',
                    name: 'global_settings',
                    component: global_settings,
                },
                {
                    path: '/settings/clients/incoming_test',
                    name: 'client_incoming_test',
                    component: client_incoming_test,
                },
                {
                    path: '/settings/integrations',
                    name: 'integration_settings',
                    component: integration_settings,
                },
                {
                    path: '/settings/sites',
                    name: 'site_settings',
                    component: site_settings,
                },
                {
                    path: '/settings/users',
                    name: 'users_groups',
                    component: users_groups,
                },
                {
                    path: '/hr/:id(\\d+)/permissions',
                    name: 'user_permissions',
                    component: user_permissions,
                    props: (route) => ({ id: route.params.id }),
                },
                {
                    path: '/payments',
                    name: 'payments',
                    component: payments,
                },
                {
                    path: '/subscriptions',
                    name: 'subscriptions',
                    component: subscriptions,
                },
                {
                    path: '/settings/company_information',
                    name: 'company_information',
                    component: company_information,
                },
                {
                    path: '/settings/hr',
                    name: 'hr_settings',
                    component: hr_settings,
                },
                {
                    path: '/invoice',
                    name: 'invoice_index',
                    component: invoice_index,
                    props: (route) => ({ offset: route.query.offset }),
                },
                {
                    path: '/invoice/create',
                    name: 'invoice_create',
                    component: invoice_form,
                },
                {
                    path: '/invoice/:id(\\d+)/edit',
                    name: 'invoice_edit',
                    component: invoice_form,
                    props: (route) => ({ id: route.params.id }),
                },
                {
                    path: '/invoice/:id(\\d+)/pay_stage',
                    name: 'invoice_create_pay_stage',
                    component: invoice_form,
                    props: (route) => ({ pay_stage_id: route.params.id }),
                },
                {
                    path: '/invoice/:id(\\d+)',
                    name: 'invoice_pdf',
                    component: file_show,
                    props: (route) => ({ src: '/api/invoices/get_link/' + route.params.id }),
                },
                {
                    path: '/invoice_settings',
                    name: 'invoice_settings',
                    component: invoice_settings
                },
                {
                    path: '/iva_settings',
                    name: 'iva_settings',
                    component: iva_settings
                },
                {
                    path: '/timetracker_settings',
                    name: 'timetracker_settings',
                    component: timetracker_settings
                },
                {
                    path: '/general_contractors',
                    name: 'general_contractors',
                    component: general_contractors
                },
                {
                    path: '/products',
                    name: 'products_index',
                    component: products_index
                },
                {
                    path: '/products/create',
                    name: 'products_create',
                    component: products_form
                },
                {
                    path: '/products/:id(\\d+)/edit',
                    name: 'products_edit',
                    component: products_form,
                    props: (route) => ({ id: route.params.id }),
                },
            ]
            .concat(estimate_fork_routes)
            .concat(calendar_routes)
            .concat(service_routes)
            .concat(dashboard_routes)
            .concat(client_routes)
            .concat(email_routes)
            .concat(estimate_routes)
            .concat(hr_routes)
            .concat(project_routes)
            .concat(gantt_routes)
            .concat(expences_routes)
            .concat(analytics_routes)
            .concat(financial_calendar_routes),
    },
];

const router = new VueRouter({
    mode: 'history',
    routes,
});

router.beforeEach(async(to, from, next) => {
    if (store.getters.getSharedInfoStatus == 'success') {
        next();
    } else {
        store.dispatch('loadSharedInfo').then((resp) => {
            next();
        });
    }
});

export default router;