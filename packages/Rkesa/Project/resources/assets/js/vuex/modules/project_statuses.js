import Vue from 'vue'

const state = {
    status: '',
    project_statuses: [],
};

const getters = {
    getProjectStatuses: state => state.project_statuses,
    getProjectStatusesById: state => state.project_statuses.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getProjectStatusesStatus: state => state.status,
};

const actions = {
    projectStatusesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('projectStatusesRequest');
            axios.get('/api/project_statuses')
                .then((resp) => {
                    commit('projectStatusesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('projectStatusesError');
                    reject();
                })
        });
    },
};

const mutations = {
    projectStatusesRequest: (state) => {
        state.status = 'loading';
    },
    projectStatusesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'project_statuses', resp);
    },
    projectStatusesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}