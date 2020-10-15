/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('moment');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('account-wizard', require('./components/AccountWizard.vue').default);
Vue.component('traffic-sources', require('./components/TrafficSources.vue').default);
Vue.component('trackers', require('./components/Trackers.vue').default);
Vue.component('campaigns', require('./components/Campaigns.vue').default);
Vue.component('campaign', require('./components/Campaign.vue').default);
Vue.component('queues', require('./components/Queues.vue').default);
Vue.component('campaign-creator', require('./components/CampaignCreator.vue').default);
Vue.component('select2', require('./plugins/Select2.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
