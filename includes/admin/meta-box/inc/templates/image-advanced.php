<?php
?>
<script id="tmpl-mashsb-rwmb-image-item" type="text/html">
  <input type="hidden" name="{{{ data.fieldName }}}" value="{{{ data.id }}}" class="mashsb-rwmb-media-input">
  <!--<div class="mashsb-rwmb-media-preview" style="background-image: url({{{ data.sizes.full.url }}});">-->
  <img src="{{{ data.sizes.full.url }}}">
    <!--<div class="mashsb-rwmb-media-content">
      <div class="centered">
           <img src="{{{ data.sizes.full.url }}}">
      </div>
    </div>//-->
  </div>
  <div class="mashsb-rwmb-overlay"></div>
  <div class="mashsb-rwmb-media-bar">
    <a class="mashsb-rwmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
      <span class="dashicons dashicons-edit"></span>
    </a>
    <a href="#" class="mashsb-rwmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
      <span class="dashicons dashicons-no-alt"></span>
    </a>
  </div>
</script>
