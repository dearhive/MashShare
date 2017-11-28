<?php
?>
<script id="tmpl-mashsb-rwmb-image-item" type="text/html">
  <input type="hidden" name="{{{ data.fieldName }}}" value="{{{ data.id }}}" class="mashsb-rwmb-media-input">
  <div class="mashsb-rwmb-media-preview">
    <div class="mashsb-rwmb-media-content">
      <div class="centered">
        <# if ( 'image' === data.type && data.sizes ) { #>
          <# if ( data.sizes.thumbnail ) { #>
            <img src="{{{ data.sizes.thumbnail.url }}}">
          <# } else { #>
            <img src="{{{ data.sizes.full.url }}}">
          <# } #>
        <# } else { #>
          <# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
            <img src="{{ data.image.src }}" />
          <# } else { #>
            <img src="{{ data.icon }}" />
          <# } #>
        <# } #>
      </div>
    </div>
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
