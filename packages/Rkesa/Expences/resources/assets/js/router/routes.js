import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const expences_index = () => ({
    component: import(/* webpackChunkName: "expence" */ '../components/index.vue'),
    loading: LoadingComponent,
});

const expences_form = () => ({
    component: import(/* webpackChunkName: "expence" */ '../components/_form.vue'),
    loading: LoadingComponent,
});

const expences_show = () => ({
    component: import(/* webpackChunkName: "expence" */ '../components/show.vue'),
    loading: LoadingComponent,
});

export default [
    {
        path: '/expences',
        name: 'expences_index',
        component: expences_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/expences/:estimate_id(\\d+)/estimate',
        name: 'expences_index_estimate',
        component: expences_index,
        props: (route) => ({ offset: route.query.offset, estimate_id: route.params.estimate_id }),
    },
    {
        path: '/expences/create',
        name: 'expences_create',
        component: expences_form,
    },
    {
        path: '/expences/:id(\\d+)/edit',
        name: 'expences_edit',
        component: expences_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/expences/:id(\\d+)',
        name: 'expences_show',
        component: expences_show,
        props: (route) => ({id: route.params.id }),
    },
];