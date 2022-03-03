<style>
</style>

<template lang="pug">
div(v-if="company")
  //div.alert.alert-danger(v-if="no_tasks" role="alert")
    | {{ $t('client.There_is_no_tasks_in_client') }}
    a.ml-2(v-bind:href="'/clients/' + client.client_id + '/ignore_no_tasks'") {{ $t('client.Ignore') }}
  client(:client="company", @load_company="load_company")
  .diga-container.p-4.mb-3
    h2 {{ $t('client.Contacts') }}
    .table-responsive
      table.table.table-striped.table-bordered.table-advance
        thead
          tr
            th
              i.fa.fa-user-o.mr-2
              | {{ $t('client.Fullname') }}
            th
              i.fa.fa-envelope.mr-2
              | {{ $t('client.Email') }}
            th
              i.fa.fa-phone.mr-2
              | {{ $t('client.Phones') }}
            th
              i.fa.fa-graduation-cap.mr-2
              | {{ $t('client.Profession') }}
            th(
              v-if="$root.can_do('clients', 'update') != 0 || $root.can_do('clients', 'delete') != 0",
              style="width: 260px"
            )
              i.fa.fa-cogs.mr-2
              | {{ $t('client.Actions') }}
        tbody
          tr(v-for="contact in company.client_contacts")
            td
              template(v-if="company.client_contacts.length > 1")
                i.fa.fa-check-circle-o.mr-2(
                  v-if="contact.is_main_contact",
                  v-bind:title="$t('client.Main_contact')"
                )
                template(v-else)
                  i.fa.fa-circle-o.mr-2(
                    v-if="$root.can_do('clients', 'update') == 0"
                  )
                  i.fa.fa-circle-o.clickable.mr-2(
                    v-else,
                    v-bind:title="$t('client.Set_to_main')",
                    v-on:click="setMainContact(contact)"
                  )
              router-link(
                :to="{ name: $root.contact_or_client_show_route(), params: { id: contact.id } }"
              ) {{ $root.fullName(contact) }}
            td(
              v-text="contact.client_contact_emails.map((e) => e.email).join(', ')"
            )
            td
              common_phone_number(
                v-for="(phone, index) in contact.client_contact_phones",
                :key="phone.id",
                :number="phone.phone_number",
                :isLast="index === contact.client_contact_phones.length - 1"
              )
            td {{ contact.profession }}
            td(
              v-if="$root.can_do('clients', 'update') != 0 || $root.can_do('clients', 'delete') != 0"
            )
              router-link.btn.btn-secondary.mr-2(
                v-if="$root.can_do('clients', 'update') != 0",
                :to="{ name: 'contact_edit', params: { id: contact.id } }"
              ) {{ $t('template.Edit') }}
              button.btn.btn-danger(
                v-if="$root.can_do('clients', 'delete') != 0",
                v-on:click="remove_contact(contact.id)"
              ) {{ $t('template.Remove') }}
          tr(v-if="company.client_contacts.length == 0")
            td.text-center(
              :colspan="$root.can_do('clients', 'update') != 0 || $root.can_do('clients', 'delete') != 0 ? 5 : 4"
            )
              | {{ $t('client.No_contacts') }}
    router-link.btn.btn-diga(
      v-if="$root.can_do('clients', 'create') != 0",
      :to="{ name: $root.contact_or_client_create_route(), query: { company_id: company.id } }"
    ) {{ $t('client.Add_contact') }}
  card(
    :events="all_events()",
    :contact="main_contact",
    :company_id="company.id",
    :is_group="company.is_group",
    :history_entities="all_history()",
    :projects="company.projects",
    :equipment="company.equipment",
    :calculations="company.calculations",
    :selected_service="null"
  )
</template>

<script>
import client from "../shared/client.vue";
import card from "../card/main.vue";
import common_phone_number from "@/components/callable_phone";

export default {
  props: ["id"],
  components: {
    client,
    card,
    common_phone_number,
  },
  data: function () {
    return {
      company: null,
      group_services: [],
    };
  },
  methods: {
    load_company: function () {
      let $this = this;
      this.$root.global_loading = true;
      this.$http.get("/api/companies/" + this.id).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            this.$root.global_loading = false;
          } else {
            this.company = res.data;
            // if (this.company.is_group === true) {
            //   this.load_group_services();
            // }
            this.company.client_contacts.forEach(function (client_contact, i) {
              // client_contact.services.forEach(function (service, j) {
              //   $this.company.client_contacts[i].services[
              //     j
              //   ].client_contact = client_contact;
              // });
              client_contact.events.forEach(function (event, j) {
                $this.company.client_contacts[i].events[
                  j
                ].client_contact = client_contact;
              });
            });
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
    },
    load_group_services() {
      this.$root.global_loading = true;
      this.$http
        .get("/api/services/by_company_is_group?client_id=" + this.company.id)
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$root.global_loading = false;
              this.group_services = res.data.rows;
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
    },
    all_services() {
      return [].concat(
        ...this.company.client_contacts.map(function (contact) {
          return contact.services;
        })
      );
    },
    all_events() {
      return [].concat(
        ...this.company.client_contacts.map(function (contact) {
          return contact.events;
        })
      );
    },
    all_history() {
      return [].concat(
        ...this.company.client_contacts.map(function (contact) {
          return contact.client_history;
        })
      );
    },
    setMainContact(contact) {
      this.$http.post("/api/contacts/" + contact.id + "/set_main", {}).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.$toastr.s(
              this.$root.$t("client.Main_contact_updated"),
              this.$root.$t("template.Success")
            );

            this.company.client_contacts.forEach(function (el, index, ar) {
              ar[index].is_main_contact = ar[index].id == contact.id;
            });
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
    remove_contact(id) {
      if (
        confirm(this.$root.$t("client.Are_you_sure_you_want_to_delete_client"))
      ) {
        let url = this.$root.contact_or_client_show(id);
        this.$http.delete("/api" + url).then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("client.Contact_removed"),
                this.$root.$t("template.Success")
              );
              window.location.reload();
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
      }
    },
  },
  computed: {
    main_contact: function () {
      return this.company.client_contacts.filter(function (contact) {
        return contact.is_main_contact;
      })[0];
    },
  },
  mounted() {
    document.title =
      this.$root.global_settings.site_name +
      " | " +
      this.$root.$t("client.Client_card");
    this.load_company();
    // this.$bus.$on('refetch_client', (data) => {
    //     this.load_company()
    // })
  },
  beforeDestroy: function () {
    // this.$bus.$off("refetch_client", this.load_company);
  },
};
</script>
