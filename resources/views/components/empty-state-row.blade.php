@props(['colspan', 'icon', 'title', 'subtitle'])

<tr>
  <td colspan="{{ $colspan }}" class="text-center py-5 text-muted">
    <span class="mdi {{ $icon }} d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
    <span class="d-block fw-semibold mb-1">{{ $title }}</span>
    <span class="d-block small">{{ $subtitle }}</span>
  </td>
</tr>
