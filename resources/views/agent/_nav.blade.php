{{-- SECONDARY NAV --}}
<div class="bg-dark border-bottom border-secondary">
  <div class="d-flex align-items-center px-3">
    <ul class="nav flex-nowrap overflow-auto">
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small {{ $activeTab === 'detail' ? 'active' : '' }}" href="{{ route('agent.detail', $agent->agent_id ?? '#') }}">
          <span class="mdi mdi-home"></span> Details
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small {{ $activeTab === 'security-events' ? 'active' : '' }}" href="{{ route('agent.security-events', $agent->agent_id ?? '#') }}">
          <span class="mdi mdi-format-list-bulleted"></span> Security Events
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small {{ $activeTab === 'integrity-monitoring' ? 'active' : '' }}" href="{{ route('agent.integrity-monitoring', $agent->agent_id ?? '#') }}">
          <span class="mdi mdi-shield"></span> Integrity Monitoring
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small {{ $activeTab === 'sca' ? 'active' : '' }}" href="{{ route('agent.sca', $agent->agent_id ?? '#') }}">
          <span class="mdi mdi-clock-outline"></span> SCA
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small {{ $activeTab === 'vulnerabilities' ? 'active' : '' }}" href="{{ route('agent.vulnerabilities', $agent->agent_id ?? '#') }}">
          <span class="mdi mdi-bug"></span> Vulnerabilities
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small {{ $activeTab === 'mitre-attack' ? 'active' : '' }}" href="{{ route('agent.mitre-attack', $agent->agent_id ?? '#') }}">
          <span class="mdi mdi-sword-cross"></span> MITRE ATT&amp;CK
        </a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small dropdown-toggle {{ $activeTab === 'compliance' ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="mdi mdi-check-decagram"></span> Compliance
        </a>
        <ul class="dropdown-menu dropdown-menu-dark">
          <li><a class="dropdown-item" href="{{ route('agent.compliance', $agent->agent_id ?? '#') }}?compliance_type=pci_dss">PCI DSS</a></li>
          <li><a class="dropdown-item" href="{{ route('agent.compliance', $agent->agent_id ?? '#') }}?compliance_type=gdpr">GDPR</a></li>
          <li><a class="dropdown-item" href="{{ route('agent.compliance', $agent->agent_id ?? '#') }}?compliance_type=hipaa">HIPAA</a></li>
          <li><a class="dropdown-item" href="{{ route('agent.compliance', $agent->agent_id ?? '#') }}?compliance_type=nist_800_53">NIST 800-53</a></li>
          <li><a class="dropdown-item" href="{{ route('agent.compliance', $agent->agent_id ?? '#') }}?compliance_type=tsc">TSC</a></li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small {{ $activeTab === 'inventory' ? 'active' : '' }}" href="{{ route('agent.inventory', $agent->agent_id ?? '#') }}">
          <span class="mdi mdi-database"></span> Inventory Data
        </a>
      </li>
    </ul>
    <div class="ms-auto d-flex gap-2 flex-shrink-0 py-1">
      <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent') }}" title="Back to Agents List">
        <span class="mdi mdi-arrow-left"></span> Back
      </a>
    </div>
  </div>
</div>
