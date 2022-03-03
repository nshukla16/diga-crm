import Vue from 'vue'

const state = {
    status: '',
    profile: {},
};

const getters = {
    getUser: state => state.profile,
    getUserStatus: state => state.status,
};

const actions = {
    userRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('userRequest');
            axios.get('/api/me')
                .then((resp) => {
                    commit('userSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('userError');
                    reject();
                })
        });
    },
};

const mutations = {
    userRequest: (state) => {
        state.status = 'loading';
    },
    userSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'profile', resp);
    },
    userError: (state) => {
        state.status = 'error';
    },
    reloadUser: () => {
        state.status = '';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}