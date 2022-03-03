<style>
</style>

<template lang="pug">
div
  .row(v-for="estimate_group in estimate_groups", style="margin-bottom: 20px")
    workers(
      v-if="estimate",
      :estimate="estimate",
      :group_id="estimate_group.group_id"
    )

  .text-center(v-if="estimate === null || estimate_groups.length === 0")
    h3 {{ $t('template.for_calculating_workers') }}
    button.btn.btn-diga.mt-2(
      v-if="$root.can_do('estimates', 'create') != 0",
      v-on:click="open_create_estimate_page"
    ) {{ $t('client.Create_estimate') }}

  .text-center(style="margin-top: 10px")
    router-link.btn.btn-diga(
      v-if="service && service.client_contact_id",
      :to="{ name: this.$root.contact_or_client_show_route(), params: { id: service.client_contact_id } }"
    ) {{ $t('estimate.Open_client_card') }}

//- div
//-     div.modal.fade(:id="'modal-estimate_group_workers_' + this.service.id" tabindex="-1" aria-hidden="true")
//-         div.modal-dialog.modal-lg(role="document")
//-             div.modal-content
//-                 div.modal-header
//-                     h5.modal-title {{ $t("estimate.Mao_de_obra") }}
//-                 div.modal-body
//-                     div.scoll-tree
//-                         div(v-for="estimate_group in estimate_groups")
//-                             workers(v-if="estimate" :estimate="estimate" :group_id="estimate_group.group_id")
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import workers from "../../../../../../../Estimate/resources/assets/js/components/my_works/form/estimate_group_workers.vue";

export default {
  data() {
    return {
      service: null,
      estimate_groups: [],
      estimate: null,
    };
  },
  props: ["id"],
  components: { workers },
  mounted() {
    this.getService();
  },
  methods: {
    load_estimate() {
      this.$http.get("/api/estimates/" + this.service.master_estimate_id).then(
        (res) => {
          this.estimate = res.data;
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    getResults() {
      this.$http
        .get(
          "/api/estimate_group_pay_stages/" + this.service.master_estimate_id
        )
        .then((res) => {
          return res.json();
        })
        .then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.estimate_groups = data.data;
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
    getService() {
      this.$http.get("/api/services/" + this.id).then(
        (res) => {
          this.service = res.data;
          if (this.service.master_estimate_id !== null) {
            this.getResults();
            this.load_estimate();
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
    open_create_estimate_page() {
      this.$router.push({
        name: "estimate_create",
        query: { service_id: this.service.id },
      });
    },
  },
  computed: {},
};
</script>