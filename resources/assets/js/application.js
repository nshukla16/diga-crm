/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue');

import Vue from 'vue'
import * as Sentry from '@sentry/browser';
import * as Integrations from '@sentry/integrations';

if (process.env.NODE_ENV === 'production') {
    Vue.config.devtools = false;
    Vue.config.debug = false;
    Vue.config.silent = true;

    Sentry.init({
        dsn: 'https://2ee97950bc3c4e3b9b621d6cb6e38da0@sentry.diga.pt/3',
        integrations: [new Integrations.Vue({ Vue, attachProps: true })],
        release: VERSION,
    });
} else {
    Vue.config.performance = true
}

require('./bootstrap');
require('./components');

/* GOOGLE MAPS */

const VueGoogleMaps = require('vue2-google-maps');
Vue.use(VueGoogleMaps, {
    load: {
        key: 'AIzaSyAWAoJ2F0U1mir25P0fyUzf2P2mkQYnT6s',
        v: '3',
        // 'directions' breaks the app!
        libraries: 'places',
        // language: document.getElementById("erp-application").getAttribute("data-site-language") // TODO
    },
    installComponents: true,
});

Object.defineProperty(Vue.prototype, "$bus", {
    get: function() {
        return this.$root.bus;
    },
});

import i18n from './i18n.js';
import tourGenerator from './product-tour.js'
import VeeValidate from 'vee-validate';
import ru from 'vee-validate/dist/locale/ru.js';
import en from 'vee-validate/dist/locale/en.js';
import pt from 'vee-validate/dist/locale/pt_PT.js';
import es from 'vee-validate/dist/locale/es.js';
import moment from 'moment';

import 'moment/locale/es'
import 'moment/locale/pt'
import 'moment/locale/ru'

Vue.use(VeeValidate, {
    i18n,
    dictionary: {
        ru,
        pt,
        en,
        es
    },
});

import router from './router/routes';
import store from './vuex/store';
import VueTimeago from 'vue-timeago';
import LoadingComponent from './components/loading.vue';
import { mapGetters } from 'vuex';

