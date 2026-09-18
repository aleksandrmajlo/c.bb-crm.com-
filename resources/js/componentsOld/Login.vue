<template>
    <div v-cloak>
        <div class="card" style="width: 27rem;">

            <div class="card-body">
                <div v-if="showAdd">
                    <booking-add   @selectAction="selectActionEvent"></booking-add>
                </div>
                <div v-else-if="showChange">
                    <booking-change @selectAction="selectActionEvent" ></booking-change>
                </div>
                <div v-else>
                    <template v-if="showPhone">
                        <h2 class="card-title text-center">{{ $t("message.login") }}</h2>
                        <p >{{ $t('message.phone_enter') }}</p>
                        <div class="mb-3" >
                            <input class="form-control" id="phone_inp"/>
                        </div>
                        <div class="wrapButtonStep1 mb-3" v-if="phoneValid">
                            <button @click.prevent="sendPhone"
                                    :disabled="disabledSend" class="btn btn-success w-100">
                                {{ $t("message.code_get") }}
                            </button>
                        </div>
                    </template>
                    <template v-else-if="showCode">
                        <h2 class="card-title text-center">{{ $t("message.login") }}</h2>
                        <p >{{ $t('message.code') }}</p>
                        <div class="wrapButtonStep2 mb-3" >
                            <div class="invalid-feedback" v-if="code_error" style="display: block">
                                {{ $t("message.code_error") }}
                            </div>
                            <imask-input v-model="code" :mask="'000000'" :unmask="true"
                                         type="text" :lazy="false" class="form-control mb-3 w-100"
                                         :min="100000" :max="999999" :placeholder="$t('code')"
                                         @complete="onCompleteCode" @accept="onAcceptCode"/>
                            <button @click.prevent="sendCode"
                                    v-if="isCodeValid"
                                    :disabled="disabledCode" class="btn btn-success w-100">
                                {{ $t("message.leave") }}
                            </button>
                        </div>
                    </template>
                    <template v-else-if="showButtonSelect">
                        <h2 class="card-title text-center">{{ $t("message.menu") }}</h2>
                        <p >{{ $t('message.select_action') }}</p>
                        <div class="wrapButtonStep3 mb-3">
                            <button @click.prevent="selectAction(1)" class="btn btn-success mb-3 w-100">
                                {{ $t("message.create_book_but") }}
                            </button>
                            <button @click.prevent="selectAction(2)" class="btn btn-success w-100">
                                {{ $t("message.change_book_but") }}
                            </button>
                        </div>
                    </template>
                </div>
                <!--
                <pre style="border:5px solid blue;padding: 2rem"> {{ sms_id }} </pre>
                 -->
            </div>

        </div>
        <div class="language-switcher text-center mt-3">
            <a href="#" class="d-inline-block p-2" @click.prevent="switchLanguage('ua')">Українська</a>
            <a href="#" class="d-inline-block p-2" @click.prevent="switchLanguage('en')">English</a>
            <a href="#" class="d-inline-block p-2"  @click.prevent="switchLanguage('ru')">Русский</a>
        </div>

    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';
import {IMaskComponent} from 'vue-imask';
import BookingAdd from './BookingAdd.vue';
import BookingChange from './BookingChange.vue';

export default {
    name: "Login",
    components: {
        'imask-input': IMaskComponent,
        'booking-add': BookingAdd,
        'booking-change': BookingChange,
    },
    props: {
        route: {
            type: String,
            default: 'atmosphera'
        },
    },
    data() {
        return {
            showPhone: true,
            showCode: false,
            showButtonSelect: false,
            showAdd:false,
            showChange: false,

            LangFlag: ['ua', 'pl', 'tr', 'by', 'lv', 'lt', 'de', 'kz', 'kg', 'us'],
            phone: "",
            phoneValid: false,
            sms_id: "",

            code: '',
            isCodeValid: false,
            code_error: false,

            disabledSend: false,
            disabledCode: false,
            disabledViber: false,
            disabledTelegram: false,

        }

    },
    created() {
        this.$store.dispatch('getSettingsClub').then(() => {});
    },
    mounted() {

        let self = this;
        let input = document.querySelector("#phone_inp");
        if (input) {
            let initialCountry = "ua";
            if (typeof country_ISO != "undefined") {
                initialCountry = country_ISO;
                let incl = this.$LangFlag.includes(country_ISO);
                if (!incl) {
                    this.$LangFlag.push(country_ISO);
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

        sendPhone() {
            this.disabledSend = true;
            axios
                .post("/sendPhone", {
                    phone: this.phone,
                    route: this.route
                })
                .then((response) => {
                    if (response.data.suc) {
                        this.sms_id = response.data.sms_id;
                        this.showPhone = false;
                        this.showCode = true;
                    }
                })
                .catch((error) => { })
                .then(() => {
                    this.disabledSend = false;
                });
        },
        
        sendCode() {
            this.disabledCode = true;
            this.code_error = false;
            axios
                .post("/sendCode", {
                    code: this.code,
                    sms_id: this.sms_id
                })
                .then((response) => {
                    if (response.data.suc) {
                        this.showCode = false;
                        this.showButtonSelect = true;
                        this.$store.commit('phoneSet', this.phone);

                    } else {
                        this.code_error = true;
                    }
                })
                .catch((error) => {})
                .then(() => {
                    this.disabledCode = false;
                });
        },

        onAcceptCode() {
            this.isCodeValid = false;
        },
        onCompleteCode() {
            this.isCodeValid = true;
        },

        selectAction(ind) {
            if (ind == 1) {
                this.showAdd = true;
            }
            if (ind == 2) {
                this.showChange = true;
            }
        },

        switchLanguage(lang) {
            this.$i18n.locale = lang;
        },

        selectActionEvent() {
            this.showAdd = false;
            this.showChange = false;
            this.showPhone = false;
            this.showCode = false;
            this.showButtonSelect= true;
        }
    }
}
</script>

