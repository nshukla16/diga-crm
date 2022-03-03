<style>

</style>

<template lang="pug">
    div
        div.row
            div.col-12
                div.diga-container.p-4
                    h2 Test
                    form(action="/api/login_with_photo" method="POST" enctype="multipart/form-data")
                        input(type="file" name="image")
                        button(type="submit") kek
</template>

<script>
export default {
    data() {
        return {

        }
    },
    props: [],
    methods: {

    },
}
</script>