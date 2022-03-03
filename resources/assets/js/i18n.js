import Vue from 'vue';
import VueI18n from 'vue-i18n';
import locales from './vue-i18n-locales.generated.js';

Vue.use(VueI18n);

export default new VueI18n({
    locale: 'ru',
    messages: locales,
});