import Vue from 'vue'

const state = {
    status: '',
    service_priorities: [],
};

const getters = {
    getServicePriorities: state => state.service_priorities,
    getServicePrioritiesById: state => state.service_priorities.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getServicePrioritiesStatus: state => state.status,
};

const actions = {
    servicePrioritiesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('servicePrioritiesRequest');
            axios.get('/api/service_priorities')
                .then((resp) => {
                    commit('servicePrioritiesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('servicePrioritiesError');
                    reject();
                })
        });
    },
};

const mutations = {
    servicePrioritiesRequest: (state) => {
        state.status = 'loading';
    },
    servicePrioritiesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'service_priorities', resp);
    },
    servicePrioritiesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}