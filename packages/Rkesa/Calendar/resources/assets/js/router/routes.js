// Calendar
import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const calendar_index = () => ({
    component: import(/* webpackChunkName: "calendar" */ '../components/calendar/index.vue'),
    loading: LoadingComponent,
});
const checklists_settings = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/checklist/index.vue'),
    loading: LoadingComponent,
});
const checklist_form = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/checklist/_form.vue'),
    loading: LoadingComponent,
});
const calendar_settings = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/calendar_settings/colors_and_icons.vue'),
    loading: LoadingComponent,
});
const file_show = () => ({
    component: import(/* webpackChunkName: "calendar" */ '../../../../../../../resources/assets/js/components/home/show_pdf.vue'),
    loading: LoadingComponent,
});
const vacations_calendar_index = () => ({
    component: import(/* webpackChunkName: "calendar" */ '../components/vacations_calendar/index.vue'),
    loading: LoadingComponent,
});

export default [
    {
        path: '/calendar',
        name: 'calendar_index',
        component: calendar_index,
    },
    {
        path: '/settings/calendar',
        name: 'calendar_settings',
        component: calendar_settings,
    },
    {
        path: '/settings/checklists',
        name: 'checklists_settings',
        component: checklists_settings,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/settings/checklists/:id(\\d+)',
        name: 'checklist_show',
        component: file_show,
        props: (route) => ({src: '/api/checklists/get_link/' + route.params.id }),
    },
    {
        path: '/settings/checklists/:id(\\d+)/edit',
        name: 'checklist_edit',
        component: checklist_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/settings/checklists/create',
        name: 'checklist_create',
        component: checklist_form,
    },
    {
        path: '/fills/:id(\\d+)',
        name: 'fill_show',
        component: file_show,
        props: (route) => ({src: '/api/fills/get_link/' + route.params.id }),
    },
    {
        path: '/vacations_calendar',
        name: 'vacations_calendar_index',
        component: vacations_calendar_index,
    },
];