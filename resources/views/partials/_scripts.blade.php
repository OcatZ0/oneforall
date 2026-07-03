<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('js/jquery.cookie.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/off-canvas.js') }}"></script>
<script src="{{ asset('js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('js/template.js') }}" type="module"></script>
<script>
function emptyStateRow(colspan, icon, title, subtitle) {
  return `<tr><td colspan="${colspan}" class="text-center py-5 text-muted">
    <span class="mdi ${icon} d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
    <span class="d-block fw-semibold mb-1">${title}</span>
    <span class="d-block small">${subtitle}</span>
  </td></tr>`;
}
</script>