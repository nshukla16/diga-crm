import Vue from 'vue'

const state = {
    status: '',
    event_groups: [],
};

const getters = {
    getEventGroups: state => state.event_groups,
    getEventGroupsById: state => state.event_groups.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getEventGroupsStatus: state => state.status,
};

const actions = {
    eventGroupsRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('eventGroupsRequest');
            axios.get('/api/event_groups?fields=id,name,color')
                .then((resp) => {
                    commit('eventGroupsSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('eventGroupsError');
                    reject();
                })
        });
    },
};

const mutations = {
    eventGroupsRequest: (state) => {
        state.status = 'loading';
    },
    eventGroupsSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'event_groups', resp);
    },
    eventGroupsError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}