const app = new Vue({
    i18n,
    store,
    router,
    data() {
        return {
            // locale: '',
            page_content_class: 'page-content',
            bus: new Vue({}),
            // notifications
            show_notifications: false,
            notifications: [],
            not_count: 0,
            //
            js_updated_showing: false,
            global_loading: false,
            locale: this.$store.getters.getLocale,
            // permissions: [],
            currencies: require('./../../../storage/app/currencies.json'),
            max_file_size: 31457280, // 30M in bytes,
            // for DatePicker (vue2-datepicker)
            valueType: {
                value2date: (value) => {
                    return value ? moment(value, 'YYYY-MM-DD').toDate() : null;
                },
                date2value: (date) => {
                    return date ? moment(date).format('YYYY-MM-DD') : null;
                },
            },
            // realtime_state: false,
            online: [],
            toogleSidebar: false,
        }
    },
    created() {

    },
    mounted() {
        // Product tour
        if (this.user.show_product_tour) {
            this.start_user_tour();
        }
        this.$bus.$on("notif_read", this.notif_read);
    },
    components: {
        'loading': LoadingComponent, // located in spa.blade.php
    },
    methods: {
        check_real_time() {
            let $this = this;
            this.realtime_state = false;
            this.$http.post('/api/check');

            setTimeout(() => {
                if (!$this.realtime_state) {
                    this.$toastr.Add({
                        msg: this.$t("template.Realtime_notifications_are_not_working"),
                        title: this.$t("template.Error"),
                        type: "error",
                        timeout: 0,
                    });
                }
            }, 10000);
        },
        start_user_tour() {
            let tour = tourGenerator.getTourInstance(this, $(document));
            tour.init();
            tour.start(true);
        },
        download_file(url) {
            return new Promise((resolve, reject) => {
                this.$http.get(url, { responseType: 'arraybuffer' }).then((response) => {
                    let blob = new Blob([response.data], { type: response.headers['content-type'] });
                    resolve(URL.createObjectURL(blob));
                });
            });
        },
        show_js_updated_notification() {
            this.js_updated_showing = true;
        },
        change_language() {
            this.$i18n.locale = this.locale; // because locale watcher executes after components mounted function, so QR generates with wrong language
            moment.locale(this.locale);

            Vue.use(VueTimeago, {
                name: 'Timeago', // Component name, `Timeago` by default
                locale: this.locale, // Default locale
                // We use `date-fns` under the hood
                // So you can use all locales from it
                locales: {
                    'ru': require('date-fns/locale/ru'),
                    'en': require('date-fns/locale/en'),
                    'pt': require('date-fns/locale/pt'),
                    'es': require('date-fns/locale/es'),
                },
            });
        },
        LoadNotificationData() {
            this.setupJSUpdatedStream();
            this.setupUserNotificationsStream();
            this.loadUserNotifications();
            if (this.user.new_client_notifications) {
                this.setupNotificationsStream();
                this.loadNotifications();
            }
            if (this.user.new_fb_messages_notifications) {
                this.setupFBNotificationsStream();
                this.loadFBNotifications();
            }
            this.setupCalendarSettingsChangedStream();
            this.setupClientSettingsChangedStream();
            this.setupGlobalSettingsChangedStream();
            this.setupServiceSettingsChangedStream();
            this.setupEstimateSettingsChangedStream();
            this.setupProjectSettingsChangedStream();
            this.setupSitesSettingsChangedStream();
            this.setupProfileChangedStream();
            this.setupGroupsChangedStream();
            this.setupLegalEntitiesChangedStream();
            this.setupUsersChangedStream();
            this.setupCallNewContactNotification();
            this.setupProjectChangedEvent();
            // this.setupChatStream(); // moved to rightpanel component
            this.setupOnlineStream();
            // this.setupCheckRealTime();
            //
            // let $this = this;
            // setTimeout(() => {
            //     $this.check_real_time();
            // }, 5000);
        },
        leave_all_channels() {
            Echo.leave(this.pusher_user_channel_name);
            Echo.leave(this.pusher_common_channel_name);
            Echo.leave(this.pusher_online_channel_name);
        },
        // setupCheckRealTime() {
        //     let $this = this;
        //     Echo.private(this.pusher_user_channel_name)
        //         .listen('CheckRealTime', (e) => {
        //             if ($this.realtime_state){ // realtime_state should be false, because we reset it in $this.check_real_time()
        //                 this.$toastr.Add({
        //                     msg: this.$t("template.Realtime_notifications_are_not_working"),
        //                     title: this.$t("template.Error"),
        //                     type: "error",
        //                     timeout: 0,
        //                 });
        //             } else {
        //                 $this.realtime_state = true;
        //             }
        //         });
        // },
        setupOnlineStream() {
            let $this = this;
            Echo.join(this.pusher_online_channel_name)
                .here((users) => {
                    $this.online = users.map(u => u.id);
                })
                .listen('.pusher:member_added', (user) => {
                    $this.online.push(user.id);
                })
                .listen('.pusher:member_removed', (user) => {
                    $this.online.splice($this.online.indexOf(user.id), 1);
                })
                .listen('.pusher:subscription_error', (user) => {
                    $this.$toastr.Add({
                        msg: $this.$t("template.Realtime_notifications_are_not_working"),
                        title: $this.$t("template.Error"),
                        type: "error",
                        timeout: 0,
                    });
                })
                .listen('.error', (user) => { // NOT TESTED
                    $this.$toastr.Add({
                        msg: $this.$t("template.Realtime_notifications_are_not_working"),
                        title: $this.$t("template.Error"),
                        type: "error",
                        timeout: 0,
                    });
                });
        },
        // setupChatStream() { // moved to rightpanel component
        //     //
        // },
        setupCalendarSettingsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('.Rkesa\\Calendar\\Events\\EventTypesChanged', (e) => {
                    $this.$store.dispatch('eventTypesRequest');
                });
        },
        setupProjectChangedEvent() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('.Rkesa\\Project\\Events\\ProjectChangedEvent', (e) => {
                    $this.bus.$emit('project_changed_event', e.project_id, e.user_id);
                });
        },
        setupJSUpdatedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('JSUpdated', (e) => {
                    $this.show_js_updated_notification();
                });
        },
        setupUsersChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('UsersChanged', (e) => {
                    $this.$store.dispatch('usersRequest');
                    $this.$store.dispatch('groupsRequest');
                    $this.$store.dispatch('chatUsersRequest');
                });
        },
        setupGroupsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('GroupsChanged', (e) => {
                    $this.$store.dispatch('usersRequest');
                    $this.$store.dispatch('groupsRequest');
                });
        },
        setupLegalEntitiesChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('LegalEntitiesChanged', (e) => {
                    $this.$store.dispatch('legalEntitiesRequest');
                });
        },
        setupClientSettingsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('ClientsSettingsChanged', (e) => {
                    $this.$store.dispatch('clientReferrersRequest');
                    $this.$store.dispatch('globalSettingsRequest');
                });
        },
        setupServiceSettingsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('ServiceSettingsChanged', (e) => {
                    $this.$store.dispatch('serviceScopesRequest');
                    $this.$store.dispatch('serviceStatesRequest');
                    $this.$store.dispatch('serviceTypesRequest');
                    $this.$store.dispatch('globalSettingsRequest');
                });
        },
        setupEstimateSettingsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('EstimateSettingsChanged', (e) => {
                    $this.$store.dispatch('estimateUnitsRequest');
                    $this.$store.dispatch('globalSettingsRequest');
                });
        },
        setupProjectSettingsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('ProjectSettingsChanged', (e) => {
                    $this.$store.dispatch('projectStatusesRequest');
                    $this.$store.dispatch('projectTypesRequest');
                });
        },
        setupSitesSettingsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('SitesSettingsChanged', (e) => {
                    $this.$store.dispatch('sitesRequest');
                    $this.$store.dispatch('globalSettingsRequest');
                });
        },
        setupGlobalSettingsChangedStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('GlobalSettingsChanged', (e) => {
                    $this.$store.dispatch('globalSettingsRequest');
                });
        },
        setupProfileChangedStream() {
            let $this = this;
            Echo.private(this.pusher_user_channel_name)
                .listen('ProfileChanged', (e) => {
                    $this.$store.dispatch('userRequest');
                });
        },
        setupUserNotificationsStream() {
            let $this = this;
            Echo.private(this.pusher_user_channel_name)
                .notification((notification) => {
                    notification.read = false;
                    $this.notifications.unshift(notification);
                    $this.bus.$emit('notif_page_add_not');
                    $this.not_count++;
                    if ($this.not_count > 5) {
                        $this.notifications.pop();
                    }
                });
        },
        setupNotificationsStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('IncomingClient', (e) => {
                    $this.new_client_toastr(e.client);
                });
        },
        setupFBNotificationsStream() {
            let $this = this;
            Echo.private(this.pusher_common_channel_name)
                .listen('NewFBMessage', (e) => {
                    if (location.pathname == '/clients/' + e.client.id || location.pathname == '/contacts/' + e.client.id) {
                        let comment = {
                            type_id: 19,
                            user: this.$root.user.id,
                            client_contact: e.client,
                            created_at: moment(new Date()).format('YYYY-MM-DD HH:mm:ss'),
                            message: e.client.message,
                        };
                        this.$bus.$emit('system_message', comment);
                    } else {
                        $this.new_fb_message_toastr(e.client);
                    }
                });
        },
        setupCallNewContactNotification() {
            let checkZadarmaNotificationCookie = () => {
                let cn = 'zadarma_notification';
                let c = Cookies.get(cn);
                let now = moment().unix();
                if (typeof c != 'undefined') {
                    let diff = now - c;

                    if (diff > 5) {
                        Cookies.set(cn, now);
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    Cookies.set(cn, now);
                    return true;
                }
            }

            Echo.private(this.pusher_user_channel_name)
                .listen('CallContact', (e) => {
                    let the_site_url = location.protocol + '//' + location.host;
                    let push_title;
                    let push_params = {
                        icon: this.global_settings.site_logo,
                        requireInteraction: true,
                    };
                    if (e.out.case == 'new_contact') {
                        let referrer_id = e.out.referrer_id;
                        let caller_id = e.out.caller_id;
                        push_title = this.$t('zadarma.Call_incomming') + ': ' + caller_id;
                        push_params.body = this.$t('zadarma.Press_to_create_contact') + '\n' + the_site_url + '/clients/create?referrer_id=' + referrer_id + '&caller_id=' + caller_id;
                        push_params.onClick = function() {
                            window.open(the_site_url + '/clients/create?referrer_id=' + referrer_id + '&caller_id=' + caller_id); // just added the window.open to a OnClick event.
                            this.close();
                        };
                    } else {
                        let contact_id = e.out.contact_id;
                        let contact_name = e.out.contact_name;
                        let link = the_site_url + '/contacts/' + contact_id
                        push_title = this.$t('zadarma.Call_incomming') + ': ' + contact_name;
                        push_params.body = this.$t('template.Open_card') + '\n' + link;
                        push_params.onClick = function() {
                            window.open(link); // just added the window.open to a OnClick event.
                            this.close();
                        };
                    }

                    let notificationTest = checkZadarmaNotificationCookie();
                    if (notificationTest) Push.create(push_title, push_params);
                });
        },
        taskAssignedNotificationText(notification) {
            let tmp = this.$t('template.NotificationPartTask') + this.event_types[notification.event_type_id].title + this.$t('template.NotificationPart1') + '<a href="/contacts/' + notification.contact_id + '">' + notification.contact_name + '</a>';
            if (notification.company_id) {
                tmp += this.$t('template.NotificationPart2') + '<a href="/companies/' + notification.company_id + '">' + notification.company_name + '</a>';
            }
            return tmp;
        },
        newMissedCallNotificationText(notification) {
            let tmp = this.$t('zadarma.missed_call_from');
            tmp += notification.caller_id;

            return tmp;
        },
        autoTaskAssignedNotificationText(notification) {
            let tmp = "";
            tmp = this.$t('template.NotificationPartTask') + this.event_types[notification.event_type_id].title;

            switch (notification.not_type) {
                case "Technical_documentation_available":
                    tmp += this.$t('template.Notification_part_technical_doc') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    if (notification.event_url) {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="' + new URL(notification.event_url).href + '">' + notification.project_name + '</a>';
                    } else {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    }
                    break;
                case "Manufacturer_order_created":
                    tmp += this.$t('template.Notification_part_manufacturer_order') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    if (notification.event_url) {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="' + new URL(notification.event_url).href + '">' + notification.project_name + '</a>';
                    } else {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    }
                    break;
                case "Manufacturer_bill_filled":
                    tmp += this.$t('template.Notification_part_manufacturer_bill_filled') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    tmp += this.$t('template.Notification_part_project') +
                        '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    break;
                case "Manufacturer_confirmed_filled":
                    tmp += this.$t('template.Notification_part_manufacturer_confirmed_filled') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    tmp += this.$t('template.Notification_part_project') +
                        '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    break;
                case "Invoice_uploaded":
                    tmp = this.$t('template.NotificationPart_invoice_uploaded') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    if (notification.event_url) {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="' + new URL(notification.event_url).href + '">' + notification.project_name + '</a>';
                    } else {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    }
                    if (notification.event_description) {
                        tmp += this.$t('template.Notification_part_document_name') +
                            notification.event_description;
                    }
                    break;
                case "Invoice_confirmed":
                    tmp = this.$t('template.NotificationPart_invoice_confirmed') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    if (notification.event_url) {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="' + new URL(notification.event_url).href + '">' + notification.project_name + '</a>';
                    } else {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    }
                    if (notification.event_description) {
                        tmp += this.$t('template.Notification_part_document_name') +
                            notification.event_description;
                    }
                    break;
                default:
                    tmp += this.$t('template.NotificationPart1') + '<a href="/contacts/' + notification.contact_id + '">' + notification.contact_name + '</a>';
                    if (notification.company_id) {
                        tmp += this.$t('template.NotificationPart2') + '<a href="/companies/' + notification.company_id + '">' + notification.company_name + '</a>';
                    }
                    if (notification.event_url) {
                        tmp += this.$t('template.Notification_part_project') +
                            '<a href="' + new URL(notification.event_url).href + '">' + notification.project_name + '</a>';
                    } else {
                        if (notification.project_id) {
                            tmp += this.$t('template.Notification_part_project') +
                                '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                        }
                    }
            }

            return tmp;
        },
        projectApplicationsNotificationText(notification) {
            let tmp = "";

            switch (notification.not_type) {
                case "Manufacturer_order_created":
                    tmp += this.$t('template.Notification_part_manufacturer_order_2') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    tmp += this.$t('template.Notification_part_project') +
                        '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    break;
                case "Manufacturer_bill_filled":
                    tmp += this.$t('template.Notification_part_manufacturer_bill_filled_2') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    tmp += this.$t('template.Notification_part_project') +
                        '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    break;
                case "Manufacturer_confirmed_filled":
                    tmp += this.$t('template.Notification_part_manufacturer_confirmed_filled_2') +
                        '<a href="/manufacturers/' + notification.manufacturer_id + '">' + notification.manufacturer_name + '</a>';
                    tmp += this.$t('template.Notification_part_project') +
                        '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
                    break;
                default:
                    tmp += this.$t('template.NotificationPart1') + '<a href="/contacts/' + notification.contact_id + '">' + notification.contact_name + '</a>';
                    if (notification.company_id) {
                        tmp += this.$t('template.NotificationPart2') + '<a href="/companies/' + notification.company_id + '">' + notification.company_name + '</a>';
                    }
            }

            return tmp;
        },
        serviceAssignedNotificationText(notification) {
            let tmp = this.$t('template.NotificationPartService') + '<a href="/contacts/' + notification.contact_id + '?service_id=' + notification.service_id + '">' + notification.service_number + '</a>' + this.$t('template.NotificationPart1') + '<a href="/contacts/' + notification.contact_id + '">' + notification.contact_name + '</a>';
            if (notification.company_id) {
                tmp += this.$t('template.NotificationPart2') + '<a href="/companies/' + notification.company_id + '">' + notification.company_name + '</a>';
            }
            return tmp;
        },
        newPaymentStepText(notification) {
            let temp = notification.left_part + ' ' + '<a href="/estimates/' + notification.estimate_id + '/edit' + '">' + notification.estimate_number + '<a/>';
            return temp;
        },
        estimateGrantedText(notification) {
            let temp = '<a href="/estimates/' + notification.service.master_estimate_id + '/edit' + '">' + notification.service.estimate_number + '<a/>' + ' ' + notification.left_part + ' ' + notification.created_at;
            return temp;
        },
        taskChangesInRoadmapText(notification) {
            let temp = notification.left_part + '<a href="/user_plannings/' + notification.roadmap_id + '"> ' + notification.roadmap_name + ' <a/> ' + ' ' + notification.right_part + ' <a href="/estimates/' + notification.estimate_id + '/edit' + '"> ' + notification.estimate_number + '<a/>';
            return temp;
        },
        newConnectionText(notification) {
            let temp = notification.left_part + ' <a href="/general_contractors"> ' + this.$t("template.Open") + ' </a>';
            return temp;
        },
        contractorServiceChangedText(notification) {
            let tmp = this.$t('template.' + notification.from) + ' ' + this.$t('client.has_changed_status_of_estimate') + ' «' +
                '<a href="/contacts/' + notification.contact_id +
                '?service_id=' + notification.service_id + '">' +
                notification.service_number + '</a>' + this.$t('template.NotificationPart1') +
                '<a href="/contacts/' + notification.contact_id + '">' +
                notification.contact_name + '</a>';
            return tmp;
        },
        invoiceReceivedText(notification) {
            let tmp = this.$t('template.received_invoice_from_contractor') + ' <a target="_blank" href="' + notification.invoice_file + '">' + notification.invoice_file_name + '</a> «' +
                '<a href="/contacts/' + notification.contact_id +
                '?service_id=' + notification.service_id + '">' +
                notification.service_number + '</a>' + this.$t('template.NotificationPart1') +
                '<a href="/contacts/' + notification.contact_id + '">' +
                notification.contact_name + '</a>';
            return tmp;
        },
        notificationLeftPart(notification) {
            // return this.$t('template.' + this.i18n_key_from_notification_type(notification.type), {initiator: notification.initiator_name});
            switch (notification.type) {
                case "App\\Notifications\\TaskAssigned":
                    return this.taskAssignedNotificationText(notification);
                case "Rkesa\\Project\\Notifications\\AutoTaskAssigned":
                    return this.autoTaskAssignedNotificationText(notification);
                case "App\\Notifications\\ServiceAssigned":
                    return this.serviceAssignedNotificationText(notification);
                case "App\\Notifications\\ContactAssigned":
                    return notification.left_part;
                case "Rkesa\\Project\\Notifications\\ProjectChanged":
                    return notification.left_part;
                case "Rkesa\\Planning\\Notifications\\EstimateGranted":
                    return this.estimateGrantedText(notification);
                case "Rkesa\\Planning\\Notifications\\PaymentStep":
                    return this.newPaymentStepText(notification);
                case "App\\Notifications\\NewMissedCall":
                    return this.newMissedCallNotificationText(notification);
                case "Rkesa\\Project\\Notifications\\ProjectApplicationsAdded":
                    return this.projectApplicationsNotificationText(notification);
                case "Rkesa\\Planning\\Notifications\\TaskChangesInRoadmap":
                    return this.taskChangesInRoadmapText(notification);
                case "App\\Notifications\\NewConnection":
                    return this.newConnectionText(notification);
                case "App\\Notifications\\ContractorServiceChanged":
                    return this.contractorServiceChangedText(notification);
                case "App\\Notifications\\ContractorInvoiceReceived":
                    return this.invoiceReceivedText(notification);
            }
        },
        notificationRightPart(notification) {
            switch (notification.type) {
                case "App\\Notifications\\TaskAssigned":
                    return this.$t('template.TaskAssigned', { initiator: notification.initiator_name });
                case "App\\Notifications\\AutoTaskAssigned":
                    return this.$t('template.TaskAssigned', { initiator: notification.initiator_name });
                case "App\\Notifications\\ServiceAssigned":
                    return this.$t('template.ServiceAssigned', { initiator: notification.initiator_name });
                case "App\\Notifications\\ContactAssigned":
                case "App\\Notifications\\NewConnection":
                    return notification.right_part;
                case "Rkesa\\Project\\Notifications\\ProjectChanged":
                    return notification.right_part;
                case "Rkesa\\Planning\\Notifications\\PaymentStep":
                    return notification.right_part;
                case "App\\Notifications\\NewMissedCall":
                    return "Zadarma";
                case "App\\Notifications\\ProjectApplicationsAdded":
                    return notification.right_part;
                case "App\\Notifications\\ContractorServiceChanged":
                    return this.$t('template.' + notification.from);
                case "App\\Notifications\\ContractorInvoiceReceived":
                    return this.$t('template.contractor');
            }
        },
        // i18n_key_from_notification_type(type){
        //     return type.substring(type.lastIndexOf("\\"))+'_right_notification';
        // },
        project_name_link(notification) {
            return '<a href="/projects/' + notification.project_id + '">' + notification.project_name + '</a>';
        },
        new_client_toastr(client) {
            this.$toastr.Add({
                title: this.$t("template.New_client_from") + " " + client.client_referrer.title,
                msg: this.fullName(client) + "<br/><a class='button_in_toastr' href='" + this.contact_or_client_show(client.id) + "'>" + this.$t("template.Open") + "</a>",
                clickClose: true,
                timeout: 0,
                progressBarValue: 0,
                type: "success",
            });
        },
        new_fb_message_toastr(client) {
            this.$toastr.Add({
                title: this.$t("template.New_message_from_fb"),
                msg: this.fullName(client) + "<br/><a class='button_in_toastr' href='" + this.contact_or_client_show(client.id) + "'>" + this.$t("template.Open") + "</a>",
                clickClose: true,
                timeout: 0,
                progressBarValue: 0,
                type: "success",
            });
        },
        contact_or_client_create() {
            if (this.global_settings.enable_companies) {
                return '/contacts/create';
            } else {
                return '/clients/create';
            }
        },
        contact_or_client_create_route() {
            return this.global_settings.enable_companies ? 'contact_create' : 'client_create';
        },
        contact_or_client_show(id) {
            if (this.global_settings.enable_companies) {
                return '/contacts/' + id;
            } else {
                return '/clients/' + id;
            }
        },
        contact_or_client_show_route() {
            return this.global_settings.enable_companies ? 'contact_show' : 'client_show';
        },
        contact_or_client_edit(id) {
            if (this.global_settings.enable_companies) {
                return '/contacts/' + id + '/edit';
            } else {
                return '/clients/' + id + '/edit';
            }
        },
        contact_or_client_store() {
            if (this.global_settings.enable_companies) {
                return '/contacts';
            } else {
                return '/clients';
            }
        },
        // permissions
        can_do: function(action, type) {
            if (this.permissions) {
                let role = this.permissions.find(e => e.action === action);
                if (role) {
                    return role[type];
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        },
        can_with_project: function(type, section) {
            let role = this.can_do('projects', type);
            if (type == 'create' || type == 'delete') {
                return role;
            } else {
                let section_array = parseInt(role, 10).toString(2).split('').map(c => parseInt(c, 2) == 1);
                if (section_array.length != 10) {
                    let array_to_add_to_the_beginning = Array(10 - section_array.length).fill(false);
                    section_array = array_to_add_to_the_beginning.concat(section_array);
                }
                return section_array[section];
            }
        },
        can_with_client(type, client) {
            if (client) {
                switch (this.can_do('clients', type)) {
                    case 0:
                        return false;
                    case 1:
                        {
                            let client_ids_e = client.events.filter(event => event.user_id == this.user.id).length > 0;
                            let client_ids_s = client.services.filter(service => service.responsible_user_id == this.user.id).length > 0;
                            return client_ids_e || client_ids_s;
                        }
                    case 2:
                        {
                            let client_ids_ge = client.events.filter(event => this.user.groupmates.includes(event.user_id)).length > 0;
                            let client_ids_gs = client.services.filter(service => this.user.groupmates.includes(service.responsible_user_id)).length > 0;
                            return client_ids_ge || client_ids_gs;
                        }
                    case 3:
                        return true;
                }
            } else {
                return false;
            }
        },
        can_with_service(type, service) {
            switch (this.can_do('services', type)) {
                case 0:
                    return false;
                case 1:
                    return service.responsible_user_id == this.user.id;
                case 2:
                    return this.user.groupmates_ids.includes(service.responsible_user_id);
                case 3:
                    return true;
            }
        },
        can_with_event(type, event) {
            switch (this.can_do('events', type)) {
                case 0:
                    return false;
                case 1:
                    return event.user_id == this.user.id;
                case 2:
                    return this.user.groupmates_ids.includes(event.user_id);
                case 3:
                    return true;
            }
        },
        can_with_estimate(type, estimate) {
            switch (this.can_do('estimates', type)) {
                case 0:
                    return false;
                case 1:
                    return estimate.user_id == this.user.id;
                case 2:
                    return this.user.groupmates_ids.includes(estimate.user_id);
                case 3:
                    return true;
            }
        },
        // can_read_user(user){
        //     switch(this.can_do('users', 'read')){
        //         case 0:
        //             return false;
        //         case 1:
        //             return this.user.groupmates.includes(user.id);
        //         case 2:
        //             return true;
        //     }
        // },
        //
        onClassChange: function(cl) {
            this.page_content_class = cl;
        },
        hide_notifications() {
            this.show_notifications = false;
        },
        notifications_toggle_click() {
            this.show_notifications = !this.show_notifications;
        },
        notif_read(not_id) {
            let notif = this.notifications.find(x => x.id === not_id);
            if (notif) {
                notif.read = !notif.read;
            }
        },
        mark_as_read(notif) {
            let $this = this;
            this.$http.post('/api/notifications/read', { id: notif.id }).then(res => {
                if (res.data.errcode == 1) {
                    $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
                } else {
                    if (notif.read) {
                        $this.not_count++;
                    } else {
                        $this.not_count--;
                    }
                    this.$bus.$emit("notif_read", notif.id);
                }
            }, res => {
                $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
            });
        },
        mark_all_as_read() {
            let $this = this;
            this.notifications.forEach(function(notif) {
                if (!notif.read) {
                    $this.mark_as_read(notif);
                }
            });
        },
        // Helpers
        module_enabled(module_name) {
            if (this.modules) {
                return this.modules[module_name];
            } else {
                return false;
            }
        },
        get_client_name(client) {
            let contact = client.client_contacts.filter(function(contact) {
                return contact.is_main_contact;
            })[0];
            return this.fullName(contact);
        },
        loadUserNotifications() {
            let $this = this;
            this.$http.get('/api/notifications/last').then(res => {
                if (res.data.errcode == 1) {
                    $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
                } else {
                    res.data.notifs.forEach(function(not) {
                        not.read = not.read_at != null;
                        $this.notifications.push(not);
                    });
                    $this.not_count = res.data.not_count;
                }
            }, res => {
                $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
            });
        },
        loadNotifications() {
            let $this = this;
            this.$http.get('/api/clients/get_new_requests').then(res => {
                if (res.data.errcode == 1) {
                    $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
                } else {
                    res.data.clients.forEach(function(client) {
                        $this.new_client_toastr(client);
                    });
                }
            }, res => {
                $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
            });
        },
        loadFBNotifications() {
            let $this = this;
            this.$http.get('/api/clients/get_new_fb_messages').then(res => {
                if (res.data.errcode == 1) {
                    $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
                } else {
                    res.data.clients.forEach(function(client) {
                        $this.new_fb_message_toastr(client);
                    });
                }
            }, res => {
                $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
            });
        },
        // https://stackoverflow.com/questions/149055/how-can-i-format-numbers-as-dollars-currency-string-in-javascript
        format_money(n) {
            if (n) {
                if (n == 'n\\a') {
                    return n;
                } else {
                    return n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,') + ' ' + this.current_currency.symbol;
                }
            } else {
                return 'n\\a';
            }
        },
        // https://stackoverflow.com/questions/11832914/round-to-at-most-2-decimal-places-only-if-necessary
        roundNumber: function(num, scale) {
            if (!("" + num).includes("e")) {
                return +(Math.round(num + "e+" + scale) + "e-" + scale);
            } else {
                var arr = ("" + num).split("e");
                var sig = "";
                if (+arr[1] + scale > 0) {
                    sig = "+";
                }
                return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
            }
        },
        format_datetime(str) {
            return moment(str).format('DD.MM.YYYY HH:mm');
        },
        fullName(contact) {
            return (contact.name ? contact.name : '') + ' ' + (contact.surname ? contact.surname : '')
        },
        get_estimate_fork(estimate) {
            return this.forks.filter(function(fork) {
                return fork.id == estimate.fork_id
            })[0].name;
        },
        estimate_number(estimate) {
            if (estimate.service) {
                return this.service_number(estimate.service) +
                    (estimate.option != null ? " " + this.$t('template.option') + estimate.option : "") +
                    (estimate.revision != null ? " " + this.$t('template.revision') + estimate.revision : "") +
                    (estimate.fork_id != null ? " " + this.get_estimate_fork(estimate) : "");
            } else {
                return '';
            }
        },
        service_number(service) {
            return service.estimate_number +
                (service.additional != null ? " " + this.$t('template.additional') + service.additional : "")
        },
        get_visual_state(service) {
            return '<span style="margin-left: 5px;background-color: ' + this.states[service.service_state_id].color + '">' + this.states[service.service_state_id].name + '</span>';
        },
        get_status_by_id(id) {
            if (id in this.states) {
                return this.states[id];
            } else {
                return null;
            }
        },
        params(data) {
            return Object.entries(data).map(([key, val]) => `${key}=${encodeURIComponent(data[key])}`).join('&');
        },
        onLanguageChange(event) {
            let $this = this;
            let val = event.target.value;
            this.$store.dispatch('changeLanguage', { locale: val })
                .then(() => {
                    $this.$i18n.locale = val;
                });
        },
        formatFinanceValue(value) {
            if (isNaN(value)) {
                return 0;
            }
            return (value * 1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },
        is_user_in_company() {
            let flag = false;
            this.groups.forEach(g => {
                g.users_ids.forEach(u => {
                    if (g.type === 2 && u === this.user.id) {
                        flag = true;
                    }
                });
            });

            return flag;
        },
        getChartTitle(widget) {
            switch (widget.data_type) {
                case 1:
                    return this.$t('dashboard.statuses') + ' (' + widget.additional_data.total_count + ')';
                case 2:
                    return this.$t('dashboard.referrers') + ' (' + widget.additional_data.total_count + ')';
                case 3:
                    return this.$t('dashboard.avg_status_time') + ' ' + widget.state.name;
                case 4:
                    return this.$t('dashboard.avg_status_price') + ' ' + widget.state.name;
                case 5:
                    return this.$t('dashboard.services_with_state_count') + ' ' + widget.state.name;
                case 6:
                    return this.$t('dashboard.services_with_state_sum') + ' ' + widget.state.name;
                case 7:
                    return this.$t('dashboard.Status_duration') + ' ' + widget.state.name;
                case 8:
                    return this.$t('dashboard.companies_referrers') + ' (' + widget.additional_data.total_count + ')';
            }
        }
    },
    computed: {
        ...mapGetters({
            states: 'getServiceStatesById',
            event_types: 'getEventTypesById',
            global_settings: 'getGlobalSettings',
            user: 'getUser',
            users_by_id: 'getUsersById',
            forks: 'getEstimateForks',
            groups_by_id: 'getGroupsById',
            groups: 'getGroups',
            service_scopes_by_id: 'getServiceScopesById',
            access_token: 'getAccessToken',
            logo: 'getLogo',
            shared_locale: 'getLocale',
        }),
        pusher_common_channel_name() {
            return location.host.replace(/:/g, '-');
        },
        pusher_user_channel_name() {
            return this.pusher_common_channel_name + '-user-' + this.user.id;
        },
        pusher_online_channel_name() {
            return this.pusher_common_channel_name + '-online';
        },
        current_user_service_scope() {
            return this.service_scopes_by_id[this.groups_by_id[this.user.group_id].service_scope_id];
        },
        settings() {
            return this.$store.getters.getGlobalSettings.settings;
        },
        modules() {
            let gs = this.$store.getters.getGlobalSettings;
            if (gs) {
                return this.$store.getters.getGlobalSettings.modules;
            } else {
                return null;
            }
        },
        enable_companies() {
            let gs = this.$store.getters.getGlobalSettings;
            if (gs) {
                return this.$store.getters.getGlobalSettings.enable_companies;
            } else {
                return null;
            }
        },
        permissions() {
            return this.$store.getters.getUser.roles;
        },
        current_currency() {
            return this.currencies[this.global_settings.currency];
        },
        department_states() {
            let $this = this;
            let tmp_states = [];
            let started = false;
            Object.values(this.states).forEach(function(state) {
                // if (state.id == $this.$root.current_user_service_scope.start_service_state_id) {
                //     started = true;
                // }
                if ( /*started && */ state.deleted_at == null && state.id !== $this.$root.current_user_service_scope.end_service_state_id) {
                    tmp_states.push(state.id);
                }
                if (state.id == $this.$root.current_user_service_scope.end_service_state_id) {
                    started = false;
                }
            });
            return tmp_states;
        },
    },
    watch: {
        user(val) {
            if (val) {
                this.locale = val.site_language;
                this.change_language();

                if (process.env.NODE_ENV === 'production') {
                    Sentry.configureScope((scope) => {
                        scope.setUser({
                            id: val.id,
                            username: val.name,
                            email: val.email,
                        });
                    });
                }
            }
        },
        shared_locale(val) {
            if (val) {
                this.locale = val;
                this.change_language();
            }
        },
    },
    beforeDestroy: function() {
        this.notif_read && this.$bus.$off("notif_read", this.notif_read);
    },
}).$mount('#erp-application');