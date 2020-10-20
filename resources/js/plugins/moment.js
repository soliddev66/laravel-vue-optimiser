import moment from 'moment';

moment.locale('en_HK');

export default function install(Vue) {
    Object.defineProperties(Vue.prototype, {
        $moment: {
            get() {
                return moment;
            }
        }
    })
}
