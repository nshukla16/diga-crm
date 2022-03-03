<style>

</style>

<template lang="pug">
    div(style="font-size: 22px; background-color: #30b8c0; background-size: cover; height: 600px;text-align:center;color: #ffffff;")
        table(style="width: 100%; height: 100%;")
            tr
                td
                    img(src="/img/logo_200.png" style="margin-bottom: 20px;")
                    br
                    span {{ $t('template.Welcome') }}!
                    br
                    span(style="font-size: 40px;") DIGA ERP SYSTEM
                    br
                    span(style="font-size: 19px;") Let business work for you, not you for it.
</template>

<script>
export default {
    props: [],
    data() {
        return {

        }
    },
    mounted(){

    },
    methods: {

    },
}
</script>