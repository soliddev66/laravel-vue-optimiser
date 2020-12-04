/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('moment');

import Vue from 'vue';
import Vuex from 'vuex';
import VuejsDialog from 'vuejs-dialog';
import VSwitch from 'v-switch-case';
import VueTabs from 'vue-nav-tabs';
import FileManager from 'lv-file-manager';
import VModal from 'vue-js-modal';
import Select2 from 'v-select2-component';
import vueDebounce from 'vue-debounce';

import moment from './plugins/moment.js';

import 'vuejs-dialog/dist/vuejs-dialog.min.css';
import 'vue-nav-tabs/themes/vue-tabs.css';
import 'select2-bootstrap-theme/dist/select2-bootstrap.css';

Vue.use(Vuex);
const store = new Vuex.Store();

Vue.use(VuejsDialog);
Vue.use(VSwitch);
Vue.use(VueTabs);
Vue.use(FileManager, {store});
Vue.use(VModal);
Vue.use(vueDebounce)
Vue.use(moment);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
Vue.component('Select2', Select2);
$.fn.select2.defaults.set('theme', 'bootstrap');
Vue.component('pagination', require('laravel-vue-pagination'));

Vue.component('account-wizard', require('./components/AccountWizard.vue').default);
Vue.component('traffic-sources', require('./components/TrafficSources.vue').default);
Vue.component('trackers', require('./components/Trackers.vue').default);

Vue.component('campaigns', require('./components/Campaigns.vue').default);
Vue.component('rules', require('./components/Rules.vue').default);
Vue.component('campaign', require('./components/Campaign.vue').default);
Vue.component('campaign-creator', require('./components/CampaignCreator.vue').default);

Vue.component('rules', require('./components/Rules.vue').default);
Vue.component('rule-creator', require('./components/RuleCreator.vue').default);
Vue.component('rule-rule-templates', require('./components/RuleRuleTemplates.vue').default);

Vue.component('rule-templates', require('./components/RuleTemplates.vue').default);
Vue.component('rule-template-creator', require('./components/RuleTemplateCreator.vue').default);

Vue.component('ad', require('./components/Ad.vue').default);
Vue.component('ad-creator', require('./components/AdCreator.vue').default);

Vue.component('queues', require('./components/Queues.vue').default);
Vue.component('dashboard', require('./components/Dashboard.vue').default);
Vue.component('select2', require('./plugins/Select2.vue').default);
Vue.component('summary-data', require('./components/SummaryData.vue').default);

Vue.component('errors', require('./components/Errors.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    store
});
