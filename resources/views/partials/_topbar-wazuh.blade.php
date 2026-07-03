<nav class="navbar col-lg-12 col-12 px-0 py-0 py-lg-4 d-flex flex-row">
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button>
          @include('partials._navbar-logo')
        @php
            $breadcrumbPageLabels = [
                'agent.detail'               => 'Details',
                'agent.security-events'      => 'Security Events',
                'agent.integrity-monitoring' => 'Integrity Monitoring',
                'agent.sca'                  => 'SCA',
                'agent.vulnerabilities'      => 'Vulnerabilities',
                'agent.mitre-attack'         => 'MITRE ATT&CK',
                'agent.compliance'           => 'Compliance',
                'agent.inventory'           => 'Inventory Data',
            ];
            $currentRoute     = Route::currentRouteName();
            $currentPageLabel = $breadcrumbPageLabels[$currentRoute] ?? null;
        @endphp
        <nav aria-label="breadcrumb" class="d-none d-md-block align-self-center">
            <ol class="breadcrumb mb-0 bg-transparent p-0 align-items-center">
                <li class="breadcrumb-item fs-5">
                    <a href="{{ route('agent') }}" class="text-light text-decoration-none">Agents</a>
                </li>
                @if($agent && $currentRoute !== 'agent')
                    @if($currentPageLabel)
                    <li class="breadcrumb-item fs-5">
                        <a href="{{ route('agent.detail', $agent->agent_id) }}" class="text-light text-decoration-none">
                            {{ $agent->name ?? $agent->agent_id ?? 'Unknown' }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-light fs-5" aria-current="page">
                        {{ $currentPageLabel }}
                    </li>
                    @else
                    <li class="breadcrumb-item active text-light fs-5" aria-current="page">
                        {{ $agent->name ?? $agent->agent_id ?? 'Unknown' }}
                    </li>
                    @endif
                @endif
            </ol>
        </nav>
          @include('partials._profile-dropdown', ['logoutHref' => '/auth/logout'])
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>