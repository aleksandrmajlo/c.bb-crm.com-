<template>
    <div v-cloak>
        <!-- <pre style="border:10px solid red;margin:10px;width: 100%;display: block;padding: 2rem;">{{pay_id}}</pre> -->
        <div class="reserv reserv_center">
            <loading v-model:active="isLoading" :is-full-page="fullPage"/>
            <div class="wrapWay">
                <p>{{ $t('clientFirstName') }}</p>
                <div class="wrapPhone input-container">
                    <input style="min-width: 300px;" class="input1 input-phone" v-model="first_name_mod"/>
                </div>
                <p>{{ $t('clientLastName') }}</p>
                <div class="wrapPhone input-container">
                    <input class="input1 input-phone" v-model="last_name_mod"/>
                </div>
                <button :disabled="disabled" class="btn btn-orange" @click="pay_datas">{{ $t('message.pay') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import Loading from "vue-loading-overlay";
import {mapState, mapActions, mapGetters} from 'vuex';

export default {
    name: "Way",
    components: {Loading},
    data() {
        return {
            first_name_mod: '',
            last_name_mod: '',
            isLoading: false,
            fullPage: true,
        }
    },
    computed: {
        ...mapState({
            address: (state) => state.address,
            phone_number: (state) => state.phone_number,
            phone: (state) => state.phone,
            pay_id: (state) => state.pay_id,
            route: (state) => state.route,
        }),
        disabled() {
            let b = true;
            if (this.last_name_mod.length > 0 && this.first_name_mod.length > 0) {
                b = false;
            }
            return b
        }
    },
    mounted() {

        // success 
        window.addEventListener("message", (event) => {
            if (event.data == 'WfpWidgetEventApproved') {
                console.log('success payment');
                location.href = `/${this.route}/success`;
            }
        }, false);

    },

    methods: {
        pay_datas() {
            this.isLoading = true;
            console.clear();
            console.log(this.pay_id+' pay_id');
            axios.post("/way",
                 {
                    pay_id: this.pay_id,
                    route: this.$store.state.route,
                    first_name: this.first_name_mod,
                    last_name: this.last_name_mod,
                 }
                )
                .then(res => {
                    console.log(res.data);
                    if(res.data.suc){
                        this.pay(res.data.datas)
                    }
                })
                .catch(error => { })
                .then(() => { 
                    this.isLoading = false;
                });
        },
        pay(datas) {
            const wayforpay = new Wayforpay();
            wayforpay.run({
                "merchantAccount": datas.merchantAccount,
                "merchantDomainName":  datas.merchantDomainName,
                "merchantSignature": datas.merchantSignature,
                "returnUrl": datas.returnUrl,
                "serviceUrl": datas.serviceUrl,
                "orderReference": datas.orderReference,
                "orderDate": datas.orderDate,
                "amount": datas.amount,
                "currency": datas.currency,
                "productName": ["test"],
                "productPrice": [0.01],
                "productCount": [1],
                "clientFirstName": datas.clientFirstName,
                "clientLastName": datas.clientLastName,
                "clientCountry": datas.clientCountry,
                "clientPhone": datas.clientPhone,
            });
        }
    }
}
</script>
