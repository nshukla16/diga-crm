// Calendar
import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const finances_index = () => ({
    component: import(/* webpackChunkName: "calendar" */ '../components/finances/index.vue'),
    loading: LoadingComponent,
});

export default [
    {
        path: '/finances',
        name: 'finances_index',
        component: finances_index,
    },
];