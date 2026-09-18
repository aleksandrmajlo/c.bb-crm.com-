<template>
    <div  class="reserv reserv_center" v-cloak>
        <div class="booking">

            <div style="margin-bottom: 1rem;text-align: center">
                <button  style="min-width: 250px;"  @click.prevent="setStep('sports_days')" class="btn btn-orange">{{$t('add_new_bookikg')}}</button>
            </div>

            <div style="margin-bottom: 1rem;text-align: center">
                <button style="min-width: 250px;"  @click.prevent="setStep('my_bookings')" class="btn btn-orange">{{$t('my_booking')}}</button>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: "ActionSelect",
    methods:{
        setStep(step){
            this.$store.commit('stepGlobalSet', step);
        }
    }
}
</script>


