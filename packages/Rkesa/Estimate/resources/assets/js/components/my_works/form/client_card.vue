<style>
    .pic-bordered img{
        width: 200px;
        float: left;
    }
    .light-link{
        color: #bebebe;
    }
</style>

<template lang="pug">
    #contact_info.mb-3
        .diga-container
            div(style="overflow: hidden;")
                .info-top
                    div.caption.float-left
                        router-link#current_client_name.contacts-list.light-link.active(:to="{name: 'contact_show', params: {id: contact.id}}" v-text="$root.fullName(contact)")
                    div.float-right(style="font-size: 16px;")
                        router-link.color2-text.color2-border.envelope-button(v-if="$root.enable_companies && contact.client_id", :to="{name: 'company_show', params: {id: contact.client_id}}" style="padding: 3px 10px;") {{ $t("client.All_contacts") }}
                        router-link.btn.btn-circle.btn-default-1(v-if="contact.can_be_updated", :to="{name: 'contact_edit', params: {id: contact.id}}")
                            i.fa.fa-pencil
                    div.clearfix
                .info-bottom
                    div.row
                        div.col-12.col-lg-6(v-for="i in 2")
                            div.d-flex(v-for="attr in all_attributes.slice((i - 1) * attr_chunk_count, i * attr_chunk_count)")
                                span.text-muted {{ attr.name }}:
                                span.dotter
                                span.text-right(v-if="getAttrType(attr) == 'phone'")
                                    common_phone_number(v-for="(phone, index) in attr.value", :key="phone.id", :number="phone.phone_number", :isLast="index === (attr.value.length-1)")
                                span.text-right(v-else) {{ attr.value }}
</template>

<script>
import moment from 'moment';
import common_phone_number from '@/components/callable_phone'
import {mapGetters} from "vuex";

export default {
    props: ['contact'],
    components: {
        common_phone_number,
    },
    data() {
        return {
            my_contact: this.contact,
        }
    },
    mounted(){
    },
    methods: {
        getPersonType() {
            return ['', this.$root.$t('client.Legal_entity'), this.$root.$t('client.Individual')][this.card.contact_type]
        },
        getPhones(){
            return this.contact.client_contact_phones;
        },
        getSex(){
            return this.my_contact.sex == 1 ? this.$root.$t("client.Man") : this.$root.$t("client.Woman")
        },
        created() {
            return moment(new Date(this.created_at)).format('DD.MM.YYYY HH:mm');
        },
        getAttrType(attribute) {
            let type = 'all'
            if (typeof attribute.type != 'undefined' && attribute.type == 'phone') type = 'phone'
            return type;
        },
    },
    computed: {
        ...mapGetters({
            usersById: 'getUsersById',
        }),
        attr_chunk_count(){
            return Math.ceil(this.all_attributes.length / 2);
        },
        all_attributes(){
            let attrs = [];
            attrs.push({'name': this.$root.$t("client.Full_Name"), 'value': this.$root.fullName(this.my_contact)});
            if (this.my_contact.surname != '' && this.my_contact.surname != null) {
                attrs.push({'name': this.$root.$t("client.Surname"), 'value': this.my_contact.surname});
            }
            if (this.my_contact.sex) {
                attrs.push({'name': this.$root.$t("client.Sex"), 'value': this.getSex()});
            }
            if (this.getPhones() != '') {
                attrs.push({'name': this.$root.$t("client.Phones"), 'value': this.getPhones(), 'type': 'phone'});
            }
            if (this.my_contact.client_contact_emails.length > 0) {
                attrs.push({'name': "E-mail", 'value': this.my_contact.client_contact_emails.map(e => e.email).join(', ')});
            }
            if (this.my_contact.nif) {
                attrs.push({'name': this.$root.$t("client.NIF"), 'value': this.my_contact.nif});
            }
            if (this.my_contact.profession != '') {
                attrs.push({'name': this.$root.$t("client.Profession"), 'value': this.my_contact.profession});
            }
            attrs.push({'name': this.$root.$t("client.DATA_DO_PRIMEIRO_CONTACTO"), 'value': this.my_contact.created_at});
            if (this.my_contact.client_referrer) {
                attrs.push({'name': this.$root.$t("client.Referrer"), 'value': this.my_contact.client_referrer.title});
            }
            if (this.my_contact.referrer_note != "") {
                attrs.push({'name': this.$root.$t("client.Referrer_note"), 'value': this.my_contact.referrer_note});
            }
            if (this.my_contact.responsible_user_id != null) {
                attrs.push({'name': this.$root.$t("client.Responsible"), 'value': this.usersById[this.my_contact.responsible_user_id].name});
            }
            for (var pr in this.my_contact.attributes_calculated) {
                var field = this.my_contact.attributes_calculated[pr];
                if (field.show_in_card) {
                    attrs.push({'name': field.name, 'value': field.value_calculated});
                }
            }
            return attrs;
        },
    },
}
</script>