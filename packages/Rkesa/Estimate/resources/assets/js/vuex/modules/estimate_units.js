import Vue from 'vue'

const state = {
    status: '',
    estimate_units: [],
};

const getters = {
    getEstimateUnits: state => state.estimate_units,
    getEstimateUnitsById: state => state.estimate_units.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getEstimateUnitsStatus: state => state.status,
};

const actions = {
    estimateUnitsRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('estimateUnitsRequest');
            axios.get('/api/estimate_units')
                .then((resp) => {
                    commit('estimateUnitsSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('estimateUnitsError');
                    reject();
                })
        });
    },
};

const mutations = {
    estimateUnitsRequest: (state) => {
        state.status = 'loading';
    },
    estimateUnitsSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'estimate_units', resp);
    },
    estimateUnitsError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}