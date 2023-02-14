<template>
    <div class="payment" :class="{ 'open-message' : complete }">
        <loader v-if="loading" :disableScrolling="disableScrolling" object="#7cbaf7" color1="#ffffff" color2="#17fd3d" size="5" speed="2" bg="#343a40" objectbg="#999793" opacity="80" disableScrolling="false" name="spinning"></loader>

        <div class="success-modal" :class="{ 'active' : complete }">
            <img src="/assets/images/success-icon.svg" alt="">
            <h1>{{ $t('saved') }}</h1>
            <button @click="close_tab()" class="main-btn blue">{{ $t('close')}}</button>
        </div>
        <header>
            <div class="navigation">
                <a href="#"><img src="/assets/images/ableToPay.svg" alt=""></a>
                <a class="nav-link " href="#" id="navbarDropdownMenuLink1" role="button"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<!--                    <img :src="'/assets/images/'+locale.flag" alt="">-->
                    {{ locale.toUpperCase() }}
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink1">
                    <a @click.prevent="setLocale(k)" v-for="(language, k) in locales" class="dropdown-item" :href="'?lang='+k">{{ language.name }}</a>
                </div>
            </div>
            <div class="header-content">
                <h1>{{ $t('update_payment_details') }}</h1>
<!--                <h4>To make a payment, fill in all the fields.</h4>-->
            </div>
        </header>

        <main>
            <section class="card-details first-section">
                <div class="card-info">
                    <h4>{{ $t('credit_or_debit') }}</h4>
                    <img src="/assets/images/cards.svg" alt="">
                </div>
                <div class="card-form">
                    <!--//// if card already exist -->
                    <div class="existing-info">
                        <div v-if="has_payment_method">
                            <h6>{{ $t('card_brand') }}</h6>
                            <div class="existing-card">
                                <!--                            <img src="/assets/images/visa.svg" alt="">-->
                                <span>
                                {{ customer.pm_card_brand }}
                            </span>
                                <p>**** **** **** {{ customer.pm_last_four }}</p>
                                <img src="/assets/images/checked.svg" alt="">
                            </div>
                            <hr>
                        </div>
                        <form action="" class="">
                            <div class="form-field" :class="{ 'error' : validation_error.card_number.message }">
                                <label for="card_number">{{ $t('card_number') }}</label>
                                <div id="card-number" class="stripe_field"></div>
                                <p class="error-message">{{ validation_error.card_number.message }}
                                    <img src="/assets/images/warning.svg" alt="">
                                </p>
                            </div>
                            <div class="form-field fields">
                                <div class="form-field" :class="{ 'error' : validation_error.card_expiry.message }">
                                    <label for="inputGroupSelect01">{{ $t('expires') }}</label>
                                    <div id="card-expiry" class="stripe_field"></div>
                                    <p class="error-message">{{ validation_error.card_expiry.message }}
                                        <img src="/assets/images/warning.svg" alt="">
                                    </p>
                                </div>
                                <div class="form-field" :class="{ 'error' : validation_error.card_cvc.message }">
                                    <label for="cvc">{{ $t('cvc') }}</label>
                                    <div id="card-cvc" class="stripe_field"></div>
                                    <p class="error-message">{{ validation_error.card_cvc.message }}
                                        <img src="/assets/images/warning.svg" alt="">
                                    </p>
                                </div>
                                <img src="/assets/images/card-types.svg" alt="">
                            </div>
                        </form>
                        <div class="form-field error" v-if="error && (error.code === 'testmode_charges_only' || error.code === 'card_declined' || error.code === 'processing_error' || error.code === 'incorrect_cvc' || error.code === 'expired_card' || error.code === 'incorrect_number')">
                            <p class="error-message without_after">{{ error.message }}
                                <img src="/assets/images/warning.svg" alt="">
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <div class="actions">
                <hr>
                <div class="">
                    <button :class="{ 'btn_disabled' : pay_button_disabled }" @click="save(intent.client_secret)" class="main-btn dark-green" id="card-button">{{ $t('save') }}</button>
                    <button @click="close_tab()" class="main-btn cancel">{{ $t('cancel') }}</button>
                </div>
            </div>
        </main>
        <footer>
            <h4>{{ $t('copyright') }} © 2021 <a href="https://willing-able.com/">DGD German Society for Data Protection GmbH</a>. {{ $t('all_rights_reserved') }}.</h4>
            <h4><a href="https://dg-datenschutz.de/imprint/">{{ $t('imprint') }}</a> | <a href="https://dg-datenschutz.de/privacy-policy/">{{ $t('privacy_policy') }}</a></h4>
            <img src="/assets/images/company-logo.svg" alt="">
        </footer>
    </div>
</template>

<script>
import {mapGetters} from "vuex";
import {loadMessages} from '../plugins/i18n';

