<template>
    <div v-cloak>
        <div class="mb-3 text-center btn-group-vertical w-100">
            <a href="#" class="btn btn-outline-primary"
               :class="time_booking.type=='morning' ? 'active' : ''"
               @click.prevent="setStep('morning')">{{ $t('message.morning') }}</a>
            <a href="#" class="btn btn-outline-primary"
               :class="time_booking.type=='day' ? 'active' : ''"
               @click.prevent="setStep('day')">{{ $t('message.day') }}</a>
            <a href="#" class="btn btn-outline-primary"
               :class="time_booking.type=='evening' ? 'active' : ''"
               @click.prevent="setStep('evening')">{{ $t('message.evening') }}</a>
        </div>

        <div v-if="time_booking.type=='morning'" class="btn-group-vertical text-center w-100 p-2">
            <a href="#"
               v-for="(v,key) in morning" :key="key"
               @click.prevent="setTime(v)"
               :class="time_booking.start==v.start ? 'active' : ''"
               class="btn btn-light mb-1">
                {{v.text}}
            </a>
        </div>

        <div v-if="time_booking.type=='day'" class="btn-group-vertical text-center w-100 p-2">
            <a href="#"
               v-for="(v,key) in day" :key="key"
               @click.prevent="setTime(v)"
               :class="time_booking.start==v.start ? 'active' : ''"
               class="btn btn-light mb-1">
                {{v.text}}
            </a>
        </div>

        <div v-if="time_booking.type=='evening'" class="btn-group-vertical text-center w-100 p-2">
            <a href="#"
               v-for="(v,key) in evening" :key="key"
               @click.prevent="setTime(v)"
               :class="time_booking.start==v.start ? 'active' : ''"
               class="btn btn-light mb-1">
                {{v.text}}
            </a>
        </div>

    </div>
</template>

<script>
import {mapState, mapActions} from 'vuex';

export default {
    name: "TimeSelect",
    data() {
        return {
        }
    },
    computed: {
        ...mapState({
            time_booking: (state) => state.time_booking,
            morning: (state) => state.morning,
            day: (state) => state.day,
            evening: (state) => state.evening,

        }),
    },

    methods:{
        setStep(type){
            this.$store.commit('time_bookingSet',{type:type,text:'',start:null,end:null});
        },
        setTime(v){
            v.type=this.time_booking.type;
            this.$store.commit('time_bookingSet',v);
        }

    }

}
</script>

