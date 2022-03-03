import Vue from 'vue'

const state = {
    status: '',
    kpi_types: [],
};

const getters = {
    getKpiTypes: state => state.kpi_types,
    getKpiTypesById: state => state.kpi_types.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getKpiTypesStatus: state => state.status,
};

const actions = {
    kpiTypesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('kpiTypesRequest');
            axios.get('/api/kpi/types?fields=id,name')
                .then((resp) => {
                    commit('kpiTypesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('kpiTypesError');
                    reject();
                })
        });
    },
};

const mutations = {
    kpiTypesRequest: (state) => {
        state.status = 'loading';
    },
    kpiTypesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'kpi_types', resp);
    },
    kpiTypesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}