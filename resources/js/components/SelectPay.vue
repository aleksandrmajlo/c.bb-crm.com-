<template>
    <div  v-cloak>
        <div class="reserv reserv_center" v-if="!isPdf">
            <loading v-model:active="isLoading" :is-full-page="fullPage"/>
            <div class="booking">
                <div class="title-h1">{{ $t('select_pay') }}</div>
                <div class='radio_employments'>
                    <div class="radio" v-for="(pay,index) in pays" :key="index">
                        <input @change="payChange(pay.type)" name="pay" :value="pay.type" :id="'emp'+pay.type+'_'+index"
                               type="radio">
                        <label :for="'emp'+pay.type+'_'+index" class="radio-label">{{ pay.title }}</label>
                    </div>
                </div>
                <div class="backWrapPay">
                    <button  :disabled="disabled"  @click.prevent="pay" class="btn btn-orange">{{$t('message.pay')}}</button>
                    <button class="btn btn-green" @click.prevent="back" >{{$t('back')}}</button>
                </div>

            </div>

        </div>

        <div class="pdfWrap" v-show="isPdf">
            <a :href="qrLink"  target="_blank">{{$t('online_bank')}}</a>
            <div class="buttonBlock">
                <button @click="printPDF">
                    <i class="fas fa-print"></i>
                </button>
                <button @click="downloadPDF">
                    <i class="fas fa-download"></i>
                </button>
            </div>
            <canvas ref="pdfCanvas"></canvas>
        </div>
    </div>
</template>

<script>
import Loading from "vue-loading-overlay";
import {mapState, mapActions, mapGetters} from 'vuex';
export default {
    name: "SelectPay",
    components: {Loading},
    data() {
        return {
            sendPay: false,
            disabled: true,

            isPdf:false,
            pdf:null,

            isLoading: false,
            fullPage: true,
        }
    },
    computed: {
        ...mapState({
            address: (state) => state.address,
            phone_number: (state) => state.phone_number,
            phone: (state) => state.phone,

            bookings: (state) => state.bookings,
            pays: (state) => state.pays,
            type_pay: (state) => state.type_pay,

            pdf_url: (state) => state.pdf_url,
            pdf_name: (state) => state.pdf_name,
            qrLink: (state) => state.qrLink,
        })
    },

    methods: {
        payChange(type) {
            this.disabled=false;
            this.$store.commit('setPay', type);
            this.sendPay = true;
        },
        pay() {
            this.isLoading = true;
            if(this.type_pay=='wayforpay'){
                this.$store.dispatch('payBooking').then(() => {
                    this.$store.commit('stepGlobalSet', 'way');
                });
                return;
            }
            this.$store.dispatch('payBooking').then(() => {
                setTimeout(()=>{
                    this.$store.dispatch('getCheck').then(() => {
                        setTimeout(()=>{
                            this.$store.dispatch('getCheckLocal').then((data) => {
                                setTimeout(()=>{
                                    this.isPdf=true;
                                    this.renderPDF();
                                    this.isLoading = false;
                                },1500)
                            });

                        },1500)
                    });
                },1500);
            })
        },
        async renderPDF() {
            const loadingTask = pdfjsLib.getDocument(this.pdf_url);
            const pdf = await loadingTask.promise;
            const page = await pdf.getPage(1);

            const canvas = this.$refs.pdfCanvas;
            const context = canvas.getContext("2d");
            const viewport = page.getViewport({scale: 1.5});

            canvas.width = viewport.width;
            canvas.height = viewport.height;

            const renderContext = {
                canvasContext: context,
                viewport: viewport,
            };
            await page.render(renderContext).promise;
            this.pdf = pdf;

        },
        printPDF() {
            if (this.pdf) {
                const iframe = document.createElement("iframe");
                iframe.style.display = "none";
                iframe.src = this.pdf_url;
                document.body.appendChild(iframe);

                iframe.onload = function () {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                };
            }
        },
        downloadPDF() {
            const link = document.createElement("a");
            link.href = this.pdf_url;
            link.download = this.pdf_name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        back(){
            this.$store.commit('stepGlobalSet', 'action_select');
        },

    }
}
</script>

