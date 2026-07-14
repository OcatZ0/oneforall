{{-- Wazuh connectivity toast — shown when a controller passes wazuhConnected = false --}}
@if(isset($wazuhConnected) && !$wazuhConnected)
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof notyf !== 'undefined') {
      notyf.error('Tidak dapat terhubung ke Wazuh API. Beberapa data mungkin tidak tersedia.');
    }
  });
</script>
@endif
