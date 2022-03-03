<template lang="pug">
div
  .modal-scrollable.global_modal
    .modal.fade.modal-medium.in(
      tabindex="-1",
      aria-hidden="false",
      style="z-index: 10053 !important"
    )
      .modal-content
        table(style="width: 100%; height: 100%")
          tbody
            tr
              td
                .loader
  .modal-backdrop.fade.in(style="z-index: 10052 !important")
</template>

<script>
export default {
  data: function () {
    return {};
  },
};
</script>