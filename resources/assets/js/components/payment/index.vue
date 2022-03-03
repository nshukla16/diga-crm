<style>
span.inactive {
  color: grey;
  text-decoration: line-through;
}

.big-checkbox {
  width: 20px;
  height: 20px;
  margin-left: 0px;
  position: relative;
  vertical-align: middle;
  margin-top: 0;
  margin-right: 10px;
}
</style>

<template lang="pug">
.diga-container.p-4
  .row
    .col-md-9
      h3 {{ $t('template.Choose_your_modules') }}
      .row
        .col-md-3(v-for="mod in modules")
          .card.bg-light.mb-3 
            .card-header 
              input.form-check-input.big-checkbox(
                type="checkbox",
                v-model="mod.isSelected"
              )
              span {{ $t('template.module-' + mod.name) }}
              span.float-right {{ mod.price }} {{ format_currency(globalSettings.settings.price_currency) }} {{ $t('template.Per_module_per_month') }}
            .card-body
              .card-text
                p {{ $t('template.module-' + mod.name + '-desc') }}
                //- div.class.alert.alert-warning.p-3.mb-0(v-if="mod.trial_date_start", role="alert")
                //-     span(:class="{ inactive: isBiggerThanNow(mod.trial_date_end) }") {{$t('template.trial_period')}}: {{dateFormat(mod.trial_date_start)}} - {{dateFormat(mod.trial_date_end)}}
                //- div.class.alert.alert-success.p-3.mb-0(v-if="mod.current_subscription_date_start", role="alert")
                //-     span(:class="{ inactive: isBiggerThanNow(mod.current_subscription_date_end) }") {{$t('template.subscription_period')}}: {{dateFormat(mod.current_subscription_date_start)}} - {{dateFormat(mod.current_subscription_date_end)}}
    .col-md-3.text-center
      div
        h3 {{ $t('template.Choose_number_of_workers') }}
        .input-group.mb-3
          input.form-control(
            type="number",
            min="1",
            onkeypress="return event.charCode >= 48",
            style="text-align: right",
            v-model="numberOfWorkers"
          )
          .input-group-append
            span.input-group-text {{ globalSettings.settings.price_per_user }} {{ format_currency(globalSettings.settings.price_currency) }} {{ $t('template.Per_user_per_month') }}
      .btn-toolbar(
        style="margin-left: auto; margin-right: auto; display: block"
      )
        .btn-group.mr-2(
          role="group",
          aria-label="First group",
          style="width: 100%; display: grid; grid-template-columns: 25% 25% 25% 25%"
        )
          button.btn.btn-secondary(
            v-on:click="changeNumberOfMonths(1)",
            :class="{ active: numberOfMonths === 1 }",
            type="button"
          ) 1 {{ $t('template.month_short') }}
          button.btn.btn-secondary(
            v-on:click="changeNumberOfMonths(3)",
            :class="{ active: numberOfMonths === 3 }",
            type="button"
          ) 3 {{ $t('template.month_short') }}
          button.btn.btn-secondary(
            v-on:click="changeNumberOfMonths(6)",
            :class="{ active: numberOfMonths === 6 }",
            type="button"
          ) 6 {{ $t('template.month_short') }}
          button.btn.btn-secondary(
            v-on:click="changeNumberOfMonths(12)",
            :class="{ active: numberOfMonths === 12 }",
            type="button"
          ) 12 {{ $t('template.month_short') }}
      .card(style="margin-left: auto; margin-right: auto; display: block") 
        ul.list-group.list-group-flush
          li.list-group-item {{ numberOfWorkers }} {{ $t('template.workers') }} * {{ numberOfMonths }} {{ $t('template.month_short') }} * {{ globalSettings.settings.price_per_user }} {{ format_currency(globalSettings.settings.price_currency) }} = {{ priceForUsers }} {{ format_currency(globalSettings.settings.price_currency) }}
          li.list-group-item {{ modules.filter((m) => m.isSelected == true).length }} {{ $t('template.modules') }} * {{ numberOfMonths }} {{ $t('template.month_short') }} = {{ priceForModules }} {{ format_currency(globalSettings.settings.price_currency) }}
          li.list-group-item {{ $t('template.discount') }} {{ discountPercent }}% = {{ discount }} {{ format_currency(globalSettings.settings.price_currency) }}
          li.list-group-item {{ $t('template.Your_current_balance') }} = {{ globalSettings.settings.company_balance }} {{ format_currency(globalSettings.settings.price_currency) }}
          li.list-group-item {{ $t('template.previous_period_recalculation') }} = {{ previousSubscriptionsDiscount }} {{ format_currency(globalSettings.settings.price_currency) }}
          li.list-group-item {{ $t('template.total_sum') }} =
            b(v-if="totalSum > 0") {{ totalSum }} {{ format_currency(globalSettings.settings.price_currency) }}
            b(v-if="totalSum <= 0") 0 {{ format_currency(globalSettings.settings.price_currency) }}
          li.list-group-item(v-if="totalSum <= 0 && toBalance > 0") {{ $t('template.amount_to_balance') }} = {{ toBalance }} {{ format_currency(globalSettings.settings.price_currency) }}
          li.list-group-item(v-if="totalSum <= 0 && toBalance <= 0") {{ $t('template.amount_from_balance') }} = {{ toBalance }} {{ format_currency(globalSettings.settings.price_currency) }}
      div(v-if="totalSum > 0")
        .form-check(style="margin-top: 20px")
          input.form-check-input(
            v-model="paymentMethod",
            type="radio",
            value="payment_system",
            checked=""
          )
          label.form-check-label {{ $t('template.payment_system') }}
        .form-check
          input.form-check-input(
            v-model="paymentMethod",
            type="radio",
            value="invoice"
          )
          label.form-check-label {{ $t('template.invoice') }}

      //- button.btn-block.btn.btn-default(style="margin-top: 20px;", v-on:click="startTrial()") {{$t('template.Start_trial')}}
      button.btn-block.btn.btn-diga(
        :disabled="!modules.filter((m) => m.isSelected == true).length > 0 || numberOfWorkers < 1",
        style="margin-top: 20px",
        v-on:click="pay()"
      ) {{ $t('template.Make_payment') }}
  .text-center
    router-link.btn.btn-diga(:to="{ name: 'subscriptions' }") {{ $t('template.Cancel') }}

  #trialActivatedModal.modal.fade(
    tabindex="-1",
    role="dialog",
    aria-hidden="true"
  )
    .modal-dialog(role="document")
      .modal-content
        .modal-body(
          v-if="trialActivatedModules == null || trialActivatedModules.length == 0"
        ) {{ $t('template.no_module_activated') }}
        .modal-body(v-if="trialActivatedModules.length > 0") {{ $t('template.modules_activated') }}:
          ul
            li(v-for="mod in trialActivatedModules") {{ mod.name }}
        .modal-footer
          button.btn.btn-diga(v-on:click="closeModal()") {{ $t('template.btn_close') }}
  #braintreeModal.modal.fade(tabindex="-1", role="dialog", aria-hidden="true")
    .modal-dialog(role="document")
      .modal-content
        .modal-header 
          h5.modal-title {{ $t('template.braintree_title') }}
        .modal-body
          div(v-if="brainTreePaymentSucceded === false")
            p {{ $t('template.you_are_paying_braintree') }} {{  }}
              b {{ totalSum }} {{ format_currency(globalSettings.settings.price_currency) }}
            .alert.alert-danger(v-if="brainTreePaymentFailed === true") {{ $t('template.braintree_payment_failed') }}
            //- div#dropin-container
            button#pay-button.btn.btn-diga {{ $t('template.Make_payment') }}
          div(v-if="brainTreePaymentSucceded === true")
            p {{ $t('template.braintree_payment_succeded') }}
          button.btn.btn-diga(
            v-if="brainTreePaymentSucceded",
            v-on:click="closeModalBrainTree()"
          ) {{ $t('template.btn_close') }}
  #toBalanceModal.modal.fade(tabindex="-1", role="dialog", aria-hidden="true")
    .modal-dialog(role="document")
      .modal-content
        .modal-body {{ $t('template.paid_from_balance') }}
        .modal-footer
          button.btn.btn-diga(v-on:click="closeModalBalance()") {{ $t('template.btn_close') }}
  #invoiceModal.modal.fade(tabindex="-1", role="dialog", aria-hidden="true")
    .modal-dialog(role="document")
      .modal-content
        .modal-header 
          h5.modal-title(v-if="invoiceDownloadedSuccessfully !== true") {{ $t('template.Invoice_fill_fields') }}
          h5.modal-title(v-if="invoiceDownloadedSuccessfully === true") {{ $t('template.Thanks') }}
        .modal-body
          .form(v-if="invoiceDownloadedSuccessfully !== true")
            .form-group(
              :class="{ 'has-error': errors.has('invoiceCompanyName') }"
            )
              label {{ $t('template.Company_name') }}
              input.form-control(
                v-model="invoiceCompanyName",
                name="invoiceCompanyName",
                v-validate="'required'"
              )

            .form-group(:class="{ 'has-error': errors.has('invoiceCountry') }")
              label {{ $t('template.Country') }}
              input.form-control(
                v-model="invoiceCountry",
                name="invoiceCountry",
                v-validate="'required'"
              )

            .form-group(:class="{ 'has-error': errors.has('invoiceCity') }")
              label {{ $t('template.City') }}
              input.form-control(
                v-model="invoiceCity",
                name="invoiceCity",
                v-validate="'required'"
              )

            .form-group(:class="{ 'has-error': errors.has('invoiceAddress') }")
              label {{ $t('template.Address') }}
              input.form-control(
                v-model="invoiceAddress",
                name="invoiceAddress",
                v-validate="'required'"
              )

            .form-group(
              :class="{ 'has-error': errors.has('invoicePostCode') }"
            )
              label {{ $t('template.Postcode') }}
              input.form-control(
                v-model="invoicePostCode",
                name="invoicePostCode",
                v-validate="'required'"
              )

          div(v-if="invoiceDownloadedSuccessfully === true")
            p {{ $t('template.invoice_download_succeded') }}
          button.btn.btn-diga(
            v-if="invoiceDownloadedSuccessfully !== true",
            v-on:click="downloadInvoice()"
          ) {{ $t('template.Download_invoice') }}
          button.btn.btn-diga(
            v-if="invoiceDownloadedSuccessfully === true",
            v-on:click="closeModalInvoice()"
          ) {{ $t('template.btn_close') }}
