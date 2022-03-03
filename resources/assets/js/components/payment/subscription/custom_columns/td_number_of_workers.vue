<template lang="pug">
    div {{json.number_of_workers}}
</template>

<script>

export default {
    props: ['row'],
    methods: {
    },
    computed:{
        json(){
            if (this.row.data){
                return JSON.parse(this.row.data);
            }
            else 
            {
                return null;
            }
        },
    }
}
</script>