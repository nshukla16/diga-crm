import Cookies from 'js-cookie';
import Errors from './../../errors';

const state = {
    access_token: Cookies.get('access_token') || '',
    status: '',
    hasLoadedOnce: false,
    errors: new Errors(),
};

const getters = {
    isAuthenticated: state => !!state.access_token,
    getAccessToken: state => state.access_token,
    authStatus: state => state.status,
    authErrors: state => state.errors,
};

const actions = {
    tokenRequest: ({ commit, dispatch }, payload) => {
        let remember = payload.remember ? payload.remember : false;
        let data = {
            'code': payload.code,
        };
        return new Promise((resolve, reject) => {
            commit('authRequest');
            axios.post('/token', data)
                .then((resp) => {
                    let access_token = 'Bearer ' + resp.data.access_token;
                    let days = parseFloat(resp.data.expires_in) / 3600;
                    Cookies.set('access_token', access_token, { expires: days });
                    commit('authSuccess', access_token);
                    resolve(access_token);
                })
                .catch((err) => {
                    console.log(err);
                    commit('authError', err.response.data);
                    Cookies.remove('access_token');
                    reject(err);
                })
        })
    },

    authRequest: ({ commit, dispatch }, payload) => {
        let remember = payload.remember ? payload.remember : false;
        let data = {
            'username': payload.email,
            'password': payload.password,
        };

        return new Promise((resolve, reject) => {
            commit('authRequest');
            axios.post('/login', data)
                .then((resp) => {
                    let access_token = 'Bearer ' + resp.data.access_token;
                    Cookies.set('access_token', access_token, { expires: remember ? 365 : 7 });

                    commit('authSuccess', access_token);
                    resolve(access_token);
                })
                .catch((err) => {
                    console.log(err);
                    commit('authError', err.response.data);
                    Cookies.remove('access_token');
                    reject(err);
                })
        })
    },
    resetRequest: ({ commit, dispatch }, payload) => {
        let data = {
            'token': payload.token,
            'email': payload.email,
            'password': payload.password,
            'password_confirmation': payload.password_confirmation,
        };

        return new Promise((resolve, reject) => {
            axios.post('/password/reset', data)
                .then((resp) => {
                    resolve(resp);
                })
                .catch((err) => {
                    reject(err);
                })
        })
    },
    authLogout: ({ commit, dispatch }) => {
        Cookies.remove('access_token');
        return new Promise((resolve, reject) => {
            axios.post('/logout')
                .then((resp) => {
                    commit('authLogout');
                    commit('reloadUser'); // because we need to reload all data when logout
                    resolve();
                })
                .catch((err) => {
                    commit('authError', err.response.data);
                    reject(err);
                });
        })
    },
    authExpired: ({ commit, dispatch }) => {
        // localStorage.setItem('returnUrl', location.pathname + location.search + location.hash);
        Cookies.remove('access_token');
        commit('authLogout');
        window.location = '/';
    },
};

const mutations = {
    authRequest: (state) => {
        state.status = 'loading';
    },
    authSuccess: (state, access_token) => {
        state.status = 'success';
        state.access_token = access_token;
        state.hasLoadedOnce = true;
    },
    authError: (state, err) => {
        let errors = err.errors ? err.errors : {};
        if (err.error == "invalid_credentials") {
            errors.invalid_credentials = ['The user credentials were incorrect.'];
        }

        state.status = 'error';
        state.hasLoadedOnce = true;
        state.errors.record(errors);
    },
    authLogout: (state) => {
        state.access_token = '';
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
}