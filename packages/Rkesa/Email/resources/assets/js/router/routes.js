// Email
import email_index from '../components/mail/index.vue';
import email_settings from '../components/mail/domains.vue';

export default [
    {
        path: '/mail',
        name: 'email_index',
        component: email_index,
    },
    {
        path: '/settings/email',
        name: 'email_settings',
        component: email_settings,
    },
]