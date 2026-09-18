<template>
    <div  class="reserv reserv_center" v-cloak>
        <loading v-model:active="isLoading" :is-full-page="fullPage"/>
        <div class="booking">

            <div class="title-h3" style="margin-bottom: 0.5rem;margin-top: 0.5rem;">{{$t('select_emp')}}</div>
            <div class='radio_employments'>

                <div class="radio" v-for="(employment,index) in employments" :key="index">
                    <input @change="emplChange(employment.id)"  name="emp" :value="employment.id" :id="'emp'+employment.id+'_'+index"
                        type="radio" >
                    <label  :for="'emp'+employment.id+'_'+index"  class="radio-label">{{ employment.title }}</label>
                </div>

            </div>
            <div class="title-h3" style="margin-bottom: 0.5rem;margin-top: 0.5rem;">{{$t('ten_opt')}}</div>
            <div class='radio_employments tennis_options'  v-if="employment_id&&$store.state.employments[employment_id].tennis_options">

                <div class="checkbox-agree js_valid_checkbox input-container pass" v-for="(tennis_option,key,index) in $store.state.employments[employment_id].tennis_options" :key="index" style="max-width: inherit;">
                    <input type="checkbox"
                           v-model="tennis_option_arr"
                           @change="tennis_optionChange()"
                           :value="key"
                           :id="'tennis_option_'+key+'_'+index"
                           class="focus-pay">
                    <label  :for="'tennis_option_'+key+'_'+index"  style="visibility: visible;">
                        <span class="info">{{ tennis_option.title }} <span>({{tennis_option.price}} грн.)</span> </span>
                    </label>
                </div>
            </div>
            <div style="margin-bottom: 1rem;text-align: center">
                <button :disabled="disabled"  style="min-width: 250px;"  @click.prevent="setStep('login')" class="btn btn-orange">{{$t('go_pay')}}</button>
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
        }),
    },
    methods:{
        setStep(step){
            if(this.phone){
                this.$store.commit('stepGlobalSet', 'result');
            }else{
                this.$store.commit('stepGlobalSet', step);
            }

        },
        emplChange(employment_id){
            this.disabled=false;
            this.tennis_option_arr=[];
            this.$store.commit('employmentOrderSet',employment_id);
        },

        tennis_optionChange(){
            this.$store.commit('tennis_optionsOrderSet',this.tennis_option_arr)
        },
    },

}
</script>

