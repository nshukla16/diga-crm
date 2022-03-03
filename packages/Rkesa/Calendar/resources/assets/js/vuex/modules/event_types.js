import Vue from 'vue'

const state = {
    status: '',
    event_types: [],
};

const getters = {
    getEventTypes: state => state.event_types,
    getEventTypesById: state => state.event_types.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getEventTypesStatus: state => state.status,
};

const actions = {
    eventTypesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('eventTypesRequest');
            axios.get('/api/event_types?fields=id,title,order,color,icon,show_description')
                .then((resp) => {
                    commit('eventTypesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('eventTypesError');
                    reject();
                })
        });
    },
};

const mutations = {
    eventTypesRequest: (state) => {
        state.status = 'loading';
    },
    eventTypesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'event_types', resp);
    },
    eventTypesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}