<div style="height: calc(100vh - 20px); min-height: 520px; background: #f7f9fb">
  <div style="height:40px; display:flex; align-items:center; justify-content:space-between; padding:0 16px; border-bottom:1px solid #d7e0e5; background:#fff; box-sizing:border-box">
  <form id="superset-bi-filter" style="color:#31546b; font:12px Arial,sans-serif">
    <label>Exercício <input id="bi-exercicio" type="number" min="2000" max="{{ date('Y') + 1 }}" required style="width:64px; border:1px solid #b8c7d1"></label>
    <label>De <input id="bi-periodo-inicial" type="date" required style="border:1px solid #b8c7d1"></label>
    <label style="margin-left:8px">até <input id="bi-periodo-final" type="date" required style="border:1px solid #b8c7d1"></label>
    <button type="submit" style="margin-left:8px; padding:4px 10px; border:0; border-radius:3px; background:#397da8; color:#fff; cursor:pointer">Aplicar</button>
  </form>
  <div style="display:flex; align-items:center; gap:10px">
    <label style="color:#31546b; font:12px Arial,sans-serif; display:flex; align-items:center; gap:4px">
      <span style="font-weight:bold">Base:</span>
      <select id="bi-database" style="padding:4px 8px; border:1px solid #b8c7d1; border-radius:4px; background:#fff; color:#31546b; font-size:12px; cursor:pointer">
        <option value="ecidade" selected>ecidade</option>
        <option value="dbpadrao">dbpadrao</option>
      </select>
    </label>
    <button id="superset-bi-theme" type="button" title="Alternar tema do painel"
      style="padding:6px 12px; border:1px solid #b8c7d1; border-radius:4px; background:#fff; color:#31546b; cursor:pointer">
      Tema escuro
    </button>
  </div>
  </div>
  <div id="superset-bi" style="height: calc(100% - 40px)">
  <div id="superset-bi-status" style="padding: 24px; color: #355; font-family: Arial, sans-serif">
    Carregando painel BI...
  </div>
  </div>
</div>
<script src="{{ url('/public/js/bi/superset-embedded-sdk.js') }}"></script>
<script>
  const mountPoint = document.getElementById('superset-bi');
  const status = document.getElementById('superset-bi-status');
  const themeButton = document.getElementById('superset-bi-theme');
  const databaseSelect = document.getElementById('bi-database');
  const filterForm = document.getElementById('superset-bi-filter');
  const exerciseInput = document.getElementById('bi-exercicio');
  const startInput = document.getElementById('bi-periodo-inicial');
  const endInput = document.getElementById('bi-periodo-final');
  const exercise = parseInt('{{ (int) (function_exists("db_getsession") ? db_getsession("DB_anousu", false) : date("Y")) ?: date("Y") }}', 10);
  exerciseInput.value = exercise;
  startInput.value = exercise + '-01-01';
  endInput.value = exercise + '-12-31';

  function showError(error) {
    var message = error && error.message ? error.message : 'falha desconhecida';
    mountPoint.innerHTML = '<div style="padding:24px;color:#355;font-family:Arial,sans-serif"></div>';
    mountPoint.firstChild.textContent = 'Painel BI indisponível: ' + message;
  }

  async function loadDashboard() {
    if (!window.supersetEmbeddedSdk || !window.supersetEmbeddedSdk.embedDashboard) {
      throw new Error('SDK de incorporação não carregado.');
    }

    const response = await fetch('{{ url('/v4/api/bi/balancete-receita-recurso/guest-token') }}', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
      body: JSON.stringify({
        exercicio: exerciseInput.value,
        periodoInicial: startInput.value,
        periodoFinal: endInput.value,
        base: databaseSelect ? databaseSelect.value : 'ecidade'
      })
    });

    if (!response.ok) {
      throw new Error('falha ao emitir token BI (HTTP ' + response.status + ').');
    }

    const data = await response.json();
    if (status && status.parentNode) status.remove();
    mountPoint.innerHTML = '';
    const embeddedDashboard = await window.supersetEmbeddedSdk.embedDashboard({
      id: data.dashboardUuid,
      supersetDomain: data.supersetUrl,
      mountPoint: mountPoint,
      fetchGuestToken: () => Promise.resolve(data.token),
      dashboardUiConfig: {
        hideTitle: true,
        hideChartControls: true,
        hideTab: true
      }
    });

    var themeMode = 'default';
    if (embeddedDashboard && embeddedDashboard.setThemeMode) {
      embeddedDashboard.setThemeMode(themeMode);
      themeButton.addEventListener('click', function () {
        themeMode = themeMode === 'default' ? 'dark' : 'default';
        embeddedDashboard.setThemeMode(themeMode);
        themeButton.textContent = themeMode === 'default' ? 'Tema escuro' : 'Tema claro';
      });
    } else {
      themeButton.style.display = 'none';
    }

    const iframe = mountPoint.querySelector('iframe');
    if (iframe) {
      iframe.style.width = '100%';
      iframe.style.height = '100%';
      iframe.style.border = '0';
    }
  }

  filterForm.addEventListener('submit', function (event) {
    event.preventDefault();
    loadDashboard().catch(showError);
  });

  if (databaseSelect) {
    databaseSelect.addEventListener('change', function () {
      loadDashboard().catch(showError);
    });
  }

  exerciseInput.addEventListener('change', function () {
    startInput.value = exerciseInput.value + '-01-01';
    endInput.value = exerciseInput.value + '-12-31';
  });

  loadDashboard().catch(showError);
</script>
