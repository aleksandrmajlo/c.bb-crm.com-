import { createStore, createLogger } from 'vuex';

const debug = process.env.NODE_ENV !== 'production'
// const debug = false;

const timeValueToMinutes = (value) => {
    if (typeof value === 'string' && value.includes(':')) {
        const [hours, minutes = 0] = value.split(':').map(Number);
        return (hours * 60) + minutes;
    }

    return Math.round(Number(value || 0) * 60);
};

const formatTime = (value) => {
    const totalMinutes = timeValueToMinutes(value);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
};

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
          trainers:[],
          tennis_options_catalog:{},
          times:[],

          date_booking:null,// date active
          week:[],

    
          employment_id:null,
          employment_price_id:'default',
          trainer_id:null,
          trainer_price:0,
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
            state.trainers=datas.trainers || [];
            state.tennis_options_catalog=datas.tennis_options || {};
            state.times=datas.times;

        },

       orderSet(state,datas){
            const uniqueString = Date.now().toString(36) + Math.random().toString(36).substring(2);
            const table = state.tables[datas.table_id];
            const bookingDate = state.week[state.date_booking];
            const employment = state.employments[datas.employment_id];
            const primaryOrder = state.orders.find(order => order.primary);
            const hasTrainerOverride = Object.prototype.hasOwnProperty.call(datas, 'trainer_id');
            const trainerId = hasTrainerOverride ? (datas.trainer_id || null) : state.trainer_id;
            const trainer = state.trainers.find(item => String(item.id) === String(trainerId));
            const trainerPrice = Object.prototype.hasOwnProperty.call(datas, 'trainer_price')
                ? Number(datas.trainer_price || 0)
                : Number(trainer?.tariffs?.[datas.employment_id] ?? state.trainer_price ?? 0);
            const selectedPrice = employment?.price_choices?.find(
                price => String(price.id) === String(state.employment_price_id)
            );
            const durationMinutes = Number(selectedPrice?.minutes || 0);
            const end = durationMinutes
                ? (timeValueToMinutes(datas.time.start) + durationMinutes) / 60
                : datas.time.end;
            const orderTime = {
                ...datas.time,
                end,
                text: `${formatTime(datas.time.start)} - ${formatTime(end)}`,
            };
            state.orders.push({
              date:state.date_booking,
              date_title:bookingDate ? bookingDate.title : state.date_booking,
              date_day:bookingDate ? bookingDate.day : '',
              time:orderTime,
              slot_end:datas.time.end,
              table_id:datas.table_id,
              table_title:table ? table.title : '',
              employment_id:datas.employment_id,
              employment_title:employment ? employment.title : '',
              employment_price_id:state.employment_price_id,
              employment_price_label:primaryOrder?.employment_price_label || '',
              trainer_id:trainerId,
              trainer_title:datas.trainer_title || trainer?.name || '',
              trainer_price:trainerPrice,
              tennis_options:datas.tennis_options,
              option_details:[],
              price_breakdown:null,
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

       replaceItemOrder(state, updatedOrder) {
           const index = state.orders.findIndex(
               order => String(order.booking_id) === String(updatedOrder.booking_id)
           );
           if (index === -1) {
               return;
           }

           // The API returns only fields that are allowed to change. Preserve
           // the original court, date, start slot and browser-only identifiers.
           state.orders[index] = {
               ...state.orders[index],
               ...updatedOrder,
               time: {
                   ...state.orders[index].time,
                   ...(updatedOrder.time || {}),
               },
           };
       },

       employmentOrderSet(state,employment_id){
        state.employment_id=employment_id;
        const employment = state.employments[employment_id];
        state.orders.forEach((el,index )=> {
            state.orders[index].employment_id=employment_id;
            state.orders[index].employment_title=employment ? employment.title : '';
         });
       },

       employmentPriceOrderSet(state,datas){
           state.employment_price_id=datas.id;
           state.orders.forEach((el,index) => {
               state.orders[index].employment_price_id=datas.id;
               state.orders[index].employment_price_label=datas.label || '';
           });
       },

       trainerOrderSet(state,datas){
           state.trainer_id=datas.id || null;
           state.trainer_price=Number(datas.price || 0);
           state.orders.forEach((el,index) => {
               state.orders[index].trainer_id=datas.id || null;
               state.orders[index].trainer_title=datas.title || '';
               state.orders[index].trainer_price=Number(datas.price || 0);
           });
       },

       durationOrderSet(state,minutes){
           state.orders.forEach((order,index) => {
               const startMinutes = timeValueToMinutes(order.time.start);
               const fallbackEnd = order.slot_end === undefined
                   ? order.time.end
                   : order.slot_end;
               const endMinutes = minutes
                   ? startMinutes + Number(minutes)
                   : timeValueToMinutes(fallbackEnd);
               const end = endMinutes / 60;

               state.orders[index].time = {
                   ...order.time,
                   end,
                   text: `${formatTime(order.time.start)} - ${formatTime(end)}`,
               };
           });
       },


       tennis_optionsOrderSet(state,datas){
           state.tennis_options=datas;
           state.orders.forEach((el,index )=> {
              state.orders[index].tennis_options=datas;
           });
       },


       totalSet(state,data){
          if (!data || typeof data !== 'object') {
              state.total=data;
              return;
          }

          state.total=data.total || 0;
          Object.entries(data.orders || {}).forEach(([key, details]) => {
              const order = state.orders.find(item => String(item.uniqueString) === String(key));
              if (!order) {
                  return;
              }

              order.price_breakdown=details;
              order.option_details=details.option_details || [];
              order.trainer_price=Number(details.trainer_unit_price || 0);
              if (details.trainer_name) {
                  order.trainer_title=details.trainer_name;
              }
          });
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
           let bol = false;
           state.orders = state.orders.filter((order) => {
               // A booking without an id exists only in the browser and cannot
               // be checked against the server until it has been saved.
               if (!order.booking_id) {
                   return true;
               }

               const exists = ids.includes(order.booking_id);
               if (!exists) {
                   bol = true;
               }
               return exists;
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
        state.employment_price_id='default';
        state.trainer_id=null;
        state.trainer_price=0;
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
                    commit('totalSet', response.data);
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

         async previewBookingEdit({state}, order) {
             const response = await axios.post(
                 state.apiUrl + "api/getTotalBookings",
                 {
                     route: state.route,
                     orders: [order],
                 },
                 {
                     headers: {
                         'Content-Type': 'application/json',
                         'API-Key': state.apiKey,
                     },
                 }
             );

             return response.data?.orders?.[order.uniqueString] || null;
         },

         async updateBooking({commit, state, dispatch}, order) {
             commit('isAiaxSet', true);
             try {
                 const response = await axios.post(
                     state.apiUrl + "api/updateBooking",
                     {
                         route: state.route,
                         phone: state.phone,
                         booking_id: order.booking_id,
                         order,
                     },
                     {
                         headers: {
                             'Content-Type': 'application/json',
                             'API-Key': state.apiKey,
                         },
                     }
                 );

                 // Keep the translated labels prepared by the browser, but let
                 // every authoritative price and id from the API win.
                 commit('replaceItemOrder', {...order, ...response.data.order});
                 await dispatch('getTotalBookings');
                 return response.data.order;
             } finally {
                 commit('isAiaxSet', false);
             }
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
