import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const analytics_dashboards_index = () => ({
    component: import(/* webpackChunkName: "expence" */ '../components/dashboards/index.vue'),
    loading: LoadingComponent,
});

const google_ads_index = () => ({
    component: import(/* webpackChunkName: "expence" */ '../components/googleads/index.vue'),
    loading: LoadingComponent,
});

export default [
    {
        path: '/analytics',
        name: 'analytics_dashboards_index',
        component: analytics_dashboards_index,
    },
    {
        path: '/google_ads',
        name: 'google_ads_index',
        component: google_ads_index,
    },
];