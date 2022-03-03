import Vue from 'vue'

const state = {
    status: '',
    legal_entities: [],
};

const getters = {
    getLegalEntities: state => state.legal_entities,
    getLegalEntitiesById: state => state.legal_entities.reduce(function(map, obj) {
        map[obj.id] = obj;
        return map;
    }, {}),
    getLegalEntitiesStatus: state => state.status,
};

const actions = {
    legalEntitiesRequest: ({ commit, dispatch }) => {
        return new Promise((resolve, reject) => {
            commit('legalEntitiesRequest');
            axios.get('/api/legal_entities/all')
                .then((resp) => {
                    commit('legalEntitiesSuccess', resp.data.rows);
                    resolve();
                })
                .catch((err) => {
                    commit('legalEntitiesError');
                    reject();
                })
        });
    },
};

const mutations = {
    legalEntitiesRequest: (state) => {
        state.status = 'loading';
    },
    legalEntitiesSuccess: (state, resp) => {
        state.status = 'success';
        Vue.set(state, 'legal_entities', resp);
    },
    legalEntitiesError: (state) => {
        state.status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}