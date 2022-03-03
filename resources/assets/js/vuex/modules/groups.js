import Vue from 'vue'

const state = {
    status: '',
    groups: [],
};

const getters = {
    getGroups: state => state.groups,
    getGroupsById: state => state.groups.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getGroupsStatus: state => state.status,
};

const actions = {
    groupsRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('groupsRequest');
            axios.get('/api/groups')
                .then((resp) => {
                    commit('groupsSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('groupsError');
                    reject();
                })
        });
    },
};

const mutations = {
    groupsRequest: (state) => {
        state.status = 'loading';
    },
    groupsSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'groups', resp);
    },
    groupsError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}