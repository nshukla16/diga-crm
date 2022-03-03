import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const estimate_plannings_index = () => ({
    component: import(/* webpackChunkName: "gantt" */ '../components/estimate_planning/index.vue'),
    loading: LoadingComponent,
});

const estimate_plannings_show = () => ({
    component: import(/* webpackChunkName: "gantt" */ '../components/estimate_planning/show.vue'),
    loading: LoadingComponent,
});

const user_plannings_index = () => ({
    component: import(/* webpackChunkName: "gantt" */ '../components/users_planning/index.vue'),
    loading: LoadingComponent,
});

const user_plannings_show = () => ({
    component: import(/* webpackChunkName: "gantt" */ '../components/users_planning/show.vue'),
    loading: LoadingComponent,
});

const planning_settings = () => ({
    component: import(/* webpackChunkName: "gantt" */ '../components/planning_settings/planning_settings.vue'),
    loading: LoadingComponent,
});

export default [
    {
        path: '/estimate_plannings',
        name: 'estimate_plannings_index',
        component: estimate_plannings_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/estimate_plannings/:id',
        name: 'estimate_planning_show',
        component: estimate_plannings_show,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/user_plannings',
        name: 'user_plannings_index',
        component: user_plannings_index,
        props: (route) => ({offset: route.query.offset }),
    },
    {
        path: '/user_plannings/:id',
        name: 'user_planning_show',
        component: user_plannings_show,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/settings/planning',
        name: 'planning_settings',
        component: planning_settings,
    },
];