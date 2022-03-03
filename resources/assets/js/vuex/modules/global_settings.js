import Vue from 'vue'

const state = {
    status: '',
    global_settings: null,
};

const getters = {
    getGlobalSettings: state => state.global_settings,
    getGlobalSettingsStatus: state => state.status,
    getZadarmaEnabled: (state) => { return state.global_settings ? state.global_settings.zadarma_enabled : false },
};

const actions = {
    globalSettingsRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('globalSettingsRequest');
            axios.get('/api/global_settings')
                .then((resp) => {
                    commit('globalSettingsSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('globalSettingsError');
                    reject();
                })
        });
    },
};

const mutations = {
    globalSettingsRequest: (state) => {
        state.status = 'loading';
    },
    globalSettingsSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'global_settings', resp);
    },
    globalSettingsError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}