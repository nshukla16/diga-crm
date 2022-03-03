import LoadingComponent from '../../../../../../../resources/assets/js/components/loading.vue';

// Project
const projects_index = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/project/index.vue'),
    loading: LoadingComponent,
});
const project_form = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/project/_form.vue'),
    loading: LoadingComponent,
});
const project_show = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/project/show.vue'),
    loading: LoadingComponent,
});
const project_history = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/project/history/index.vue'),
    loading: LoadingComponent,
});
// Manufacturer
const manufacturers_index = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/manufacturer/index.vue'),
    loading: LoadingComponent,
});
const manufacturer_form = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/manufacturer/_form.vue'),
    loading: LoadingComponent,
});
const manufacturer_show = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/manufacturer/show.vue'),
    loading: LoadingComponent,
});
// Equipments
const equipments_index = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/equipment/index.vue'),
    loading: LoadingComponent,
});
const equipment_form = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/equipment/_form.vue'),
    loading: LoadingComponent,
});
// Specifications
const specifications_index = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/specification/index.vue'),
    loading: LoadingComponent,
});
const specification_form = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/specification/_form.vue'),
    loading: LoadingComponent,
});
// Carriers
const carriers_index = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/carrier/index.vue'),
    loading: LoadingComponent,
});
const carrier_form = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/carrier/_form.vue'),
    loading: LoadingComponent,
});
const carrier_show = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/carrier/show.vue'),
    loading: LoadingComponent,
});

const project_settings = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/project_settings/settings.vue'),
    loading: LoadingComponent,
});

// Entity Legal
const legal_entities_index = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/legal_entity/index.vue'),
    loading: LoadingComponent,
});
const legal_entity_show = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/legal_entity/show.vue'),
    loading: LoadingComponent,
});
const legal_entity_form = () => ({
    component: import(/* webpackChunkName: "settings" */ '../components/legal_entity/_form.vue'),
    loading: LoadingComponent,
});
// Technical documents
const technical_documents = () => ({
    component: import(/* webpackChunkName: "project" */ '../components/technical_documents/index.vue'),
    loading: LoadingComponent,
});

export default [
    // Project
    {
        path: '/projects',
        name: 'projects_index',
        component: projects_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/projects/create',
        name: 'project_create',
        component: project_form,
    },
    {
        path: '/projects/:id(\\d+)/edit',
        name: 'project_edit',
        component: project_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/projects/:id(\\d+)/history',
        name: 'project_history',
        component: project_history,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/projects/:id(\\d+)/:tab?',
        name: 'project_show',
        component: project_show,
        props: (route) => ({id: route.params.id, tab: route.params.tab }),
    },
    // Manufacturer
    {
        path: '/manufacturers',
        name: 'manufacturers_index',
        component: manufacturers_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/manufacturers/create',
        name: 'manufacturer_create',
        component: manufacturer_form,
    },
    {
        path: '/manufacturers/:id(\\d+)/edit',
        name: 'manufacturer_edit',
        component: manufacturer_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/manufacturers/:id(\\d+)',
        name: 'manufacturer_show',
        component: manufacturer_show,
        props: (route) => ({id: route.params.id }),
    },
    // Equipment
    {
        path: '/equipments',
        name: 'equipments_index',
        component: equipments_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/equipments/create',
        name: 'equipment_create',
        component: equipment_form,
    },
    {
        path: '/equipments/:id(\\d+)/edit',
        name: 'equipment_edit',
        component: equipment_form,
        props: (route) => ({id: route.params.id }),
    },
    // Specification
    {
        path: '/specifications',
        name: 'specifications_index',
        component: specifications_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/specifications/create',
        name: 'specification_create',
        component: specification_form,
    },
    {
        path: '/specifications/:id(\\d+)/edit',
        name: 'specification_edit',
        component: specification_form,
        props: (route) => ({id: route.params.id }),
    },
    // Carriers
    {
        path: '/carriers',
        name: 'carriers_index',
        component: carriers_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/carriers/create',
        name: 'carrier_create',
        component: carrier_form,
    },
    {
        path: '/carriers/:id(\\d+)',
        name: 'carrier_show',
        component: carrier_show,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/carriers/:id(\\d+)/edit',
        name: 'carrier_edit',
        component: carrier_form,
        props: (route) => ({id: route.params.id }),
    },
    // Settings
    {
        path: '/settings/projects',
        name: 'project_settings',
        component: project_settings,
    },
    // Legal entities
    {
        path: '/legal_entities',
        name: 'legal_entities_index',
        component: legal_entities_index,
        props: (route) => ({ offset: route.query.offset }),
    },
    {
        path: '/legal_entities/:id(\\d+)',
        name: 'legal_entity_show',
        component: legal_entity_show,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/legal_entities/create',
        name: 'legal_entity_create',
        component: legal_entity_form,
    },
    {
        path: '/legal_entities/:id(\\d+)/edit',
        name: 'legal_entity_edit',
        component: legal_entity_form,
        props: (route) => ({id: route.params.id }),
    },
    {
        path: '/legal_entities/:id(\\d+)',
        name: 'legal_entities',
        component: legal_entity_show,
        props: (route) => ({id: route.params.id }),
    },
    // technical documents
    {
        path: '/technical_documents',
        name: 'technical_documents_index',
        component: technical_documents
    },
];