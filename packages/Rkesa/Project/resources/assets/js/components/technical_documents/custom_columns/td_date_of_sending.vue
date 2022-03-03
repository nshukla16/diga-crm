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
            if (this.row.sending_date && this.row.sending_date !== '0000-00-00 00:00:00'){
                return moment(this.row.sending_date).format('DD.MM.YYYY');
            }
            else{
                return '';
            }
        }
    },
}
</script>