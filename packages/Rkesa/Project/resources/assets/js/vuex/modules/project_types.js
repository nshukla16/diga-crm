import Vue from 'vue'

const state = {
    status: '',
    project_types: [],
};

const getters = {
    getProjectTypes: state => state.project_types,
    getProjectTypesById: state => state.project_types.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getProjectTypesStatus: state => state.status,
};

const actions = {
    projectTypesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('projectTypesRequest');
            axios.get('/api/project_types')
                .then((resp) => {
                    commit('projectTypesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('projectTypesError');
                    reject();
                })
        });
    },
};

const mutations = {
    projectTypesRequest: (state) => {
        state.status = 'loading';
    },
    projectTypesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'project_types', resp);
    },
    projectTypesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}