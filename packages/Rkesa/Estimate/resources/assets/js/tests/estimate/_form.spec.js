import { shallowMount, createLocalVue } from '@vue/test-utils'
import _form from '../../components/estimate/_form.vue';
import Vuex from 'vuex';

const localVue = createLocalVue();

localVue.use(Vuex);

describe('Компонент Counter', () => {
    let getters;
    let store;

    beforeEach(() => {
        getters = {
            getGlobalSettings: () => { site_name: 'Test' },
            inputValue: () => 'input',
        };

        store = new Vuex.Store({
            getters,
        })
    });

    let wrapper = shallowMount(_form, {store, localVue});

    expect(wrapper.contains('button')).toBe(true);
});