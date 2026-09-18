/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import './common';
// import inputMask from './directives/inputMask';

import { createApp } from 'vue';


import en from './locales/en.json';
import ru from './locales/ru.json';
import ua from './locales/ua.json';
import { createI18n } from 'vue-i18n'
const i18n = createI18n({
    locale:  'ua',
    fallbackLocale: 'ua',
    messages: {
      en,
      ua,
      ru
  },
  })

import store from './store'
const app = createApp({});

app.use(i18n);
app.use(store)


import App from './components/App.vue';
app.component('app', App);


app.mount('#app');
