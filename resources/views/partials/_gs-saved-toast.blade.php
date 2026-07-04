{{-- GridStack save toast --}}
<div id="gs-saved-toast" aria-live="polite" style="
  position:fixed; bottom:88px; left:50%; transform:translateX(-50%);
  z-index:10000; display:none; align-items:center; gap:8px;
  background:#27ae60; color:#fff; padding:10px 20px;
  border-radius:24px; box-shadow:0 4px 16px rgba(0,0,0,.18);
  font-size:13px; font-weight:500; white-space:nowrap; pointer-events:none;">
  <span class="mdi mdi-check-circle"></span> Tata letak disimpan
</div>
<script>
function gsShowSavedToast(msg) {
  const t = document.getElementById('gs-saved-toast');
  if (!t) return;
  t.style.background = '#27ae60';
  t.innerHTML = '<span class="mdi mdi-check-circle"></span> ' + (msg || 'Tata letak disimpan');
  t.style.display = 'flex';
  clearTimeout(t._hideTimer);
  t._hideTimer = setTimeout(() => { t.style.display = 'none'; }, 2000);
}
function gsShowErrorToast(msg) {
  const t = document.getElementById('gs-saved-toast');
  if (!t) return;
  t.style.background = '#e74c3c';
  t.innerHTML = '<span class="mdi mdi-alert-circle"></span> ' + msg;
  t.style.display = 'flex';
  clearTimeout(t._hideTimer);
  t._hideTimer = setTimeout(() => { t.style.display = 'none'; }, 3000);
}
</script>
