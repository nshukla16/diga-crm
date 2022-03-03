<template lang="pug">
    div {{finished_at}}
</template>

<script>
import { mapGetters } from 'vuex';
import moment from 'moment';

export default {
    props: ['row'],
    computed: {
        finished_at(){
            if (this.row.finished_at){
                return moment(this.row.finished_at).format('MM.YYYY');
            }
            else{
                return '';
            }
        }
    },
}
</script>