 <template lang="pug">
    div
        card(v-if="client_contact" :contact="client_contact")
        div.row.clearfix
            div.col-12.mb-3.col-xl-12
                workers(v-if="estimate" :estimate="estimate" :group_id="$root.user.group_id")
            div.col-12.mb-3.col-xl-12
                material_consumption(v-if="estimate" :estimate="estimate" :group_id="$root.user.group_id")
</template>

<script>
import card from './client_card.vue';
import workers from './estimate_group_workers.vue';
import material_consumption from './estimate_group_material_consumption.vue';

export default {
    props: ['id'],
    data(){
        return {
            estimate_group: null,
            client_contact: null,
            estimate: null
        }
    },
    components: { card, workers, material_consumption },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.My_works');
        this.getResults();
    },
    methods: {
        getResults() {
            this.$http.get('/api/estimate_groups/' + this.id ).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    if (data.estimate !== null && data.estimate.service !== null && data.estimate.service.client_contact !== null){
                        this.client_contact = data.estimate.service.client_contact;
                    }
                    if (data.estimate !== null){
                        this.estimate = data.estimate;
                    }
                    this.estimate_group = data;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
    },
    watch: {
    },
}
</script>