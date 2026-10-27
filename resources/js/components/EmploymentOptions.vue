<template>
    <div  class="reserv reserv_center" v-cloak>
        <loading v-model:active="isLoading" :is-full-page="fullPage"/>
        <div class="booking">

            <div class="booking-choice">
                <label class="booking-choice__label" for="employment-select">
                    <span class="booking-choice__number">1</span>
                    <span>{{$t('select_emp')}}</span>
                </label>
                <div class="booking-select">
                    <select
                        id="employment-select"
                        v-model="selectedEmploymentId"
                        @change="emplChange"
                    >
                        <option value="" disabled>{{$t('choose_session')}}</option>
                        <option
                            v-for="employment in employmentList"
                            :key="employment.id"
                            :value="String(employment.id)"
                        >
                            {{ employment.title }}
                        </option>
                    </select>
                    <span class="booking-select__icon" aria-hidden="true"></span>
                </div>
                <p v-if="selectedEmployment" class="booking-choice__summary">
                    {{ selectedEmployment.title }} · {{ selectedEmployment.price }} грн
                </p>
            </div>

            <div v-if="route === 'utc' && priceChoices.length > 1" class="booking-choice">
                <label class="booking-choice__label" for="duration-price-select">
                    <span class="booking-choice__number">2</span>
                    <span>{{$t('duration_price')}}</span>
                </label>
                <div class="booking-select">
                    <select
                        id="duration-price-select"
                        v-model="employmentPriceId"
                        @change="priceChoiceChange"
                    >
                        <option
                            v-for="choice in priceChoices"
                            :key="choice.id"
                            :value="String(choice.id)"
                        >
                            {{ priceChoiceLabel(choice) }}
                        </option>
                    </select>
                    <span class="booking-select__icon" aria-hidden="true"></span>
                </div>
            </div>

            <div v-if="route === 'utc' && selectedEmployment" class="booking-choice">
                <label class="booking-choice__label" for="trainer-select">
                    <span class="booking-choice__number">{{ priceChoices.length > 1 ? 3 : 2 }}</span>
                    <span>{{$t('trainer')}}</span>
                </label>
                <div class="booking-select">
                    <select
                        id="trainer-select"
                        v-model="selectedTrainerId"
                        @change="trainerChange"
                    >
                        <option value="">{{$t('without_trainer')}}</option>
                        <option
                            v-for="trainer in trainers"
                            :key="trainer.id"
                            :value="String(trainer.id)"
                        >
                            {{ trainerLabel(trainer) }}
                        </option>
                    </select>
                    <span class="booking-select__icon" aria-hidden="true"></span>
                </div>
            </div>

            <div
                v-if="selectedEmployment && Object.keys(optionDefinitions).length"
                class="title-h3"
                style="margin-bottom: 0.5rem;margin-top: 1.25rem;"
            >{{$t('ten_opt')}}</div>
            <div class='radio_employments tennis_options' v-if="selectedEmployment && Object.keys(optionDefinitions).length">

                <div class="tennis-option-row" v-for="(tennis_option,key,index) in optionDefinitions" :key="key">
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
            selectedEmploymentId:'',
            employmentPriceId:'default',
            selectedTrainerId:'',
            disabled:true,
            isLoading: false,
            fullPage: false,
        }
    },
    computed: {
        ...mapState({
            phone: (state) => state.phone,
            employments: (state) => state.employments,
            trainers: (state) => state.trainers,
            employment_id: (state) => state.employment_id,
            employment_price_id: (state) => state.employment_price_id,
            trainer_id: (state) => state.trainer_id,
            tennis_options: (state) => state.tennis_options,
            tennis_options_catalog: (state) => state.tennis_options_catalog,
            route: (state) => state.route,
        }),
        employmentList(){
            return Object.values(this.employments || {});
        },
        selectedEmployment(){
            return this.selectedEmploymentId
                ? this.employments[this.selectedEmploymentId] || null
                : null;
        },
        priceChoices(){
            return this.selectedEmployment?.price_choices || [];
        },
        optionDefinitions(){
            if (
                this.selectedEmployment
                && Object.prototype.hasOwnProperty.call(this.selectedEmployment, 'tennis_options')
            ) {
                return this.selectedEmployment.tennis_options || {};
            }

            // Compatibility with an older API response that only contained a
            // global option catalog and no per-employment option collection.
            return this.tennis_options_catalog || {};
        },
        selectedPrice(){
            return this.priceChoices.find(
                choice => String(choice.id) === String(this.employmentPriceId)
            ) || this.priceChoices[0] || null;
        },
        selectedTrainer(){
            return this.trainers.find(
                trainer => String(trainer.id) === String(this.selectedTrainerId)
            ) || null;
        },
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
        emplChange(){
            this.disabled=false;
            this.tennis_option_arr=[];
            this.tennis_option_quantities={};
            this.employmentPriceId='default';
            this.selectedTrainerId='';
            this.$store.commit('employmentOrderSet',this.selectedEmploymentId);
            this.$store.commit('employmentPriceOrderSet', {
                id: this.employmentPriceId,
                label: this.selectedPrice ? this.priceChoiceLabel(this.selectedPrice) : '',
            });
            this.$store.commit('trainerOrderSet', {id: null, title: '', price: 0});
            this.$store.commit('durationOrderSet', null);
            this.$store.commit('tennis_optionsOrderSet', []);
        },

        priceChoiceChange(){
            this.$store.commit('employmentPriceOrderSet', {
                id: this.employmentPriceId,
                label: this.selectedPrice ? this.priceChoiceLabel(this.selectedPrice) : '',
            });
            this.$store.commit(
                'durationOrderSet',
                this.selectedPrice?.minutes ? Number(this.selectedPrice.minutes) : null
            );
        },
        trainerChange(){
            this.$store.commit('trainerOrderSet', {
                id: this.selectedTrainer ? this.selectedTrainer.id : null,
                title: this.selectedTrainer ? this.selectedTrainer.name : '',
                price: this.selectedTrainer ? this.trainerPrice(this.selectedTrainer) : 0,
            });
        },
        priceChoiceLabel(choice){
            if (!choice?.minutes) {
                return this.$t('base_hourly_price', {price: choice?.price || 0});
            }

            return this.$t('duration_price_option', {
                minutes: choice.minutes,
                price: choice.price,
            });
        },
        trainerLabel(trainer){
            return `${trainer.name} · +${this.trainerPrice(trainer)} грн/год`;
        },
        trainerPrice(trainer){
            return Number(trainer.tariffs?.[this.selectedEmploymentId] ?? 0);
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

            const definitions = this.optionDefinitions;
            const selected = {};
            this.tennis_option_arr.forEach((key) => {
                selected[key] = definitions[key]?.isPerPiece
                    ? this.normalizedQuantity(key)
                    : 1;
            });
            this.$store.commit('tennis_optionsOrderSet', selected);
        },
        restoreOptions(){
            this.selectedEmploymentId = this.employment_id
                ? String(this.employment_id)
                : '';
            this.employmentPriceId = this.employment_price_id
                ? String(this.employment_price_id)
                : 'default';
            this.selectedTrainerId = this.trainer_id
                ? String(this.trainer_id)
                : '';
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
.booking-choice {
    margin-bottom: 18px;
    padding: 16px;
    border: 1px solid rgba(38, 56, 82, .12);
    border-radius: 16px;
    background: linear-gradient(145deg, #fff 0%, #f8fafc 100%);
    box-shadow: 0 8px 24px rgba(38, 56, 82, .06);
}

.booking-choice__label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    color: #17221b;
    font-size: 16px;
    font-weight: 700;
}

.booking-choice__number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
    border-radius: 50%;
    background: #f58220;
    color: #fff;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(245, 130, 32, .25);
}

.booking-select {
    position: relative;
}

.booking-select select {
    width: 100%;
    min-height: 52px;
    padding: 0 48px 0 16px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    outline: none;
    background: #fff;
    color: #17221b;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.3;
    cursor: pointer;
    appearance: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.booking-select select:hover {
    border-color: #f58220;
}

.booking-select select:focus-visible {
    border-color: #f58220;
    box-shadow: 0 0 0 4px rgba(245, 130, 32, .14);
}

.booking-select__icon {
    position: absolute;
    top: 50%;
    right: 18px;
    width: 10px;
    height: 10px;
    border-right: 2px solid #667085;
    border-bottom: 2px solid #667085;
    pointer-events: none;
    transform: translateY(-70%) rotate(45deg);
}

.booking-choice__summary {
    margin: 9px 2px 0;
    color: #667085;
    font-size: 14px;
}

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
