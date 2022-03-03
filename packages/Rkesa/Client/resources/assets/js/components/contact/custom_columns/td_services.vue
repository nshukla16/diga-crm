<template lang="pug">
    div(v-html="get_estimate_numbers_with_states(row)")
</template>

<script>
export default {
    props: ['row'],
    methods: {
        get_estimate_numbers_with_states(contact){
            // array of arrays to array - [].concat.apply([], array)
            let $this = this;
            let arr = [];
            contact.services.forEach(function(service){
                if (service.estimates.length !== 0){
                    service.estimates.forEach(function(estimate){
                        arr.push($this.$root.estimate_number(estimate) + $this.$root.get_visual_state(estimate.service));
                    });
                } else {
                    arr.push($this.$root.service_number(service) + $this.$root.get_visual_state(service));
                }
            });
            return arr.join(', ');
        },
    },
}
</script>