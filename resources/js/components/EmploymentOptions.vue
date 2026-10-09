<template>
    <div  class="reserv reserv_center" v-cloak>
        <loading v-model:active="isLoading" :is-full-page="fullPage"/>
        <div class="booking">

            <div class="title-h3" style="margin-bottom: 0.5rem;margin-top: 0.5rem;">{{$t('select_emp')}}</div>
            <div class='radio_employments'>

                <div class="radio" v-for="(employment,index) in employments" :key="index">
                    <input @change="emplChange(employment.id)" name="emp" :value="employment.id" :id="'emp'+employment.id+'_'+index"
                        :checked="Number(employment_id) === Number(employment.id)"
                        type="radio" >
                    <label  :for="'emp'+employment.id+'_'+index"  class="radio-label">{{ employment.title }}</label>
                </div>

            </div>
            <div class="title-h3" style="margin-bottom: 0.5rem;margin-top: 0.5rem;">{{$t('ten_opt')}}</div>
            <div class='radio_employments tennis_options'  v-if="employment_id&&$store.state.employments[employment_id].tennis_options">

                <div class="tennis-option-row" v-for="(tennis_option,key,index) in $store.state.employments[employment_id].tennis_options" :key="key">
                    <div class="checkbox-agree js_valid_checkbox input-container pass" style="max-width: inherit;">
                        <input type="checkbox"
                               v-model="tennis_option_arr"
                               @change="tennis_optionChange(key)"
                               :value="key"
                               :id="'tennis_option_'+key+'_'+index"
                               class="focus-pay">
                        <label  :for="'tennis_option_'+key+'_'+index"  style="visibility: visible;">
                            <span class="info">{{ tennis_option.title }} <span>({{tennis_option.price}} грн.)</span> </span>
                        </label>
                    </div>

                    <div
                        v-if="route === 'utc' && tennis_option.isPerPiece && isOptionSelected(key)"
                        class="tennis-option-quantity"
                    >
                        <span class="tennis-option-quantity__label">Кількість</span>
                        <div class="tennis-option-quantity__control">
                            <button
                                type="button"
                                aria-label="Зменшити кількість"
                                :disabled="normalizedQuantity(key) <= 1"
                                @click="changeQuantity(key, -1)"
                            >−</button>
                            <input
                                class="tennis-option-quantity__input"
                                type="number"
                                min="1"
                                max="99"
                                inputmode="numeric"
                                :value="quantityValue(key)"
                                @input="setQuantity(key, $event.target.value)"
                                @blur="normalizeQuantity(key)"
                                @keydown.enter.prevent="normalizeQuantity(key)"
                            >
                            <button
                                type="button"
                                aria-label="Збільшити кількість"
                                :disabled="normalizedQuantity(key) >= 99"
                                @click="changeQuantity(key, 1)"
                            >+</button>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-bottom: 1rem;text-align: center">
                <button :disabled="disabled || isLoading"  style="min-width: 250px;"  @click.prevent="setStep('login')" class="btn btn-orange">{{$t('go_pay')}}</button>
            </div>
        </div>
    </div>
