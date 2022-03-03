<style>

</style>

<template lang="pug">
div
    h2(v-if="isGroup==='false'") {{userById[id].name}} - {{$t('hr.User_productivity')}}
    h2(v-if="isGroup==='true'") {{groupsById[id].name}} - {{$t('hr.Group_productivity')}}
    section.diga-container.p-4
        table.table.table-bordered.table-striped.table-hover
            thead
                tr  
                    th(rowspan="2") {{$t('hr.KPI_type')}}
                    th(colspan="3", v-for="(eot, index) in activeEndsOfTerm") {{eot.start + ' - ' + eot.end}}
                tr
                    template(v-for="(eot, index) in activeEndsOfTerm")
                        th {{$t('hr.KPI_fact')}}
                        th {{$t('hr.KPI_plan')}}
                        th {{$t('hr.KPI_kpi')}}
            tbody(v-if="kpis")
                tr(v-for="i in kpis[0].length")
                    td {{$t('hr.' + kpis[0][i-1].type.name)}}
                    template(v-for="j in kpis.length")
                        td {{ kpis[j-1][i-1].count_fact }}
                        td {{ kpis[j-1][i-1].plan_amount }}
                        td {{ $root.formatFinanceValue(kpis[j-1][i-1].kpi) }} %
                        
        div.row
            div.col-md-12
                button.btn.btn-rkesa.float-left(v-on:click="changeIndexes('left')") {{'<<'}}
                button.btn.btn-rkesa.float-right(v-on:click="changeIndexes('right')") {{'>>'}}
</template>

<script>
import {mapGetters} from "vuex";
import moment from 'moment';
const colNumber = 5;

export default {
    data: function() {
        return {
            endsOfTerm: [],
            kpis: null,
            fromIndex: 0,
            toIndex: 0
        }
    },
    props: ['id', 'isGroup'],
    methods: {
        getDates() {
            this.$root.global_loading = true;
            this.$http.get('/api/users_and_groups/user/' + this.id).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.calculateEndsOfTerms(data.period.name, data.start_date);
                    this.getKpis(this.activeEndsOfTerm.map(a => a.end));
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },

        getKpis(dates) {
            this.$root.global_loading = true;
            this.$http.post('/api/users_and_groups/user/' + this.id + '/details', {dates: dates} ).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.kpis = data;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },

        calculateEndsOfTerms(periodName, datestart){
             let now = moment();
            let tempDate = moment(datestart);

            while(tempDate < now){
                let item = {'start': tempDate.format('DD.MM.YYYY'), 'end': null};

                switch(periodName){
                    case "week":
                        tempDate = tempDate.add(1, 'weeks');
                        break;
                    case "two_weeks":
                        tempDate = tempDate.add(2, 'weeks');
                        break;
                    case "month":
                        tempDate = tempDate.add(1, 'months');
                        break;
                    case "quarter":
                        tempDate = tempDate.add(1, 'quarters');
                        break;
                    case "year":
                        tempDate = tempDate.add(1, 'years');
                        break;
                }
                
                if (tempDate <= now){
                    item.end = tempDate.format('DD.MM.YYYY');
                }
                else{
                    item.end = now.format('DD.MM.YYYY');
                }
                this.endsOfTerm.push(item);
            }
            if (this.endsOfTerm.length < colNumber){
                this.fromIndex = 0;
            }
            else{
                this.fromIndex = this.endsOfTerm.length - colNumber;
            }            
            this.toIndex = this.endsOfTerm.length - 1;
        },
        changeIndexes(buttonPressed){
            if (buttonPressed === 'left'){
                if (this.fromIndex >= colNumber){
                    this.fromIndex -= colNumber;
                }
                else{
                    this.fromIndex = 0;
                }
                if (this.toIndex > 9){
                    this.toIndex -= colNumber;
                }
                else{
                    this.toIndex = 4;
                }
            }
            else{
                if (this.fromIndex + colNumber < this.endsOfTerm.length - 1){
                    this.fromIndex += colNumber;
                }
                else{
                    if (this.endsOfTerm.length >= colNumber){
                        this.fromIndex = this.endsOfTerm.length - colNumber;
                    }
                    else{
                        this.fromIndex = 0;
                    }
                }
                if (this.toIndex + colNumber <= this.endsOfTerm.length){
                    this.toIndex += colNumber;
                }
                else{
                    this.toIndex = this.endsOfTerm.length - 1;
                }
            }
        }
    },
    computed: {
        activeEndsOfTerm(){
            return this.endsOfTerm.slice(this.fromIndex, this.toIndex+1);
        },
        ...mapGetters({
            userById: 'getUsersById',
            groupsById: 'getGroupsById'
        })
    },
    watch: {
        fromIndex: function (newFromIndex, oldfromIndex) {
            this.getKpis(this.activeEndsOfTerm.map(a => a.end));
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('hr.User_productivity');
        this.getDates();
    }
}
</script>