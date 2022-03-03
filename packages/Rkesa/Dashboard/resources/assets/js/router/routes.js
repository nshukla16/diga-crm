// Dashboard
import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const my_dashboard = () => ({
    component: import(/* webpackChunkName: "dashboard" */ '../components/dashboard/main.vue'),
    loading: LoadingComponent,
});
const dashboards_index = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/dashboard_settings/index.vue'),
    loading: LoadingComponent,
});
const dashboard_form = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/dashboard_settings/_form.vue'),
    loading: LoadingComponent,
});

import store from '../../../../../../../resources/assets/js/vuex/store'

const ifHasDashboard = (to, from, next) => {
    if (store.getters.getUser.dashboard_id != 0 && store.getters.getUser.dashboard_id != null) {
        next();
        return;
    }
    if (store.getters.getUser.show_calendar_on_main_page === true){
        next();
        return;        
    }
    next('/welcome');
};

export default [
    {
        path: '/dashboard',
        name: 'my_dashboard',
        component: my_dashboard,
        beforeEnter: ifHasDashboard,
    },
    {
        path: '/settings/dashboards',
        name: 'dashboards_settings',
        component: dashboards_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/settings/dashboards/create',
        name: 'dashboard_create',
        component: dashboard_form,
    },
    {
        path: '/settings/dashboards/:id(\\d+)/edit',
        name: 'dashboard_edit',
        component: dashboard_form,
        props: (route) => ({id: route.params.id }),
    },
];