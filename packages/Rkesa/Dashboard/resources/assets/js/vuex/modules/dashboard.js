const state = {
    field_types: null,
    entities: null,
    new_field_id: 1,
    widgets: [],
};
const mutations = {
    update_dashboard_name(state, name) {
        state.dashboard_name = name;
    },
    set_field_types(state, data) {
        state.field_types = data;
    },
    initial_data(state, {data, getters}) {
        var entities = {};
        state.dashboard_name = '';

        for (var i = 0; i < data.statuses.length; i++) {
            var status = data.statuses[i];
            entities[status.id] = status;
            entities[status.id].hide = false;
            entities[status.id].fields = [{
                id: i + 1,
                type: data.field_types[0].value,
            }];
        }
        state.entities = entities;

        state.widgets = [];
    },
    stored_data(state, data) {
        var flat_statuses = {};
        data.statuses.forEach((status) => {
            flat_statuses[status.id] = status;
        });

        var fde = {};
        data.dashboard.entities.forEach((de) => {
            var ss_id = de.service_state_id;
            fde[ss_id] = {};
            fde[ss_id].id = de.service_state_id;
            fde[ss_id].entity_id = de.id;
            fde[ss_id].hide = (de.hide != 0);
            fde[ss_id].icon = flat_statuses[ss_id].icon;
            fde[ss_id].name = flat_statuses[ss_id].name;
            fde[ss_id].order = flat_statuses[ss_id].order;
            fde[ss_id].type = flat_statuses[ss_id].type;
            fde[ss_id].fields = de.fields;
        });

        state.dashboard_id = data.dashboard.id;
        state.entities = fde;

        var widgets = data.dashboard.widgets;

        state.widgets = widgets;
    },
    add_widget(state){
        let widgets = state.widgets;

        if (widgets.length < 10) {
            let max_id = Math.max.apply(null, widgets.map(w => w.id).concat(0));

            widgets.push({
                // Random id
                id: max_id + 1,
                data_type: 1,
                size: 1,
                widget_type: 1,
            });

            state.widgets = widgets;
        }
    },
    remove_widget(state, {id, getters}){
        let index = state.widgets.indexOf(getters.getWidgetsById[id]);

        state.widgets.splice(index, 1);
    },
    add_entity_field(state, id) {
        let field = {
            id: 'a' + state.new_field_id,
            type: 0,
            dashboard_entity_id: id,
        };
        state.new_field_id += 1;
        state.entities[id].fields.push(field);
    },
    remove_entity_field(state, data) {
        let field_to_remove = state.entities[data.entity_id].fields.find(f => f.id == data.field_id);
        let index_to_remove = state.entities[data.entity_id].fields.indexOf(field_to_remove);
        state.entities[data.entity_id].fields.splice(index_to_remove, 1);
    },
    update_entity_fields(state, data) {
        Vue.set(state.entities[data.entity_id].fields.find(f => f.id == data.field_id), 'type', parseInt(data.field_value, 10));
    },
    toggle_entity_hide(state, id) {
        state.entities[id].hide = !state.entities[id].hide
    },
    update_widget_status(state, {data, getters}){
        Vue.set(getters.getWidgetsById[data.id], 'service_state_id', data.value);
    },
    update_widget_size(state, {data, getters}){
        Vue.set(getters.getWidgetsById[data.id], 'size', data.value);
    },
    update_widget_type(state, {data, getters}) {
        Vue.set(getters.getWidgetsById[data.id], 'widget_type', data.value);
    },
    update_widget_data_type(state, {data, getters}) {
        let index = state.widgets.indexOf(getters.getWidgetsById[data.id]);
        let curWid = Object.assign({}, getters.getWidgetsById[data.id]);

        curWid.data_type = data.value;

        let first_status = Object.values(state.entities)[0].id;

        // initial values
        switch (curWid.data_type) {
        case 1:
            curWid.widget_type = 1;
            curWid.service_state_id = null;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            break;
        case 2:
            curWid.widget_type = 1;
            curWid.service_state_id = null;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            break;
        case 3:
            curWid.widget_type = 1;
            curWid.service_state_id = first_status;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            break;
        case 4:
            curWid.widget_type = 1;
            curWid.service_state_id = first_status;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            break;
        case 5:
            curWid.widget_type = 1;
            curWid.service_state_id = first_status;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            break;
        case 6:
            curWid.widget_type = 1;
            curWid.service_state_id = first_status;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            break;
        case 7:
            curWid.widget_type = 4;
            curWid.service_state_id = first_status;
            curWid.color1 = '#33af0e';
            curWid.color2 = '#2f7bf5';
            curWid.color3 = '#480c86';
            curWid.color4 = '#232225';
            curWid.event_type_id = getters.getEventTypesProxy[0].id;
            break;
        case 8:
            curWid.widget_type = 1;
            curWid.service_state_id = null;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            break;
        case 9:
            curWid.widget_type = 5;
            curWid.service_state_id = null;
            curWid.color1 = null;
            curWid.color2 = null;
            curWid.color3 = null;
            curWid.color4 = null;
            curWid.event_type_id = null;
            curWid.data = null;
            break;
        }

        Vue.set(state.widgets, index, curWid);
    },
    update_widget_color1(state, {data, getters}) {
        Vue.set(getters.getWidgetsById[data.id], 'color1', data.value);
    },
    update_widget_color2(state, {data, getters}) {
        Vue.set(getters.getWidgetsById[data.id], 'color2', data.value);
    },
    update_widget_color3(state, {data, getters}) {
        Vue.set(getters.getWidgetsById[data.id], 'color3', data.value);
    },
    update_widget_color4(state, {data, getters}) {
        Vue.set(getters.getWidgetsById[data.id], 'color4', data.value);
    },
    update_widget_event(state, {data, getters}) {
        Vue.set(getters.getWidgetsById[data.id], 'event_type_id', data.value);
    },
    update_widget_data(state, {data, getters}) {
        Vue.set(getters.getWidgetsById[data.id], 'data', data.value);
    }
};
const actions = {
    dashboardName({commit}, name) {
        commit('update_dashboard_name', name);
    },
    field_types({ commit }, data) {
        commit('set_field_types', data);
    },
    initialData({commit, getters}, data) {
        commit('initial_data', {data, getters});
    },
    storedData({commit}, data) {
        commit('stored_data', data);
    },
    addWidget({commit}, data) {
        commit('add_widget', data);
    },
    removeWidget({commit, getters}, data) {
        commit('remove_widget', {data, getters});
    },
    addEntityField({commit, state}, id) {
        commit('add_entity_field', id);
    },
    removeEntityField({commit, state}, data) {
        commit('remove_entity_field', data);
    },
    updateEntityFields({commit}, data) {
        commit('update_entity_fields', data);
    },
    toggleEntityHide({commit}, id) {
        commit('toggle_entity_hide', id);
    },
    updateWidgetType({commit, getters}, data) {
        commit('update_widget_type', {data, getters});
    },
    updateWidgetDataType({commit, getters}, data) {
        commit('update_widget_data_type', {data, getters});
    },
    updateWidgetSize({commit, getters}, data) {
        commit('update_widget_size', {data, getters});
    },
    updateWidgetStatus({commit, getters}, data) {
        commit('update_widget_status', {data, getters});
    },
    updateWidgetColor1({commit, getters}, data) {
        commit('update_widget_color1', {data, getters});
    },
    updateWidgetColor2({commit, getters}, data) {
        commit('update_widget_color2', {data, getters});
    },
    updateWidgetColor3({commit, getters}, data) {
        commit('update_widget_color3', {data, getters});
    },
    updateWidgetColor4({commit, getters}, data) {
        commit('update_widget_color4', {data, getters});
    },
    updateWidgetEvent({commit, getters}, data) {
        commit('update_widget_event', {data, getters});
    },
    updateWidgetData({commit, getters}, data) {
        commit('update_widget_data', {data, getters});
    },
};
const getters = {
    types: (state) => {
        return state.field_types;
    },
    entities: (state) => {
        return state.entities;
    },
    entity_status_name: (state, getters) => (entity_id) => {
        return state.entities[entity_id].name;
    },
    entity_hide: (state) => (entity_id) => {
        return state.entities[entity_id].hide;
    },
    entity_type: (state) => (entity_id) => {
        return state.entities[entity_id].type;
    },
    entity_fields: (state, getters) => (entity_id) => {
        return state.entities[entity_id].fields;
    },
    entities_for_store: (state) => {
        let output = [];
        if (state.entities) {
            Object.values(state.entities).forEach((value) => {
                var fields = [];
                value.fields.forEach((field) => {
                    fields.push({
                        'type': field.type,
                        'event_type_id': field.event_type_id,
                    })
                });
                output.push({
                    'hide': value.hide,
                    'service_state_id': value.id,
                    'fields': fields,
                })
            });
        }
        return output;
    },
    widgets: (state) => {
        return state.widgets
    },
    getWidgetsById: state => state.widgets.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    dashboard_name: (state) => {
        return state.dashboard_name;
    },
    getEventTypesProxy: (state, getters, rootState, rootGetters) => {
        return rootGetters.getEventTypes;
    },
};
export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters,
}
