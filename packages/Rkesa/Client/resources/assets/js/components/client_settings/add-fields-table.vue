<style>

</style>

<template lang="pug">
    div
        div.table-responsive.mb-2
            table.table
                thead
                    tr
                        th {{ $t('template.Name') }}
                        th {{ $t('template.Type') }}
                        th {{ $t('client.Show_in_card') }}
                        th
                tbody
                    tr(v-for="(field,i) in ffields")
                        td
                            div(:class="{ 'has-error': errors.has(_uid+'-field-'+i) }")
                                input.form-control(:name="_uid+'-field-'+i", v-model="field.name" v-validate="'required'")
                        td
                            select.form-control(v-model="field.type", :disabled="!field.new")
                                option(value="0") {{ $t('client.String') }}
                                option(value="1") {{ $t('client.Text') }}
                                option(value="2") {{ $t('client.Select') }}
                            div.mt-2(v-if="field.type == 2")
                                div.mb-2(v-for="option in field.options")
                                    input.form-control.d-inline-block.mr-2(v-model="option.name" style="width: 200px;vertical-align: middle;")
                                    button.btn(@click="remove_option(field, option)") {{ $t('template.Remove') }}
                                button.btn.btn-diga(@click="add_option(field)") {{ $t('client.Add_option') }}
                        td(style="text-align: center;")
                            input(type="checkbox" v-model="field.show_in_card")
                        td
                            button.btn(@click="remove_field(field)") {{ $t('template.Remove') }}
        button.btn.btn-diga(@click="add_field") {{ $t('client.Add_field') }}
</template>

<script>
export default {
    data() {
        return {
            //
        }
    },
    props: {'ffields': Array},
    inject: ['$validator'],
    methods: {
        add_field() {
            let max_id = 0;
            this.ffields.forEach(function(item){
                if (item.id > max_id){
                    max_id = item.id;
                }
            });
            let field = {
                id: max_id + 1,
                name: '',
                type: 0,
                options: [],
                show_in_card: true,
                new: true,
            };
            this.ffields.push(field);
        },
        remove_field(field){
            let index = this.ffields.indexOf(field);
            this.ffields.splice(index, 1);
        },
        add_option(field) {
            let max_id = 0;
            field.options.forEach(function(item){
                if (item.id > max_id){
                    max_id = item.id;
                }
            });
            let option = {
                name: '',
                id: max_id + 1,
            };
            field.options.push(option);
        },
        remove_option(field, option) {
            let index = field.options.indexOf(option);
            field.options.splice(index, 1);
        },
    },
}
</script>