</template>

<script>
import { mapGetters } from "vuex";
import moment from "moment";
// import braintree from 'braintree-web-drop-in';

export default {
  data() {
    return {
      modules: [],
      payments: [],
      numberOfMonths: 1,
      numberOfWorkers: 1,
      paymentMethod: "payment_system",
      trialActivatedModules: [],
      brainTreeToken: "",
      braintreeDropin: null,
      brainTreePaymentSucceded: false,
      brainTreePaymentFailed: false,
      invoiceCompanyName: "",
      invoiceCountry: "",
      invoiceCity: "",
      invoiceAddress: "",
      invoicePostCode: "",
      invoiceDownloadedSuccessfully: false,
    };
  },
  props: [],
  computed: {},
  methods: {
    format_currency(currency) {
      return {
        eur: "€",
        rub: "₽",
      }[currency];
    },
    getModules() {
      this.$http.get("/api/modules").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.modules = res.data;
          }
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    getBrainTreeToken() {
      this.$http.get("/api/braintree/token").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.brainTreeToken = res.data;
          }
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    getPayments() {
      this.$http.get("/api/payments").then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.payments = res.data;
          }
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    changeNumberOfMonths(number) {
      this.numberOfMonths = number;
    },
    dateFormat(datetime) {
      return moment(datetime).format("D MMM YYYY");
    },
    isBiggerThanNow(datetime) {
      return moment() >= moment(datetime);
    },
    startTrial() {
      this.$http
        .post("/api/subscriptions/start_trial", {
          modules: this.modules.filter((m) => m.isSelected == true),
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.trialActivatedModules = res.data.data;
              this.showModal();
              this.getModules();
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
    },
    pay() {
      if (this.totalSum > 0) {
        if (this.paymentMethod === "payment_system") {
          var button = document.querySelector("#pay-button");

          this.brainTreePaymentSucceded = false;
          this.brainTreePaymentFailed = false;

          if (!this.braintreeDropin) {
            braintree.create(
              {
                authorization: this.brainTreeToken,
                container: "#dropin-container",
              },
              function (createErr, instance) {
                this.braintreeDropin = instance;

                button.addEventListener(
                  "click",
                  function () {
                    instance.requestPaymentMethod(
                      function (err, payload) {
                        this.brainTreePaymentFailed = false;
                        this.$http
                          .post("/api/braintree/nonce", {
                            nonce: payload.nonce,
                            amount: this.totalSum,
                            modules: this.modules.filter(
                              (m) => m.isSelected == true
                            ),
                            numberOfWorkers: this.numberOfWorkers,
                            numberOfMonths: this.numberOfMonths,
                          })
                          .then(
                            (res) => {
                              if (res.data.errcode == 1) {
                                this.brainTreePaymentFailed = true;
                                instance.clearSelectedPaymentMethod();
                                // this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                              } else {
                                this.brainTreePaymentSucceded = true;
                                this.getModules();
                              }
                            },
                            (res) => {
                              this.brainTreePaymentFailed = true;
                              instance.clearSelectedPaymentMethod();
                              //this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                            }
                          );
                      }.bind(this)
                    );
                  }.bind(this)
                );
              }.bind(this)
            );
          }
          this.showModalBrainTree();
        }
        if (this.paymentMethod === "invoice") {
          this.invoiceDownloadedSuccessfully = false;
          this.showModalInvoice();
        }
      } else {
        this.$root.global_loading = true;
        this.$http
          .post("/api/subscriptions/from_balance", {
            amount: this.totalSum,
            toBalance: this.toBalance,
            modules: this.modules.filter((m) => m.isSelected == true),
            numberOfWorkers: this.numberOfWorkers,
            numberOfMonths: this.numberOfMonths,
          })
          .then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.showModalBalance();
                this.getModules();
                this.$root.global_loading = false;
              }
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Server_error"),
                this.$root.$t("template.Error")
              );
              this.$root.global_loading = false;
            }
          );
      }
    },
    downloadInvoice() {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        }
        this.$root.global_loading = true;
        this.$http
          .post(
            "/api/subscriptions/with_invoice",
            {
              amount: this.totalSum,
              modules: this.modules.filter((m) => m.isSelected == true),
              numberOfWorkers: this.numberOfWorkers,
              numberOfMonths: this.numberOfMonths,
              invoiceCompanyName: this.invoiceCompanyName,
              invoiceCountry: this.invoiceCountry,
              invoiceCity: this.invoiceCity,
              invoiceAddress: this.invoiceAddress,
              invoicePostCode: this.invoicePostCode,
            },
            { responseType: "blob" }
          )
          .then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.forceFileDownload(res);
                this.invoiceDownloadedSuccessfully = true;
              }
              this.$root.global_loading = false;
            },
            (res) => {
              this.$toastr.e(
                this.$root.$t("template.Server_error"),
                this.$root.$t("template.Error")
              );
              this.$root.global_loading = false;
            }
          );
      });
    },
    redirectToSubscriptions() {
      this.$router.push("subscriptions");
    },
    showModal() {
      jQuery("#trialActivatedModal").modal("show");
    },
    closeModal() {
      jQuery("#trialActivatedModal").modal("hide");
    },
    showModalBrainTree() {
      jQuery("#braintreeModal").modal("show");
    },
    closeModalBrainTree() {
      jQuery("#braintreeModal").modal("hide");
      this.redirectToSubscriptions();
    },
    showModalBalance() {
      jQuery("#toBalanceModal").modal("show");
    },
    closeModalBalance() {
      jQuery("#toBalanceModal").modal("hide");
      this.redirectToSubscriptions();
    },
    showModalInvoice() {
      jQuery("#invoiceModal").modal("show");
    },
    closeModalInvoice() {
      jQuery("#invoiceModal").modal("hide");
      this.redirectToSubscriptions();
    },
    forceFileDownload(response) {
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", "invoice.pdf"); //or any other extension
      document.body.appendChild(link);
      link.click();
    },
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("template.Payments");
    this.numberOfWorkers = this.globalSettings.settings.max_users;
    this.getModules();
    this.getBrainTreeToken();
    this.getPayments();
  },
  computed: {
    ...mapGetters({
      globalSettings: "getGlobalSettings",
    }),
    priceForUsers() {
      return (
        this.numberOfWorkers *
        this.numberOfMonths *
        this.globalSettings.settings.price_per_user
      );
    },
    priceForModules() {
      let selectedModules = this.modules.filter((m) => m.isSelected == true);
      let price = 0;
      if (selectedModules.length > 0) {
        price = selectedModules
          .map((m) => m.price)
          .reduce(function (previousValue, currentValue, index, array) {
            return previousValue + currentValue;
          });
      }
      return Math.round(price * this.numberOfMonths);
    },
    discountPercent() {
      switch (this.numberOfMonths) {
        default:
          return 0;
          break;
        case 3:
          return 3;
          break;
        case 6:
          return 6;
          break;
        case 12:
          return 10;
          break;
      }
    },
    discount() {
      var sum = this.priceForModules + this.priceForUsers;
      var discountAmount = sum * this.discountPercent;
      if (discountAmount > 0) {
        return Math.round(discountAmount / 100);
      }
      return 0;
    },
    previousSubscriptionsDiscount() {
      var activeModules = this.modules.filter(
        (m) =>
          m.current_subscription_date_end &&
          !this.isBiggerThanNow(m.current_subscription_date_end)
      );
      var sum = 0.0;

      if (activeModules.length == 0) {
        return 0;
      }

      if (this.payments.length == 0) {
        return 0;
      }

      var lastSuccessfullPayments = this.payments
        .filter(
          (p) =>
            p.status === "submitted_for_settlement" ||
            p.status === "balance" ||
            p.status === "approved"
        )
        .sort((a, b) => b.created_at - a.created_at);

      if (lastSuccessfullPayments.length === 0) {
        return 0;
      }

      console.log(lastSuccessfullPayments);

      var lastSuccessfullPayment =
        lastSuccessfullPayments[lastSuccessfullPayments.length - 1];

      if (!lastSuccessfullPayment) {
        return 0;
      }
      if (lastSuccessfullPayment.sum <= 0) {
        return 0;
      }

      var any_module = activeModules[0];

      var daysNumber = moment(any_module.current_subscription_date_end).diff(
        moment(any_module.current_subscription_date_start),
        "days"
      );

      var pricePerDay = lastSuccessfullPayment.sum / daysNumber;

      var leftPrice =
        pricePerDay *
        moment(any_module.current_subscription_date_end).diff(moment(), "days");

      return Math.round(leftPrice);
    },
    totalSum() {
      return Math.round(
        this.priceForModules +
          this.priceForUsers -
          this.discount -
          this.globalSettings.settings.company_balance -
          this.previousSubscriptionsDiscount
      );
    },
    toBalance() {
      if (this.previousSubscriptionsDiscount > 0)
        return Math.round(
          this.previousSubscriptionsDiscount -
            (this.priceForModules + this.priceForUsers - this.discount)
        );
      else
        return (
          Math.round(
            this.priceForModules +
              this.priceForUsers -
              this.discount -
              parseFloat(this.globalSettings.settings.company_balance) +
              parseFloat(this.globalSettings.settings.company_balance)
          ) * -1
        );
    },
  },
};
</script>