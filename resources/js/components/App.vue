<template>
    <div class="container" v-cloak>
        <div class="language-switcher text-center mt-3">
            <a href="#" class="d-inline-block  item_lang p-2" :class="$i18n.locale=='ua'? 'active' : ''" @click.prevent="switchLanguage('ua')">Ua</a>
            <a href="#" class="d-inline-block item_lang p-2" :class="$i18n.locale=='en'? 'active' : ''"  @click.prevent="switchLanguage('en')">En</a>
            <a href="#" class="d-inline-block item_lang p-2" :class="$i18n.locale=='ru'? 'active' : ''"   @click.prevent="switchLanguage('ru')">Ru</a>
        </div>

        <login  v-if="stepGlobal=='login'"></login>
        <action-select v-if="stepGlobal=='action_select'"></action-select>
        <sports-days  v-if="stepGlobal=='sports_days'"></sports-days>
        <employment-options v-if="stepGlobal=='employment_options'"></employment-options>
        <result  v-if="stepGlobal=='result'"></result>
        <select-pay v-if="stepGlobal=='select_pay'"></select-pay>
        <way v-if="stepGlobal=='way'"></way>
        <my-bookings v-if="stepGlobal=='my_bookings'"></my-bookings>



        <!--

                <pre style="border:10px solid brown;margin:10px;width: 100%;display: block;padding: 2rem;">
            {{pay_id}}
        </pre>

                <pre style="border:10px solid brown;margin:10px;width: 100%;display: block;padding: 2rem;">
            {{stepGlobal}}
        </pre>
        <pre style="border:10px solid green;margin:10px;width: 100%;display: block;padding: 2rem;">
           {{orders}}
        </pre> 

<pre style="border:10px solid brown;margin:10px;width: 100%;display: block;padding: 2rem;">
{{phone}}
</pre>



<pre style="border:10px solid rebeccapurple;margin:10px;width: 100%;display: block;padding: 2rem;">
{{pdf_name}}
</pre>

<pre style="border:10px solid brown;margin:10px;width: 100%;display: block;padding: 2rem;">
{{orders}}
</pre>

<pre style="border:10px solid blue;margin:10px;width: 100%;display: block;padding: 2rem;">
</pre>

<pre style="border:10px solid red;margin:10px;width: 100%;display: block;padding: 2rem;">
{{counter}}
</pre>

<pre style="border:10px solid greenyellow;margin:10px;width: 100%;display: block;padding: 2rem;">
{{times_dop}}
</pre>
-->
    </div>
</template>

<script>
import {mapState, mapActions} from 'vuex';
import Login from './Login.vue';
import ActionSelect from './ActionSelect.vue';
import EmploymentOptions from './EmploymentOptions.vue';
import SportsDays from './SportsDays.vue';
import Result from './Result.vue';
import MyBookings from './MyBookings.vue';
import SelectPay from './SelectPay.vue';
import Way from './pay/Way.vue';

export default {
    name: "App",
    components: {
        Login,
        ActionSelect,
        EmploymentOptions,
        SportsDays,
        Result,
        SelectPay,
        Way,
        MyBookings,
    },
    computed: {
        ...mapState({
            stepGlobal: (state) => state.stepGlobal,
            phone: (state) => state.phone,
            orders: (state) => state.orders,
            tables: (state) => state.tables,
            employments: (state) => state.employments,
            times: (state) => state.times,

            times_dop: (state) => state.times_dop,

            bookings: (state) => state.bookings,
            isAiax: (state) => state.isAiax,
            pay_id: (state) => state.pay_id,
            pdf_url: (state) => state.pdf_url,
            pdf_name: (state) => state.pdf_name,
            type_pay: (state) => state.type_pay,

        }),
    },
    data() {
        return {
            intervalId: null,
        }
    },
    created() {
        this.$store.dispatch('getSettingsClub').then(() => { });
    },
    mounted() {
        this.intervalId = setInterval(() => {
            if(!this.isAiax){
                this.$store.dispatch('updateClearOrder').then(() => { });
            }
        }, 60000);
    },
    beforeDestroy() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    },
    methods:{
        switchLanguage(lang) {
            this.$i18n.locale = lang;
        },
    }
}
</script>

