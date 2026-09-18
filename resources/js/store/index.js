import { createStore, createLogger } from 'vuex';

const debug = process.env.NODE_ENV !== 'production'
// const debug = false;

export default createStore({
    state () {
        return {

          /*
          * setting club 
          */
          address:null,  
          phone_number:null, 
          working_mode:null,
          working_mode_text:null,

        //   stepGlobal:'login',
            stepGlobal:'sports_days',
        //   stepGlobal:'my_bookings',
        //   stepGlobal:'action_select',
        //   stepGlobal:'result',
        //   stepGlobal:'employment_options',
        //   stepGlobal:'select_pay',
        //   stepGlobal:'way',


          phone: null,
          first_name:'',
          last_name:"",

          route:route,

          tables:[],
          employments:[],
          times:[],

          date_booking:null,// date active
          week:[],

    
          employment_id:null,
          tennis_options:[],
          orders:[],
          total:0,

          // dop orders
          table_dop_id:null,
          times_dop:[],

          bookings:[],

          isAiax:false,

          type_pay:null,
          pays:[
           
            {
                "type":'payment_invoice',
                "title":'Оплата рахунком'
            },           
            {
                "type":'wayforpay',
                "title":'Оплата картою'
            }
          ],

          pay_id:null,
          pdf_url:null,    
          pdf_url_orig:null,    
          pdf_name:'',   
          qrLink:null, 

          apiUrl: '',
          apiKey: import.meta.env.VITE_APP_API_KEY,
          appName: import.meta.env.VITE_APP_NAME,
          appTest: import.meta.env.VITE_APP_TEST,

        }
    },
    mutations:{

        routeSet(state,route){
            state.route=route;
         },

       settingsSet(state,datas){     
        state.address=datas.address;
        state.phone_number=datas.phone_number;
        state.working_mode=datas.working_mode;
        state.working_mode_text=datas.working_mode_text;
        }, 

        phoneSet(state,phone){
          state.phone=phone;
        }, 

        date_bookingSet(state,date_booking){
            state.date_booking=date_booking;
        },
        
        stepGlobalSet(state,step){
            state.stepGlobal=step;
        },

        tablesBookingsSet(state,datas){
            state.week=datas.week;
            state.date_booking=datas.date_booking;
            state.tables=datas.tables;
            state.employments=datas.employments;
            state.times=datas.times;

        },

       orderSet(state,datas){
            const uniqueString = Date.now().toString(36) + Math.random().toString(36).substring(2);
            state.orders.push({
              date:state.date_booking,
              time:datas.time,
              table_id:datas.table_id,
              employment_id:datas.employment_id,
              tennis_options:datas.tennis_options,
              primary:datas.primary,
              booking_id:datas.booking_id,
              uniqueString:uniqueString,
            })
       },

       orderUpdate(state,ids){       
        state.orders.forEach((el,index) => {
            let uniqueString=el.uniqueString;
            if(typeof ids[uniqueString] !='undefined'){
                state.orders[index].booking_id= ids[uniqueString];  
            }
        });
       },

       removeItemOrder(state, booking_id) {
        state.orders.forEach((el,index )=> {
            if(el.booking_id==booking_id){
                state.orders.splice(index, 1);
            }
         });         
      },

       employmentOrderSet(state,employment_id){
        state.employment_id=employment_id;
        state.orders.forEach((el,index )=> {
            state.orders[index].employment_id=employment_id;
         });
       },


       tennis_optionsOrderSet(state,datas){
           state.tennis_options=datas;
           state.orders.forEach((el,index )=> {
              state.orders[index].tennis_options=datas;
           });
       },


       totalSet(state,total){
          state.total=total;
       },

       table_dop_idSet(state,table_dop_id){
          state.table_dop_id=table_dop_id;
       },

       times_dopSet(state,data){
          state.times_dop=data.times_dop;
       },

       bookingsSet(state,bookings){
          state.bookings=bookings;
       },

       isAiaxSet(state,bol){
          state.isAiax=bol;
       },

       clearOrders(state,ids){
           let bol=false;
           state.orders.forEach((el,index )=> {
             let booking_id=el.booking_id;
             if (ids.indexOf(booking_id) !== -1) {
         
              }  else{
                 bol=true;
                 state.orders.splice(index, 1);
              } 
           });
           if(bol){
              this.dispatch('getBokingsDops').then(() => {
                  this.dispatch('getTotalBookings').then(() => { });
              });
           }
       },

       setPay(state,type){
          state.type_pay=type;
       },

       setPayID(state,pay_id){
          state.pay_id=pay_id;
       },

       setPdfUrlOrig(state,data){
          state.pdf_url_orig=data.pdf_url;
          state.pdf_name=data.pdf_name;
          state.qrLink=data.qrLink;
       },

       setPdfUrl(state,data){
          state.pdf_url=data.pdf_url;
        //   state.pdf_name=data.pdf_name;
       },

       zeroingDatas(state){
        state.date_booking=null;
        state.orders=[];
        state.employment_id=null;
        state.tennis_options=[];
        state.total=0;
        state.table_dop_id=null;
        state.times_dop=null;
        state.type_pay=null;
       },


    },
    actions: {    
        
        async getSettingsClub({ commit,state }){
                    const headers = {
                        'Content-Type': 'application/json',
                        'API-Key': state.apiKey
                    };
                    return  axios.get(state.apiUrl+"api/getSettingsClub", {
                        headers,
                        params: {
                            route: state.route,
                        }
                    })
                    .then(response => {
                        commit('settingsSet', response.data.settings);
                    })
        },


        async getTablesBookings({ commit,state }){
                   commit('isAiaxSet',true);
                    const headers = {
                        'Content-Type': 'application/json',
                        'API-Key': state.apiKey
                    };
                    return axios.get(state.apiUrl+"api/getTablesBookings", {
                        headers,
                        params: {
                             route: state.route,
                             date_booking: state.date_booking,
                        }
                    })
                    .then(response => {
                        commit('tablesBookingsSet', response.data);
                    })
                    .catch(error => { 
                    })
                    .then(()=>{
                        commit('isAiaxSet',false);
                    });
        },

        /*
         * dop shuldes
         */
        getBokingsDops({ commit,state }){
                commit('isAiaxSet',true);
                const headers = {
                    'Content-Type': 'application/json',
                    'API-Key': state.apiKey
                };
                axios.get(state.apiUrl+"api/getBokingsDops", {
                    headers,
                    params: {
                         route: state.route,
                         date_booking: state.date_booking,
                         table_dop_id: state.table_dop_id,

                    }
                })
                .then(response => {
                    commit('times_dopSet', response.data);
                })
                .catch(error => { 
                })
                .then(()=>{
                    console.log('isAiaxSet_false')
                    commit('isAiaxSet',false);
                });

 
       
        },

        /*
         * total Booking
         */
        getTotalBookings({commit,state}){
                commit('isAiaxSet',true);
                return axios.post(state.apiUrl+"api/getTotalBookings", 
                    {
                        route: state.route,
                        orders:state.orders,
        
                    }, 
                    {
                        headers: {
                            //'Content-Type': 'application/json',
                            'API-Key': state.apiKey
                        }
                    }
                )
                .then(response => {
                    commit('totalSet', response.data.total);
                })
                .catch(error => {})
                .then(()=>{
                    commit('isAiaxSet',false);
                });
        },

        /*
         * save Booking
         */
        addBooking({commit,state},data){
                commit('isAiaxSet',true);
                return axios.post(state.apiUrl+"api/addBooking", 
                    {
                        route: state.route,
                        phone:state.phone,
                        orders:state.orders
                    }, 
                    {
                        headers: {
                            'Content-Type': 'application/json',
                            'API-Key': state.apiKey
                        }
                    }
                )
                .then(response => {
                    commit('orderUpdate',response.data.ids)
                })
                .catch(error => {})
                .then(()=>{
                    commit('isAiaxSet',false);
                });

        },

        /*
         * pay Booking
         */
        payBooking({commit,state},data){         
                commit('isAiaxSet',true);
                return axios.post(state.apiUrl+"api/payBooking", 
                    {
                        route: state.route,
                        phone:state.phone,
                        type_pay:state.type_pay,
                        orders:state.orders,
                    }, 
                    {
                        headers: {
                            'Content-Type': 'application/json',
                            'API-Key': state.apiKey
                        }
                    }
                )
                .then(res => {
                    commit('setPayID',res.data.pay_id);
                    commit('zeroingDatas');

                })
                .catch(error => {})
                .then(()=>{
                    commit('isAiaxSet',false);
                });
        }, 
       
        
        /*
         *  check pdf
        */
        getCheck({commit,state},data){
            commit('isAiaxSet',true);
            return axios.post(state.apiUrl+"api/getCheck", 
                {
                    route: state.route,
                    pay_id:state.pay_id
                }, 
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'API-Key': state.apiKey
                    }
                }
            )
            .then(res => {
                if(res.data.success){
                    commit('setPdfUrlOrig',res.data);
                }
            })
            .catch(error => {})
            .then(()=>{ commit('isAiaxSet',false);  });

        },
        
        /*
         *  link pdf local
        */
        getCheckLocal({commit,state},data){
            commit('isAiaxSet',true);
           return axios.post("/pdf_donvald", 
                {
                    pdf_url_orig:state.pdf_url_orig,
                    pdf_name:state.pdf_name
                }
            )
            .then(res => {
                if(res.data.success){
                    commit('setPdfUrl',res.data);
                    resolve(res.data);
                }
            })
            .catch(error => {})
            .then(()=>{ commit('isAiaxSet',false);  });

        },

        /*
         * my bookings
         */
         getBokings({ commit,state }){
                    try {
                        const headers = {
                            'Content-Type': 'application/json',
                            'API-Key': state.apiKey
                        };
                        axios.get(state.apiUrl+"api/bookings", {
                            headers,
                            params: {
                                 route: state.route,
                                 phone: state.phone,
                            }
                        })
                        .then(response => {
                            commit('bookingsSet', response.data.bookings);
                        })
                        .catch(error => {    });
                    }
                    catch (error) {
                    }
        },

        /*
         * save Booking
         */
         removeBooking({commit,state},booking_id){
            commit('isAiaxSet',true);
            axios.post(state.apiUrl+"api/removeBooking", 
                {
                                route: state.route,
                                phone:state.phone,
                                booking_id:booking_id
                
                }, 
                {
                                headers: {
                                    'Content-Type': 'application/json',
                                    'API-Key': state.apiKey
                                }
                            }
                        )
                        .then(response => {
                           commit('removeItemOrder',booking_id)
                        })
                        .catch(error => {})
                        .then(()=>{                      
                            commit('isAiaxSet',false);
                        });
             
         },

        /*
         *  update order
         */
         updateClearOrder({commit,state},booking_id){
               
                        axios.post(state.apiUrl+"api/updateClearOrder", 
                            {
                                route: state.route,
                                phone:state.phone,
                
                            }, 
                            {
                                headers: {
                                    'Content-Type': 'application/json',
                                    'API-Key': state.apiKey
                                }
                            }
                        )
                        .then(response => {
                           commit('clearOrders',response.data.ids)
                        })
                        .catch(error => {})
                        .then(()=>{                      
                         
                        });
             
         },


    },
    getters: {
        isValidOrder: state => {
            let b=true;
            state.orders.forEach(el => {
                if(el.employment_id){

                }else{
                    b=false;
                }
            });
            return b;
          },

          halfDops:state => {
            let half=state.times_dop.length/2;
            return (Math.ceil(half)-1);
          },
    },
    strict: debug,
    plugins: debug ? [createLogger()] : []
  })