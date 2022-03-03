// Estimate
import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const resources_index = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/resource/index.vue'),
    loading: LoadingComponent,
});
const resource_form = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/resource/_form.vue'),
    loading: LoadingComponent,
});
const fichas_index = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/ficha/index.vue'),
    loading: LoadingComponent,
});
const ficha_form = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/ficha/_form.vue'),
    loading: LoadingComponent,
});
const estimates_index = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/estimate/index.vue'),
    loading: LoadingComponent,
});
const estimate_form = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/estimate/_form.vue'),
    loading: LoadingComponent,
});
const estimate_documents = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/estimate/documents.vue'),
    loading: LoadingComponent,
});
const planning_index = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../components/planning/edit.vue'),
    loading: LoadingComponent,
});
const estimate_settings = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/estimate_settings/settings.vue'),
    loading: LoadingComponent,
});
const file_show = () => ({
    component: import(/* webpackChunkName: "estimate" */ '../../../../../../../resources/assets/js/components/home/show_pdf.vue'),
    loading: LoadingComponent,
});

const financial_liabilities = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/financial_liabilities/index.vue'),
    loading: LoadingComponent,
});

const my_works_index = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/my_works/index.vue'),
    loading: LoadingComponent,
});

const my_works_form = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/my_works/form/_form.vue'),
    loading: LoadingComponent,
});

export default [
    {
        path: '/estimates',
        name: 'estimates_index',
        component: estimates_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/estimates/create',
        name: 'estimate_create',
        component: estimate_form,
        props: (route) => ({service_id: route.query.service_id, base_estimate_id: route.query.base_estimate_id, action: route.query.action }),
    },
    {
        path: '/estimates/:id(\\d+)/edit',
        name: 'estimate_edit',
        component: estimate_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/estimates/:id(\\d+)',
        name: 'estimate_show',
        component: file_show,
        props: (route) => ({src: '/api/estimates/get_link/' + route.params.id }),
    },
    {
        path: '/plannings/:id(\\d+)',
        name: 'planning_show',
        component: file_show,
        props: (route) => ({src: '/api/plannings/get_link/' + route.params.id }),
    },
    {
        path: '/estimates/:id(\\d+)/documents',
        name: 'estimate_documents',
        component: estimate_documents,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/plannings/:id(\\d+)/edit',
        name: 'planning_edit',
        component: planning_index,
        props: (route) => ({id: route.params.id }),
    },

    {
        path: '/fichas',
        name: 'fichas_index',
        component: fichas_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/fichas/create',
        name: 'ficha_create',
        component: ficha_form,
    },
    {
        path: '/fichas/:id(\\d+)/edit',
        name: 'ficha_edit',
        component: ficha_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/resources',
        name: 'resources_index',
        component: resources_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/resources/create',
        name: 'resource_create',
        component: resource_form,
    },
    {
        path: '/resources/:id(\\d+)/edit',
        name: 'resource_edit',
        component: resource_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/settings/estimates',
        name: 'estimate_settings',
        component: estimate_settings,
    },
    {
        path: '/financial_liabilities',
        name: 'financial_liabilities_index',
        component: financial_liabilities,
    },
    {
        path: '/my_works',
        name: 'my_works_index',
        component: my_works_index,
    },
    {
        path: '/my_works/:id(\\d+)',
        name: 'my_works_edit',
        component: my_works_form,
        props: (route) => ({id: route.params.id }),
    },
];