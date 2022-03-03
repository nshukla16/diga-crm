import Vue from 'vue'

const state = {
    status: '',
    users: [],
};

const getters = {
    getUsers: state => state.users,
    getUsersById: state => state.users.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getNotRemovedUsers: state => state.users.filter(el => el.deleted_at == null),
    getUsersStatus: state => state.status,
};

const actions = {
    usersRequest: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('usersRequest');
            axios.get('/api/users/all?fields=id,active,name,salary_type,salary,vacation_days_left,photo,zadarma_internal_phonecode,zadarma_default_responsible,deleted_at,tg_username,cell_phone,formatted_cell_phone,tg_id,dashboard_id,widget_order,day_start_time,lunch_time,day_finish_time,working_days,group_id')
                .then((resp) => {
                    commit('usersSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('usersError');
                    reject();
                })
        });
    },
};

const mutations = {
    usersRequest: (state) => {
        state.status = 'loading';
    },
    usersSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'users', resp);
    },
    usersError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}