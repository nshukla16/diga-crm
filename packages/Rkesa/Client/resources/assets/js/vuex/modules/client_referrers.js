import Vue from 'vue'

const state = {
    status: '',
    client_referrers: [],
};

const getters = {
    getClientReferrers: state => state.client_referrers,
    getClientReferrersById: state => state.client_referrers.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getClientReferrersStatus: state => state.status,
};

const actions = {
    clientReferrersRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('clientReferrersRequest');
            axios.get('/api/client_referrers?fields=id,title')
                .then((resp) => {
                    commit('clientReferrersSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('clientReferrersError');
                    reject();
                })
        });
    },
};

const mutations = {
    clientReferrersRequest: (state) => {
        state.status = 'loading';
    },
    clientReferrersSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'client_referrers', resp);
    },
    clientReferrersError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}