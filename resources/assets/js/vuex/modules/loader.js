const state = {
    shared_info_status: '',
    shared_info: {
        logo: '/img/logo.png',
        locale: 'en',
    },
};

const getters = {
    getAllSettingsStatus: (state, getters) => {
        let settings = [
            getters.getUserStatus,
            getters.getGroups,
            getters.getGlobalSettingsStatus,
            getters.getClientReferrersStatus,
            getters.getServiceStatesStatus,
            getters.getServiceScopesStatus,
            getters.getServicePrioritiesStatus,
            getters.getServiceTypesStatus,
            getters.getEventTypesStatus,
            getters.getEstimateForksStatus,
            getters.getEstimateUnitsStatus,
            getters.getSitesStatus,
            getters.getEventGroupsStatus,
            getters.getUsersStatus,
            getters.getLegalEntitiesStatus,
            getters.getProjectTypesStatus,
            getters.getProjectStatusesStatus,
            getters.getKpiTypesStatus,
            getters.getChatUsers,
        ];
        if (settings.includes('error')){
            return 'error';
        }
        if (settings.includes('loading')){
            return 'loading';
        }
        if (settings.includes('')){
            return 'not-started';
        }
        return 'success';
    },
    getSharedInfoStatus: state => state.shared_info_status,
    getLogo: state => state.shared_info.logo,
    getLocale: state => state.shared_info.locale,
};

const actions = {
    loadInitialData: async ({commit, dispatch}) => {
        await Promise.all([
            dispatch('userRequest'),
            dispatch('groupsRequest'),
            dispatch('globalSettingsRequest'),
            dispatch('clientReferrersRequest'),
            dispatch('serviceStatesRequest'),
            dispatch('serviceScopesRequest'),
            dispatch('servicePrioritiesRequest'),
            dispatch('serviceTypesRequest'),
            dispatch('eventTypesRequest'),
            dispatch('eventGroupsRequest'),
            dispatch('estimateForksRequest'),
            dispatch('estimateUnitsRequest'),
            dispatch('sitesRequest'),
            dispatch('usersRequest'),
            dispatch('legalEntitiesRequest'),
            dispatch('projectTypesRequest'),
            dispatch('projectStatusesRequest'),
            dispatch('kpiTypesRequest'),
            dispatch('chatUsersRequest'),
        ]);
    },
    loadSharedInfo: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit('sharedInfoRequest');
            axios.get('/shared_info')
                .then((resp) => {
                    commit('sharedInfoSuccess', resp.data);
                    resolve();
                })
                .catch((err) => {
                    commit('sharedInfoError');
                    reject();
                });
        });
    },
};

const mutations = {
    sharedInfoRequest: (state) => {
        state.shared_info_status = 'loading';
    },
    sharedInfoSuccess: (state, resp) => {
        state.shared_info_status = 'success';
        state.shared_info.logo = resp.url;
        state.shared_info.locale = resp.language;
    },
    sharedInfoError: (state) => {
        state.shared_info_status = 'error';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}