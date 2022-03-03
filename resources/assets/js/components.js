import VuePdf from 'vue-pdf';
Vue.component('pdf', VuePdf);

import BootstrapToggle from 'vue-bootstrap-toggle';
Vue.component('bootstrap-toggle', BootstrapToggle);

import VueCoreImageUpload from 'vue-core-image-upload';
Vue.component('vue-core-image-upload', VueCoreImageUpload);

import DatePicker from 'vue2-datepicker';
Vue.use(DatePicker);

import Toastr from 'vue-toastr';
Vue.use(Toastr);

import Multiselect from 'vue-multiselect'
Vue.component('multiselect', Multiselect);

Vue.prototype.$i18nForDatatable = (function() {
    return function(srcTxt) {
        switch (srcTxt) {
            case ',':
                return this.$t('datatables.elements');
            case 'items / page':
                return this.$t('datatables.items_page');
            case 'Apply':
                return this.$t('template.apply');
            default:
                return this.$t('datatables.' + srcTxt.replace(new RegExp(' ', 'g'), '_'));
        }
    };
}());

import Datatable from 'vue2-datatable-component';
Vue.use(Datatable);

import vSelect from 'vue-select';
Vue.component('v-select', vSelect);

import { Sketch } from "vue-color"
Vue.component('sketch-picker', Sketch);

import Popper from 'vue-popperjs';
Vue.component('popper', Popper);

import { mixin as clickaway } from 'vue-clickaway';
Vue.mixin(clickaway);

import VueClosable from 'vue-closable';
Vue.use(VueClosable);

import FileUploader from './components/file_uploader';
Vue.component('file-uploader', FileUploader);

import VueCookie from 'vue-cookie';
Vue.use(VueCookie);

import Vuebar from 'vuebar';
Vue.use(Vuebar);

import PortalVue from 'portal-vue';
Vue.use(PortalVue);

import VueBrowserUpdate from 'vue-browserupdate';
Vue.use(VueBrowserUpdate, {
    options: {
        required: { e: -4, f: -3, o: -3, s: -1, c: -3 },
        insecure: true,
        unsupported: true,
        api: 2018.10,
    },
});

import VueNumeric from 'vue-numeric';
Vue.use(VueNumeric);