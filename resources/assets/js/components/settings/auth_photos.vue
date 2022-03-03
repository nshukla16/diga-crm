<style>

</style>

<template lang="pug">
    div
        div.row
            div.col-12
                div.diga-container.p-4
                    h2 {{ $t('template.Auth_photos') }}
                    div.row
                        div.col-2(v-for="auth_photo in auth_photos")
                            img.w-100(:src="auth_photo.url")
                            div {{ auth_photo.name }}
                            div {{ auth_photo.result }}
</template>

<script>
export default {
    data() {
        return {

        }
    },
    props: ['auth_photos'],
    methods: {

    },
}
</script>