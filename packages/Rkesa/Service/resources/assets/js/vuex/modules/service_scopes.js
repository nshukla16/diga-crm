import Vue from 'vue'

const state = {
    status: '',
    service_scopes: [],
};

const getters = {
    getServiceScopes: state => state.service_scopes,
    getServiceScopesById: state => state.service_scopes.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getServiceScopesStatus: state => state.status,
};

const actions = {
    serviceScopesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('serviceScopesRequest');
            axios.get('/api/service_scopes')
                .then((resp) => {
                    commit('serviceScopesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('serviceScopesError');
                    reject();
                })
        });
    },
};

const mutations = {
    serviceScopesRequest: (state) => {
        state.status = 'loading';
    },
    serviceScopesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'service_scopes', resp);
    },
    serviceScopesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}