</template>
<script>
import {mapState, mapActions} from 'vuex';
import Loading from "vue-loading-overlay";
export default {
    name: "EmploymentOptions",
    components: {Loading},
    data(){
        return{
            tennis_option_arr:[],
            tennis_option_quantities:{},
            disabled:true,
            isLoading: false,
            fullPage: false,
        }
    },
    computed: {
        ...mapState({
            phone: (state) => state.phone,
            employments: (state) => state.employments,
            employment_id: (state) => state.employment_id,
            tennis_options: (state) => state.tennis_options,
            route: (state) => state.route,
        }),
    },
    mounted(){
        this.restoreOptions();
    },
    methods:{
        setStep(step){
            if(this.phone){
                if (this.isLoading) {
                    return;
                }
                this.isLoading = true;
                this.$store.dispatch('addBooking').then(() => {
                    this.$store.commit('stepGlobalSet', 'result');
                }).finally(() => {
                    this.isLoading = false;
                });
            }else{
                this.$store.commit('stepGlobalSet', step);
            }

        },
        emplChange(employment_id){
            this.disabled=false;
            this.tennis_option_arr=[];
            this.tennis_option_quantities={};
            this.$store.commit('employmentOrderSet',employment_id);
            this.$store.commit('tennis_optionsOrderSet', []);
        },

        tennis_optionChange(key){
            if (this.isOptionSelected(key) && this.tennis_option_quantities[key] === undefined) {
                this.tennis_option_quantities[key] = 1;
            }
            this.syncOptions();
        },
        isOptionSelected(key){
            return this.tennis_option_arr.includes(key);
        },
        quantityValue(key){
            return this.tennis_option_quantities[key] === undefined
                ? 1
                : this.tennis_option_quantities[key];
        },
        normalizedQuantity(key){
            const quantity = Number.parseInt(this.tennis_option_quantities[key], 10);
            return Math.min(99, Math.max(1, Number.isFinite(quantity) ? quantity : 1));
        },
        setQuantity(key, value){
            this.tennis_option_quantities[key] = value;
            if (value !== '') {
                this.syncOptions();
            }
        },
        normalizeQuantity(key){
            this.tennis_option_quantities[key] = this.normalizedQuantity(key);
            this.syncOptions();
        },
        changeQuantity(key, direction){
            this.tennis_option_quantities[key] = Math.min(
                99,
                Math.max(1, this.normalizedQuantity(key) + direction)
            );
            this.syncOptions();
        },
        syncOptions(){
            if (this.route !== 'utc') {
                this.$store.commit('tennis_optionsOrderSet', [...this.tennis_option_arr]);
                return;
            }

            const definitions = this.$store.state.employments[this.employment_id]?.tennis_options || {};
            const selected = {};
            this.tennis_option_arr.forEach((key) => {
                selected[key] = definitions[key]?.isPerPiece
                    ? this.normalizedQuantity(key)
                    : 1;
            });
            this.$store.commit('tennis_optionsOrderSet', selected);
        },
        restoreOptions(){
            this.disabled = !this.employment_id;

            if (Array.isArray(this.tennis_options)) {
                this.tennis_option_arr = [...new Set(this.tennis_options)];
                this.tennis_options.forEach((key) => {
                    this.tennis_option_quantities[key] = (this.tennis_option_quantities[key] || 0) + 1;
                });
                return;
            }

            if (this.tennis_options && typeof this.tennis_options === 'object') {
                this.tennis_option_arr = Object.keys(this.tennis_options);
                Object.entries(this.tennis_options).forEach(([key, quantity]) => {
                    this.tennis_option_quantities[key] = quantity;
                });
            }
        },
    },

}
</script>

<style scoped>
.tennis-option-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 56px;
    padding: 7px 0;
    border-bottom: 1px solid rgba(38, 56, 82, .1);
}

.tennis-option-row:last-child {
    border-bottom: 0;
}

.tennis-option-row .checkbox-agree {
    flex: 1 1 auto;
    min-width: 0;
    margin: 0;
}

.tennis-option-quantity {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 10px;
}

.tennis-option-quantity__label {
    color: #5e6877;
    font-size: 14px;
    font-weight: 600;
}

.tennis-option-quantity__control {
    display: grid;
    grid-template-columns: 38px 56px 38px;
    height: 40px;
    overflow: hidden;
    border: 1px solid #c8d2df;
    border-radius: 9px;
    background: #fff;
}

.tennis-option-quantity__control button,
.tennis-option-quantity__control input {
    width: 100%;
    height: 38px;
    margin: 0;
    border: 0;
    border-radius: 0;
    box-shadow: none;
}

.tennis-option-quantity__control button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: #eef4fb;
    color: #1e5799;
    font-size: 20px;
    font-weight: 700;
    cursor: pointer;
}

.tennis-option-quantity__control button:hover:not(:disabled) {
    background: #dceafb;
}

.tennis-option-quantity__control button:disabled {
    color: #a4afbd;
    cursor: not-allowed;
}

.tennis-option-quantity__control .tennis-option-quantity__input {
    padding: 0 4px !important;
    border-right: 1px solid #d9e1eb;
    border-left: 1px solid #d9e1eb;
    background: #fff;
    color: #17221b;
    font-size: 16px;
    font-weight: 700;
    line-height: 38px;
    text-align: center;
    font-variant-numeric: tabular-nums;
    -webkit-text-fill-color: #17221b;
    -moz-appearance: textfield;
}

.tennis-option-quantity__control input::-webkit-inner-spin-button,
.tennis-option-quantity__control input::-webkit-outer-spin-button {
    margin: 0;
    -webkit-appearance: none;
}

.tennis-option-quantity__control button:focus-visible,
.tennis-option-quantity__control input:focus-visible {
    position: relative;
    z-index: 1;
    outline: 2px solid #2f80d0;
    outline-offset: -2px;
}

@media (max-width: 640px) {
    .tennis-option-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 6px;
    }

    .tennis-option-quantity {
        align-self: stretch;
        justify-content: space-between;
        padding-left: 34px;
    }
}
</style>
