import Vue from 'vue'

const state = {
    status: '',
    service_states: [],
};

const getters = {
    getServiceStates: state => state.service_states,
    getServiceStatesById: state => state.service_states.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getNotRemovedServiceStates: state => state.service_states.filter(el => el.deleted_at == null),
    getServiceStatesStatus: state => state.status,
};

const actions = {
    serviceStatesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('serviceStatesRequest');
            axios.get('/api/service_states?fields=id,name,order,horizontal,color,icon,type,can_click,with_reason,destination_state_id,deleted_at')
                .then((resp) => {
                    commit('serviceStatesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('serviceStatesError');
                    reject();
                })
        });
    },
};

const mutations = {
    serviceStatesRequest: (state) => {
        state.status = 'loading';
    },
    serviceStatesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'service_states', resp);
    },
    serviceStatesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}