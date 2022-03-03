// Hr
import users_index from '../components/hr/index.vue';
import users_form from '../components/hr/_form.vue';
import timetracker_reports from '../components/timetracker/index.vue';
import timetracker_personal from '../components/timetracker/timetracker.vue';
import file_show from '../../../../../../../resources/assets/js/components/home/show_pdf.vue';
import kpi_users_and_groups from '../components/kpi/users_and_groups/index.vue';
import kpi_users_and_groups_form from '../components/kpi/users_and_groups/_form.vue';
import kpi_users_and_groups_by_user from '../components/kpi/productivity/index.vue';
import groups_index from '../components/groups/index.vue';
import contractors_index from '../components/groups/teams_and_groups.vue';
import timetracker_report_index from '../components/timetracker/report.vue';

export default [
    // HR
    {
        path: '/hr',
        name: 'users_index',
        component: users_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/hr/create',
        name: 'user_create',
        component: users_form,
    },
    {
        path: '/hr/:id(\\d+)/edit',
        name: 'user_edit',
        component: users_form,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/hr/blank',
        name: 'user_blank',
        component: file_show,
        props: (route) => ({ src: '/api/user_blank/get_link/' }),
    },
    {
        path: '/hr/:id(\\d+)/card',
        name: 'user_card',
        component: file_show,
        props: (route) => ({ src: '/api/users/get_link/' + route.params.id }),
    },
    {
        path: '/timetracker/totals',
        name: 'timetracker_reports',
        component: timetracker_reports,
    },
    {
        path: '/timetracker/personal',
        name: 'timetracker_engine',
        component: timetracker_personal,
    },
    {
        path: '/kpi/users_and_groups',
        name: 'kpi_users_and_groups',
        component: kpi_users_and_groups
    },
    {
        path: '/kpi/users_and_groups/create',
        name: 'users_and_groups_create',
        component: kpi_users_and_groups_form,
    },
    {
        path: '/kpi/users_and_groups/:id(\\d+)/edit',
        name: 'users_and_groups_edit',
        component: kpi_users_and_groups_form,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/kpi/users_and_groups/user/:id(\\d+)/:isGroup',
        name: 'users_and_groups_by_user',
        component: kpi_users_and_groups_by_user,
        props: (route) => ({ id: route.params.id, isGroup: route.params.isGroup }),
    },
    {
        path: '/groups',
        name: 'groups_index',
        component: groups_index,
    },
    {
        path: '/timetracker/report',
        name: 'timetracker_report',
        component: timetracker_report_index
    },
    {
        path: '/contractors',
        name: 'contractors_index',
        component: contractors_index,
    },
];