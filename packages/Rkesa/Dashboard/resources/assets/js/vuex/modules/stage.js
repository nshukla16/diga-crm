import i18n from './../../../../../../../../resources/assets/js/i18n';

const state = {
    filters: {
        // service states
        range: {
            start: {
                date: null,
            },
            end: {
                date: null,
            },
        },
        responsible: null,
        use_range: null,
        // widgets
        w_range: {
            start: {
                date: null,
            },
            end: {
                date: null,
            },
        },
        w_use_range: null,
    },
    dashboard: {
        entities: null,
        widgets: null,
    },
    entity_field_types: null,
    users: null,
    full_list: {
        visible_rows_count: 0,
        total_rows_count: 0,
        rows: [],
        loading: true,
        total_master_sum: 0,
    },
};
const mutations = {
    init_filters(state, filters) {
        state.filters = filters;
    },
    init_users(state, users) {
        state.users = users;
    },
    init_entity_field_types(state, types) {
        let entity_field_types = {};
        for (var i = 0; i < types.length; i++) {
            entity_field_types[types[i].value] = types[i];
        }
        state.entity_field_types = entity_field_types;
    },
    set_responsible(state, id) {
        state.filters.responsible = id
    },
    set_range(state, data) {
        state.filters.range.start.date = data[0];
        state.filters.range.end.date = data[1];
    },
    set_use_range(state, data) {
        state.filters.use_range = data;
    },
    update_w_range(state, data){
        state.filters.w_range.start.date = data[0];
        state.filters.w_range.end.date = data[1];
    },
    update_w_use_range(state, data){
        state.filters.w_use_range = data;
    },
    init_dashboard(state, dashboard) {
        Vue.set(state, 'dashboard', dashboard);
    },
    set_entities(state, data) {
        var entities = {};
        for (var i = 0; i < data.length; i++) {
            var entity = data[i];
            entities[entity.service_state_id] = Object.assign({}, entity);
            for (var n = 0; n < entities[entity.service_state_id].fields.length; n++) {
                var field = entities[entity.service_state_id].fields[n];
                field.text = state.entity_field_types[field.type].text;
            }
        }
        Vue.set(state.dashboard, 'entities', entities);
    },
    set_widgets(state, data) {
        let widgets = {};
        for (var i = 0; i < data.length; i++) {
            var widget = data[i];
            widgets[widget.id] = widget;
        }
        Vue.set(state.dashboard, 'widgets', widgets);
    },
    set_entity_rows(state, data) {
        Vue.set(state.dashboard.entities[data.index], 'total_rows_count', data.total_rows_count);
        Vue.set(state.dashboard.entities[data.index], 'visible_rows_count', data.rows.length);
        Vue.set(state.dashboard.entities[data.index], 'total_master_sum', data.total_master_sum);
        Vue.set(state.dashboard.entities[data.index], 'master_sum_index', data.master_sum_index);
        Vue.set(state.dashboard.entities[data.index], 'rows', data.rows);
    },
    update_entity_loading(state, data){
        Vue.set(state.dashboard.entities[data.index], 'loading', data.value);
    },
    update_widget_loading(state, data){
        Vue.set(state.dashboard.widgets[data.id], 'loading', data.value);
    },
    set_full_entity_rows(state, data) {
        Vue.set(state.full_list, 'total_rows_count', data.total_rows_count);
        Vue.set(state.full_list, 'visible_rows_count', data.rows.length);
        Vue.set(state.full_list, 'total_master_sum', data.total_master_sum);
        Vue.set(state.full_list, 'master_sum_index', data.master_sum_index);
        Vue.set(state.full_list, 'rows', data.rows);
        Vue.set(state.full_list, 'loading', false);
    },
    toggle_entity(state, data) {
        var count = state.dashboard.entities[data.index].visible_rows_count;
        var rows_count = state.dashboard.entities[data.index].rows.length;

        const newEntity = Object.assign({}, state.dashboard.entities[data.index]);
        if (count != rows_count) {
            count = rows_count;
        } else if (rows_count < state.dashboard.entity_default_rows_count) {
            count = rows_count;
        } else {
            count = state.dashboard.entity_default_rows_count;
        }

        Vue.set(state.dashboard.entities[data.index], 'visible_rows_count', count);
    },
    set_widget_data(state, data) {
        Vue.set(state.dashboard.widgets[data.id], 'series', data.series);
        Vue.set(state.dashboard.widgets[data.id], 'additional_data', data.additional_data);
        Vue.set(state.dashboard.widgets[data.id], 'categories', data.categories);
        Vue.set(state.dashboard.widgets[data.id], 'x_title', data.x_title);
        Vue.set(state.dashboard.widgets[data.id], 'y_title', data.y_title);
        Vue.set(state.dashboard.widgets[data.id], 'rejected_count', data.rejected_count);
        Vue.set(state.dashboard.widgets[data.id], 'time_to_reject', data.time_to_reject);
        Vue.set(state.dashboard.widgets[data.id], 'time_to_sold', data.time_to_sold);
        Vue.set(state.dashboard.widgets[data.id], 'funnel_items', data.funnel_items);
        Vue.set(state.dashboard.widgets[data.id], 'cities', data.cities);
        Vue.set(state.dashboard.widgets[data.id], 'service_addresses', data.service_addresses);
    },
};
const actions = {
    init({ commit }, data) {
        return new Promise((resolve, reject) => {
            commit('init_users', data.users);
            commit('init_filters', data.filters);
            commit('init_entity_field_types', data.entity_field_types);
            commit('set_use_range', false);
            commit('update_w_use_range', false);
            resolve();
        }).catch((error => {
            console.log(error);
        }));
    },
    responsible({ commit, dispatch }, id) {
        commit('set_responsible', id);
        dispatch('get_entities');
    },
    range({ commit, dispatch }, data) {
        commit('set_range', data);
        dispatch('get_entities');
    },
    set_use_range({ commit, dispatch }, data) {
        commit('set_use_range', data);
        dispatch('get_entities');
    },
    updateWRange({ commit, dispatch }, data) {
        commit('update_w_range', data);
        dispatch('get_widgets');
    },
    updateWUseRange({ commit, dispatch }, data) {
        commit('update_w_use_range', data);
        dispatch('get_widgets');
    },
    get_entities({ commit, dispatch, state }){
        Object.values(state.dashboard.entities).forEach((entity) => {
            dispatch('get_short_entity', {
                status_id: entity.service_state_id,
            })
        });
    },
    get_widgets({ commit, dispatch, state }){
        Object.values(state.dashboard.widgets).forEach((widget) => {
            dispatch('get_widget', {
                id: widget.id,
            })
        });
    },
    get_dashboard({ commit, dispatch, state }) {
        return new Promise((resolve, reject) => {
            Vue.http.get('/api/me/dashboard').then((response) => {
                commit('init_dashboard', response.body);
                commit('set_entities', response.body.entities);
                dispatch('get_entities');
                commit('set_widgets', response.body.widgets);
                dispatch('get_widgets');
                resolve();
            }).catch((error => {
                console.log(error);
            }));
        });
    },
    get_short_entity({ commit, state }, data) {
        return new Promise((resolve, reject) => {
            data.fields = state.dashboard.entities[data.status_id].fields;
            data.id = state.dashboard.entities[data.status_id].id;
            data.responsible = state.filters.responsible;
            data.range = state.filters.range;
            data.use_range = state.filters.use_range;
            commit('update_entity_loading', { index: data.status_id, value: true});
            Vue.http.post('/api/me/dashboard/short_entity', data).then((response) => {
                commit('set_entity_rows', {
                    index: data.status_id,
                    rows: response.body.rows,
                    total_rows_count: response.body.total_rows_count,
                    total_master_sum: response.body.total_master_sum,
                    master_sum_index: response.body.master_sum_index,
                });
                commit('update_entity_loading', { index: data.status_id, value: false});
                resolve();
            }).catch((error => {
                console.log(error);
            }));
        });
    },
    get_full_entity({ commit, state }, data) {
        return new Promise((resolve, reject) => {
            data.fields = state.dashboard.entities[data.index].fields;
            data.id = state.dashboard.entities[data.index].id;
            data.responsible = state.filters.responsible;
            data.range = state.filters.range;
            data.use_range = state.filters.use_range;
            data.status_id = state.dashboard.entities[data.index].service_state_id;
            Vue.http.post('/api/me/dashboard/full_entity', data).then((response) => {
                commit('set_full_entity_rows', {
                    index: data.index,
                    rows: response.body.rows,
                    total_rows_count: response.body.total_rows_count,
                    total_master_sum: response.body.total_master_sum,
                    master_sum_index: response.body.master_sum_index,
                });
                resolve();
            }).catch((error => {
                console.log(error);
            }));
        });
    },
    get_toggle_entity({ commit }, data) {
        commit('toggle_entity', data);
    },
    get_widget({ commit, state }, data) {
        return new Promise((resolve, reject) => {
            // data.responsible = state.filters.responsible;
            data.range = state.filters.w_range;
            commit('update_widget_loading', { id: data.id, value: true});
            data.use_range = state.filters.w_use_range;
            Vue.http.post('/api/me/dashboard/widget', data).then((response) => {
                commit('set_widget_data', {
                    id: response.body.widget.id,
                    series: response.body.widget.series,
                    additional_data: response.body.widget.additional_data,
                    categories: response.body.widget.categories,
                    x_title: response.body.widget.x_title,
                    y_title: response.body.widget.y_title,
                    rejected_count: response.body.widget.rejected_count,
                    time_to_reject: response.body.widget.time_to_reject,
                    time_to_sold: response.body.widget.time_to_sold,
                    funnel_items: response.body.widget.funnel_items,
                    cities: response.body.widget.cities,
                    service_addresses: response.body.widget.service_addresses,
                });
                commit('update_widget_loading', { id: data.id, value: false});
                resolve();
            }).catch((error => {
                console.log(error);
            }));
        });
    },
};
const getters = {
    getChartTitle: (state) => (id) => {
        let widget = state.dashboard.widgets[id];
        switch (widget.data_type){
        case 1:
            return i18n.t('dashboard.statuses') + ' (' + widget.additional_data.total_count + ')';
        case 2:
            return i18n.t('dashboard.referrers') + ' (' + widget.additional_data.total_count + ')';
        case 3:
            return i18n.t('dashboard.avg_status_time') + ' ' + widget.state.name;
        case 4:
            return i18n.t('dashboard.avg_status_price') + ' ' + widget.state.name;
        case 5:
            return i18n.t('dashboard.services_with_state_count') + ' ' + widget.state.name;
        case 6:
            return i18n.t('dashboard.services_with_state_sum') + ' ' + widget.state.name;
        case 7:
            return i18n.t('dashboard.Status_duration') + ' ' + widget.state.name;
        case 8:
            return i18n.t('dashboard.companies_referrers') + ' (' + widget.additional_data.total_count + ')';
        }
    },
    range: (state) => {
        return [state.filters.range.start.date, state.filters.range.end.date];
    },
    use_range: (state) => {
        return state.filters.use_range;
    },
    w_range: (state) => {
        return [state.filters.w_range.start.date, state.filters.w_range.end.date];
    },
    w_use_range: (state) => {
        return state.filters.w_use_range;
    },
    responsible: (state) => {
        return state.filters.responsible;
    },
    users: (state) => {
        return state.users;
    },
    entities: (state) => {
        return state.dashboard.entities;
    },
    entity: (state) => (id, limit) => {
        return state.dashboard.entities[id];
    },
    entity_rows: (state) => (id) => {
        let rows = [];
        for (var i = 0; i < state.dashboard.entities[id].visible_rows_count; i++) rows.push(state.dashboard.entities[id].rows[i]);
        return rows;
    },
    full_entity_rows: (state) => (id) => {
        let rows = [];
        for (var i = 0; i < state.full_list.visible_rows_count; i++) rows.push(state.full_list.rows[i]);
        return rows;
    },
    master_sum_index: (state) => (id) => {
        return state.dashboard.entities[id].master_sum_index;
    },
    widget: (state) => (id) => {
        return state.dashboard.widgets[id];
    },
    widgets: (state) => {
        return state.dashboard.widgets;
    },
};

export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters,
}
