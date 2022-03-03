<template lang="pug">
    div {{date}}
</template>

<script>
import { mapGetters } from 'vuex';
import moment from 'moment';

export default {
    props: ['row'],
    computed: {
        date(){
            if (this.row.translating_date && this.row.translating_date !== '0000-00-00 00:00:00'){
                return moment(this.row.translating_date).format('DD.MM.YYYY');
            }
            else{
                return '';
            }
        }
    },
}
</script>