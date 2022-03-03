<style>
    .doc-wrapper{
        margin-bottom: 20px;
    }
    .doc-wrapper .doc{
        border: 1px solid #cccccc;
    }
    .doc-wrapper .doc .doc-body{
        height:100px;
        overflow: hidden;
    }
    .doc-wrapper .doc .doc-foot{
        border-top: 1px solid #cccccc;
        height: 40px;
        overflow: hidden;
        background-color: #eeeeee;
        padding: 5px;
    }
    .doc-wrapper .doc .doc-foot > input{
        width: 30px;
    }
    .doc-wrapper .doc .doc-foot label{
        white-space: nowrap;
        overflow: hidden;
        height: 20px;
        margin-bottom: 0;
    }
    .doc-wrapper .doc .doc-foot span{
        background-color: #fff;
    }
</style>

<template lang="pug">
    div.col-2.doc-wrapper
        div.doc
            div.doc-body
                template(v-if="extension(doc.url) == '.jpg' || extension(doc.url) == '.jpeg' || extension(doc.url) == '.png'")
                    img(v-bind:src="doc.url" style="width:100%")
                template(v-else)
                    pdf(v-bind:src="doc.url" ref="mypdf" style="width:100%")
            div.doc-foot
                input(type="checkbox" v-model="doc.checked")
                input.mr-2(type="number" v-model="doc.count" min="0")
                a(v-bind:href="doc.url") {{ doc.name }}
</template>

<script>
export default {
    data: function() {
        return {
            doc: this.document,
        }
    },
    props: ['document'],
    methods: {
        extension: function(str){
            return str.substring(str.lastIndexOf('.'));
        },
    },
}
</script>