// Service
import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

import services_index from '../components/service/index.vue';
const service_settings = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/service_settings/states.vue'),
    loading: LoadingComponent,
});

export default [
    {
        path: '/services',
        name: 'services_index',
        component: services_index,
        props: (route) => ({ responsible_user_id: route.query.responsible_user_id, offset: route.query.offset }),
    },
    {
        path: '/settings/services',
        name: 'service_settings',
        component: service_settings,
    },
];