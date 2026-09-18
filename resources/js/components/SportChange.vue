<template>

    <div class="wrapper">
        <div class="container-advertisment">
            <div class="all-slect-table flex-center">
                <loading v-model:active="isLoading" :is-full-page="fullPage"/>
                <div class="change-table">
                    <div class="title-h3 bottom-m">{{ $t('message.sport_select') }}</div>
                    <ul class="types">
                        <template v-for="(sport,index) in sports" :key="index">
                            <li class="de">
                                <a @click.prevent="collapse(index)" href="#">{{ sport.title }}</a>
                            </li>
                            <li class=" wrapRadio " :class="step==index ? 'show' : 'd-none'" :id="'#collapse_'+index">
                                <div class="collapse">
                                    <div class="form-check me-2" v-for="(employment,ind)  in sport.employments" :key="ind">
                                        <input class="form-check-input" type="radio" name="employment_id"
                                               v-model="employment_id" :value="sport.id+'_'+employment.id"
                                               :id="'employment_'+index+'_'+ind">
                                        <label class="form-check-label" :for="'employment_'+index+'_'+ind">
                                            {{ employment.title }}
                                        </label>
                                    </div>
                                </div>
                            </li>
                        </template>

                    </ul>
                </div>
            </div>

        </div>
    </div>


</template>

<script>
import {mapState, mapActions} from 'vuex';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';

export default {
    name: "SportChange",
    components: {
        Loading,
    },
    computed: {
        ...mapState({
            sports: (state) => state.sports,
        })
    },
    data() {
        return {
            step: null,
            employment_id: null,
            isLoading: false,
            fullPage: false,
        }
    },
    watch: {
        employment_id(newV, oldV) {
            this.isLoading = true;
            this.$store.commit('tableId_employmentIdSet', newV);
            this.$store.dispatch('getBookingsDateTime').then(() => {
                this.isLoading = false;
                this.$store.commit('stepGlobalSet', 2);
            })
        },
    },
    methods: {
        collapse(index) {
            this.step = index;
        }
    }

}
</script>

