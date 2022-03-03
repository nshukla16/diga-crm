import Vue from 'vue'

const state = {
    opened: false,
    number: '',
};

const getters = {
    getNumber: (state) => { return state.number },
    isOpenedNumberWindow: (state) => { return state.opened },
};

const actions = {
    initWindow: ({commit, dispatch, state}, number) => {
        commit("number", number);
        if (!state.opened) commit("windowStatus", true);
    },
    closeWindow: ({commit}) => {
        commit("windowStatus", false);
        commit("number", '');
    },
};

const mutations = {
    number(state, number) {
        state.number = number;
    },
    windowStatus(state, status) {
        state.opened = status;
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}