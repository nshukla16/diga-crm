import estimate_forks_settings from '../components/estimate_fork/index.vue';
import estimate_fork_form from '../components/estimate_fork/_form.vue';

export default [
    {
        path: '/settings/forks',
        name: 'estimate_forks_settings',
        component: estimate_forks_settings,
    },
    {
        path: '/settings/forks/create',
        name: 'estimate_fork_create',
        component: estimate_fork_form,
    },
    {
        path: '/settings/forks/:id(\\d+)/edit',
        name: 'estimate_fork_edit',
        component: estimate_fork_form,
        props: (route) => ({id: route.params.id }),
    },
];