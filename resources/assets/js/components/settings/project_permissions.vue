<style>
    .p_border{
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
    }
    .n_border{
        border-top: none !important;
    }
</style>
<template lang="pug">
    div.table-responsive
        table.table.table-striped.permissions.w-100
            thead
                tr
                    td {{ $t('template.Type') }}
                    td {{ $t('template.Section') }}
                    td {{ $t('template.Creating') }}
                    td {{ $t('template.Reading') }}
                    td {{ $t('template.Updating') }}
                    td {{ $t('template.Deleting') }}
                    td {{ $t('template.Description') }}
            tbody
                tr
                    td(:rowspan="this.count + 2") {{ $t('template.Projects') }}
                    td
                    td(:rowspan="this.count + 2")
                        input(type="radio" v-model="proj_roles.create" name="create", :value="0")
                        span.ml-2 {{ $t('template.Forbidden') }}
                        br
                        input(type="radio" v-model="proj_roles.create" name="create", :value="1")
                        span.ml-2 {{ $t('template.Allowed') }}
                    td
                    td
                    td(:rowspan="this.count + 2")
                        input(type="radio" v-model="proj_roles.delete" name="delete", :value="0")
                        span.ml-2 {{ $t('template.Forbidden') }}
                        br
                        input(type="radio" v-model="proj_roles.delete" name="delete", :value="1")
                        span.ml-2 {{ $t('template.Allowed') }}
                    td
                tr(v-for="i in count")
                    td.p_border {{ $t('template.ProjectPermissionSection_' + i) }}
                    td.p_border
                        input(type="checkbox" v-model="read_values[i - 1]" @change="save_r_values($event, i - 1)")
                        span.ml-2 {{ $t('template.Allowed') }}
                    td.p_border
                        input(type="checkbox" v-model="update_values[i - 1]" @change="save_u_values($event, i - 1)")
                        span.ml-2 {{ $t('template.Allowed') }}
                    td.p_border
</template>

<script>

export default {
    props: {
        user: Object,
    },
    data(){
        return {
            count: 10,
        }
    },
    mounted(){
    },
    methods: {
        save_r_values(event, index){
            this.proj_roles.read = this.arr_to_val(this.array_of_r_values(event.target.checked, index));
        },
        save_u_values(event, index){
            this.proj_roles.update = this.arr_to_val(this.array_of_u_values(event.target.checked, index));
        },
        arr_to_val(arr){
            return parseInt(arr.map(e => e ? '1' : '0').join(''), 2);
        },
        array_of_r_values(new_value, index){
            this.read_values[index] =  new_value;
            return this.read_values;
        },
        array_of_u_values(new_value, index){
            this.update_values[index] =  new_value;
            return this.update_values;
        },
    },
    computed: {
        proj_roles(){
            return this.user.roles.find(r => r.action == 'projects');
        },
        read_values(){
            let tmp = parseInt(this.proj_roles.read, 10).toString(2).split('').map(c => parseInt(c, 2) == 1);
            if (tmp.length != this.count){
                let array_to_add_to_the_beginning = Array(this.count - tmp.length).fill(false);
                tmp = array_to_add_to_the_beginning.concat(tmp);
            }
            return tmp;
        },
        update_values(){
            let tmp = parseInt(this.proj_roles.update, 10).toString(2).split('').map(c => parseInt(c, 2) == 1);
            if (tmp.length != this.count){
                let array_to_add_to_the_beginning = Array(this.count - tmp.length).fill(false);
                tmp = array_to_add_to_the_beginning.concat(tmp);
            }
            return tmp;
        },
    },
}
</script>