<template>
<!--    <div class="wrapper loginFormWraper" v-cloak>-->
    <div class="loginFormWraper" v-cloak>
        <div v-if="step==1" class="container-advertisment">
            <loading v-model:active="isLoading" :is-full-page="fullPage"/>
            <div class="all-slect-table flex-center">
                <div class="change-table text-center loginForm cont-info">
                    <div class="title-h3 bottom-m">{{ $t("message.login") }}</div>
                    <p>{{ $t('message.phone_enter') }}</p>
                    <div class="wrapPhone input-container">
                        <input class="input1 input-phone" id="phone_inp"/>
                    </div>
<!--                    <div v-if="!isValid" class="error">{{ validationMessage }}</div>-->
                    <button :disabled="!phoneValid"
                        @click.prevent="sendPhone"
                        class="btn btn-orange nowrap phone_enter_button">{{ $t('message.login_enter') }}
                    </button>
                </div>
            </div>
        </div>
        <div v-if="step==2" class="container-advertisment">
            <loading v-model:active="isLoading" :is-full-page="fullPage"/>
            <div class="all-slect-table flex-center">
                <div class="change-table text-center loginForm cont-info">
                    <div class="title-h3 bottom-m">{{ $t("message.login") }}</div>
                    <p>{{ $t('message.code') }}</p>
                    <div class="wrapPhone input-container">
                        <imask-input v-model="code" :mask="'000000'" :unmask="true"
                                     type="text" :lazy="false" class="input1 input-phone"
                                     :min="100000" :max="999999" :placeholder="$t('code')"
                                     @complete="onCompleteCode" @accept="onAcceptCode"/>
                    </div>
                    <div class="error" v-if="code_error">
                        {{ $t("message.code_error") }}
                    </div>
                    <button  @click.prevent="sendCode" :disabled="disabledCode"  class="btn btn-orange nowrap">{{ $t('code_button') }}</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import inputMask from './../directives/inputMask.js';
import {IMaskComponent} from 'vue-imask';

import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import {mapState, mapActions} from 'vuex';

import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';

export default {
    name: "Login",
    data() {
        return {
            step: 1,
            LangFlag: ['ua', 'us','pl', 'tr', 'lv', 'lt', 'de', 'kz', 'kg'],
            phone: '',
            phoneValid: false,

            isValid: true,
            validationMessage: '',
            disabledPhone: true,

            sms_id: "",

            code: '',
            isCodeValid: false,
            code_error: false,

            isLoading: false,
            fullPage: false,

        }
    },
    components: {
        Loading,
        'imask-input': IMaskComponent,
    },
    directives: {
        inputMask,
    },
    mounted() {
        let self = this;
        let input = document.querySelector("#phone_inp");
        if (input) {
            let initialCountry = "ua";
            if (typeof country_ISO != "undefined") {
                initialCountry = country_ISO;
                let incl = this.LangFlag.includes(country_ISO);
                if (!incl) {
                    this.LangFlag.push(country_ISO);
                }
            }
            self.iti = intlTelInput(input, {
                initialCountry: initialCountry,
                onlyCountries: this.LangFlag,
                separateDialCode: true,
                autoHideDialCode: false,
                nationalMode: false,
                utilsScript: "/libs/intl-tel-input/build/js/utils.js",
            });
            input.addEventListener("countrychange", function () {
                self.phone = self.iti.getNumber();
                if (self.iti.isValidNumber()) {
                    self.phoneValid = true;
                } else {
                    self.phoneValid = false;
                }
            });
            input.addEventListener("input", function () {
                self.phone = self.iti.getNumber();
                if (input.value.trim()) {
                    if (self.iti.isValidNumber()) {
                        self.phoneValid = true;
                    } else {
                        self.phoneValid = false;
                    }
                }
            });
        }
    },
    methods: {
        validatePhoneNumber() {
            const phonePattern = /^\+38 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;
            if (!this.phone.match(phonePattern)) {
                this.isValid = false;
                this.disabledPhone = 1;
                this.validationMessage = 'Please enter a valid Ukrainian phone number.';
            } else {
                this.isValid = true;
                this.disabledPhone = false;
                this.validationMessage = '';
            }
        },
        onAcceptCode() {
            this.isCodeValid = false;
        },
        onCompleteCode() {
            this.isCodeValid = true;
        },
        sendPhone() {
            this.isLoading = 1
            axios
                .post("/sendPhone", {
                    phone: this.phone,
                    route: this.$store.state.route
                })
                .then((response) => {
                    if (response.data.suc) {
                        this.sms_id = response.data.sms_id;
                        this.step = 2;
                    }
                })
                .catch((error) => {
                })
                .then(() => {
                    this.isLoading = false;
                });
        },
        sendCode() {
            this.isLoading = 1;
            axios
                .post("/sendCode", {
                    code: this.code,
                    sms_id: this.sms_id
                })
                .then((response) => {
                    if (response.data.suc) {
                        this.$store.commit('phoneSet', this.phone);
                        this.$store.dispatch('addBooking',{
                            primary:true,
                        }).then(()=>{
                            this.isLoading=false;
                            this.$store.commit('stepGlobalSet', 'result');
                        })
                    } else {
                        this.code_error = true;
                    }
                })
                .catch((error) => {
                })
                .then(() => {
                    this.isLoading = false;
                });
        },
    },
}
</script>

