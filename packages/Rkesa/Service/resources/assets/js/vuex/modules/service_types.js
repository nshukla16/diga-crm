import Vue from 'vue'

const state = {
    status: '',
    service_types: [],
};

const getters = {
    getServiceTypes: state => state.service_types,
    getServiceTypesById: state => state.service_types.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getServiceTypesStatus: state => state.status,
};

const actions = {
    serviceTypesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('serviceTypesRequest');
            axios.get('/api/service_types')
                .then((resp) => {
                    commit('serviceTypesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('serviceTypesError');
                    reject();
                })
        });
    },
};

const mutations = {
    serviceTypesRequest: (state) => {
        state.status = 'loading';
    },
    serviceTypesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'service_types', resp);
    },
    serviceTypesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}