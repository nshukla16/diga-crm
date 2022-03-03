import Vue from 'vue'

const state = {
    status: '',
    chat_users: [],
};

const getters = {
    getChatUsers: state => state.chat_users,
    getChatUsersById: state => state.chat_users.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getChatUsersStatus: state => state.status,
};

const actions = {
    chatUsersRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('chatUsersRequest');
            axios.get('/api/chats/users')
                .then((resp) => {
                    commit('chatUsersSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('chatUsersError');
                    reject();
                })
        });
    },
};

const mutations = {
    chatUsersRequest: (state) => {
        state.status = 'loading';
    },
    chatUsersSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'chat_users', resp);
    },
    chatUsersError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}