export default {
    name: "ManagePayment",
    data() {
        return {
          pay_button_disabled: true,
            loading: true,
            disableScrolling: true,
            complete: false,
            error: null,
            validation_error: {
              card_number: {
                message: null,
                valid: false
              },
              card_expiry: {
                message: null,
                valid: false
              },
              card_cvc: {
                message: null,
                valid: false
              },
            },
            stripe: null,
            cardNumber: null,
          cardExpiry: null,
          cardCvc: null,
            customer: {},
            has_payment_method: false,
            token: this.$route.query.token,
            intent: {}
        }
    },
    created() {
        this.fetch();
        console.log('ekav')
    },
  watch: {
    validation_error: {
      handler() {
        this.checkButtonState();
      },
      deep: true
    }
  },
  computed: mapGetters({
    locales: 'lang/locales',
    locale: 'lang/locale'
  }),
    methods: {
      checkButtonState(){
        if(this.validation_error.card_number.valid && this.validation_error.card_cvc.valid && this.validation_error.card_expiry.valid){
          this.pay_button_disabled = false;
        }
        else{
          this.pay_button_disabled = true;
        }
      },
      async setLocale(locale) {
        if (this.$i18n.locale !== locale) {
          loadMessages(locale);
          await this.$store.dispatch('lang/setLocale', {locale});
        }
      },
        async save(clientSecret){
          if(this.pay_button_disabled) { return true }
            this.error = null;
            this.loading = true;
            const { setupIntent, error } = await this.stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: this.cardNumber,
                    }
                }
            );

            if (error) {
                this.loading = false;
                this.error = error
                console.log(error)
            } else {
                this.loading = true;
                await axios.post(window.ENV.host+'/api/subscriptions/client/manage_payment', {
                    paymentMethodId: setupIntent ? setupIntent.payment_method : null,
                    token: this.token
                })
                    .then(({data}) => {
                        this.complete = true;
                        this.loading = false;
                    })
                    .catch((e) => {
                        this.loading = false;
                        alert('Server error')
                    })
            }
        },
        async fetch() {
            await axios.get(window.ENV.host + '/api/subscriptions/client/manage_payment?token=' + this.token)
                .then(({data}) => {
                    this.customer = data.customer;
                    this.intent = data.intent;
                    this.has_payment_method = data.has_payment_method;

                    this.includeStripe('js.stripe.com/v3/', function () {
                        this.render_stripe();
                    }.bind(this));
                })
                .catch((e) => {
                    if( e.response.status == 401){
                        alert('Unauthorized')
                        //todo abort(401) Unauthorized
                    }
                    this.loading = false;
                });
        },
        includeStripe( URL, callback ){
            let documentTag = document, tag = 'script',
                object = documentTag.createElement(tag),
                scriptTag = documentTag.getElementsByTagName(tag)[0];
            object.src = '//' + URL;
            if (callback) { object.addEventListener('load', function (e) { callback(null, e); }, false); }
            scriptTag.parentNode.insertBefore(object, scriptTag);

            this.loading = false;
        },
        render_stripe(){
            const stripe = Stripe(window.ENV.stripe_key);
            this.stripe = stripe

            const elements = stripe.elements();

            var cardNumber = elements.create('cardNumber');
            cardNumber.mount('#card-number');
            this.cardNumber = cardNumber;

            var cardExpiry = elements.create('cardExpiry');
            cardExpiry.mount('#card-expiry');
            this.cardExpiry = cardExpiry;

            var cardCvc = elements.create('cardCvc');
            cardCvc.mount('#card-cvc');
            this.cardCvc = cardCvc;

            cardNumber.on('change', (event) => {
              this.validation_error.card_number = {};
              if(event.error) {
                this.validation_error.card_number.valid = false;
                this.validation_error.card_number.message = event.error.message;
              }
              else{
                this.validation_error.card_number.valid = !event.empty;
              }

            });
            cardExpiry.on('change', (event) => {
              this.validation_error.card_expiry = {};
              if(event.error) {
                this.validation_error.card_expiry.valid = false;
                this.validation_error.card_expiry.message = event.error.message;
              }
              else{
                this.validation_error.card_expiry.valid = !event.empty;
              }
            });
            cardCvc.on('change', (event) => {
              this.validation_error.card_cvc = {};
              if(event.error) {
                this.validation_error.card_cvc.valid = false;
                this.validation_error.card_cvc.message = event.error.message;
              }
              else{
                this.validation_error.card_cvc.valid = !event.empty;
              }
            });
        },
        close_tab(){
          window.close()
        },
    },
}
</script>

<style scoped>
.error-message.without_after:after{
    content: none;
}

.stripe_field{
    border: 1px solid #C4C4C4;
    box-sizing: border-box;
    border-radius: 4px;
    width: 100%;
    height: 44px;
    padding: 10px 15px;
}

.btn_disabled{
  opacity: 0.5;
  pointer-events: none;
}
</style>
