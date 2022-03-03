import Vue from 'vue'

const state = {
    status: '',
    sites: [],
};

const getters = {
    getSites: state => state.sites,
    getSitesById: state => state.sites.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getSitesStatus: state => state.status,
};

const actions = {
    sitesRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('sitesRequest');
            axios.get('/api/sites?fields=id,domain,token')
                .then((resp) => {
                    commit('sitesSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('sitesError');
                    reject();
                })
        });
    },
};

const mutations = {
    sitesRequest: (state) => {
        state.status = 'loading';
    },
    sitesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'sites', resp);
    },
    sitesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}