// Client
import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

const companies_index = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/client/index.vue'),
    loading: LoadingComponent,
});
const company_form = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/client/_form.vue'),
    loading: LoadingComponent,
});
const company_show = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/client/show.vue'),
    loading: LoadingComponent,
});
const contacts_index = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/contact/index.vue'),
    loading: LoadingComponent,
});
const contact_form = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/contact/_form.vue'),
    loading: LoadingComponent,
});
const contact_show = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/contact/show.vue'),
    loading: LoadingComponent,
});
const service_form = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/card/services/_form.vue'),
    loading: LoadingComponent,
});
const client_settings = () => ({
    component: import ( /* webpackChunkName: "settings" */ '../components/client_settings/referrers.vue'),
    loading: LoadingComponent,
});
const file_show = () => ({
    component: import ( /* webpackChunkName: "client" */ '../../../../../../../resources/assets/js/components/home/show_pdf.vue'),
    loading: LoadingComponent,
});
const calls_index = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/call/index.vue'),
    loading: LoadingComponent,
});
const estimate_group_materials = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/card/services/estimate_group_materials.vue'),
    loading: LoadingComponent,
});
const estimate_group_workers = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/card/services/estimate_group_workers.vue'),
    loading: LoadingComponent,
});
const subcontractors = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/card/services/subcontractors.vue'),
    loading: LoadingComponent,
});
const chat_access = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/card/services/chat_access.vue'),
    loading: LoadingComponent,
});
const summary = () => ({
    component: import ( /* webpackChunkName: "client" */ '../components/card/services/summary.vue'),
    loading: LoadingComponent,
});


export default [{
        path: '/contacts',
        name: 'contacts_index',
        component: contacts_index,
        props: (route) => ({ referrer: route.query.referrer_id, offset: route.query.offset }),
    },
    {
        path: '/clients',
        name: 'clients_index',
        component: contacts_index,
        props: (route) => ({ referrer: route.query.referrer_id, offset: route.query.offset }),
    },
    {
        path: '/contacts/:id(\\d+)',
        name: 'contact_show',
        component: contact_show,
        props: (route) => ({ id: route.params.id, service_id: route.query.service_id }),
    },
    {
        path: '/clients/:id(\\d+)',
        name: 'client_show',
        component: contact_show,
        props: (route) => ({ id: route.params.id, service_id: route.query.service_id }),
    },
    {
        path: '/contacts/create',
        name: 'contact_create',
        component: contact_form,
        props: (route) => ({ company_id: route.query.company_id }),
    },
    {
        path: '/clients/create',
        name: 'client_create',
        component: contact_form,
        props: (route) => ({ company_id: route.query.company_id }),
    },
    {
        path: '/contacts/:id(\\d+)/edit',
        name: 'contact_edit',
        component: contact_form,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/companies',
        name: 'companies_index',
        component: companies_index,
        props: (route) => ({ referrer: route.query.referrer_id, offset: route.query.offset }),
    },
    {
        path: '/companies/create',
        name: 'company_create',
        component: company_form,
    },
    {
        path: '/companies/create/:group_id(\\d+)',
        name: 'company_create_group',
        component: company_form,
        props: (route) => ({ group_id: route.params.group_id }),
    },
    {
        path: '/companies/:id(\\d+)/edit',
        name: 'company_edit',
        component: company_form,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/companies/:id(\\d+)',
        name: 'company_show',
        component: company_show,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/companies/:id(\\d+)/pdf',
        name: 'company_show_pdf',
        component: file_show,
        props: (route) => ({ src: '/api/companies/get_link/' + route.params.id }),
    },
    {
        path: '/services/create',
        name: 'service_create',
        component: service_form,
        props: (route) => ({ contact_id: route.query.contact_id }),
    },
    {
        path: '/services/:id(\\d+)/edit',
        name: 'service_edit',
        component: service_form,
        props: (route) => ({ id: route.params.id, contact_id: route.query.contact_id, from: route.query.from }),
    },
    {
        path: '/settings/clients',
        name: 'client_settings',
        component: client_settings,
    },
    {
        path: '/calls',
        name: 'calls_index',
        component: calls_index,
    },
    {
        path: '/services/:id(\\d+)/estimate_group_materials',
        name: 'estimate_group_materials',
        component: estimate_group_materials,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/services/:id(\\d+)/estimate_group_workers',
        name: 'estimate_group_workers',
        component: estimate_group_workers,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/services/:id(\\d+)/subcontractors',
        name: 'subcontractors',
        component: subcontractors,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/services/:id(\\d+)/chat_access',
        name: 'chat_access',
        component: chat_access,
        props: (route) => ({ id: route.params.id }),
    },
    {
        path: '/services/:id(\\d+)/summary',
        name: 'summary',
        component: summary,
        props: (route) => ({ id: route.params.id }),
    },
]