<template lang="pug">
    audio-player(v-if="row.record_link", :file="row.record_link")
</template>

<script>
import audioPlayer from '@/components/audio_player.vue';
export default {
    components: {
        'audio-player': audioPlayer,
    },
    props: ['row'],
}
</script>