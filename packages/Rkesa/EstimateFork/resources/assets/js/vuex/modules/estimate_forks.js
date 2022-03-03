import Vue from 'vue'

const state = {
    status: '',
    estimate_forks: [],
};

const getters = {
    getEstimateForks: state => state.estimate_forks,
    getEstimateForksById: state => state.estimate_forks.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getEstimateForksStatus: state => state.status,
};

const actions = {
    estimateForksRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('estimateForksRequest');
            axios.get('/api/estimate_forks?fields=id,name&limit=9999')
                .then((resp) => {
                    commit('estimateForksSuccess', resp.data.rows);
                    resolve();
                })
                .catch((err) => {
                    commit('estimateForksError');
                    reject();
                })
        });
    },
};

const mutations = {
    estimateForksRequest: (state) => {
        state.status = 'loading';
    },
    estimateForksSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'estimate_forks', resp);
    },
    estimateForksError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}