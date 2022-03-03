import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

import user from './modules/user';
import auth from './modules/auth';
import groups from './modules/groups';
import global_settings from './modules/global_settings';
import sites from './modules/sites';
import call from './modules/call';
import users from './../../../../packages/Rkesa/Hr/resources/assets/js/vuex/modules/users';
import client_referrers from './../../../../packages/Rkesa/Client/resources/assets/js/vuex/modules/client_referrers';
import service_states from './../../../../packages/Rkesa/Service/resources/assets/js/vuex/modules/service_states';
import service_scopes from './../../../../packages/Rkesa/Service/resources/assets/js/vuex/modules/service_scopes';
import service_priorities from './../../../../packages/Rkesa/Service/resources/assets/js/vuex/modules/service_priorities';
import service_types from './../../../../packages/Rkesa/Service/resources/assets/js/vuex/modules/service_types';
import event_types from './../../../../packages/Rkesa/Calendar/resources/assets/js/vuex/modules/event_types';
import event_groups from './../../../../packages/Rkesa/CalendarExtended/resources/assets/js/vuex/modules/event_groups';
import estimate_forks from './../../../../packages/Rkesa/EstimateFork/resources/assets/js/vuex/modules/estimate_forks';
import estimate_units from './../../../../packages/Rkesa/Estimate/resources/assets/js/vuex/modules/estimate_units';
import legal_entities from './../../../../packages/Rkesa/Project/resources/assets/js/vuex/modules/legal_entities';
import project_types from './../../../../packages/Rkesa/Project/resources/assets/js/vuex/modules/project_types';
import project_statuses from './../../../../packages/Rkesa/Project/resources/assets/js/vuex/modules/project_statuses';
import kpi_types from './../../../../packages/Rkesa/Hr/resources/assets/js/vuex/modules/kpi_types';
import loader from './modules/loader';
import chat_users from './modules/chat_users';
import stage from './../../../../packages/Rkesa/Dashboard/resources/assets/js/vuex/modules/stage';

export default new Vuex.Store({
    modules: {
        user,
        auth,
        groups,
        global_settings,
        sites,
        call,
        client_referrers,
        service_states,
        service_scopes,
        service_priorities,
        service_types,
        event_types,
        event_groups,
        estimate_forks,
        estimate_units,
        legal_entities,
        project_types,
        project_statuses,
        users,
        loader,
        kpi_types,
        chat_users,
        stage
    },
});