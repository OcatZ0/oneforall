@extends('layouts.wazuh')

@section('title', 'Agent Detail - One For All')

@section('content')

<!-- SECONDARY NAV -->
<div class="bg-dark border-bottom border-secondary">
  <div class="d-flex align-items-center px-3">
    <ul class="nav flex-nowrap overflow-auto">
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.security-events', $agent->id_agent) }}">
          <span class="mdi mdi-format-list-bulleted"></span> Security events
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.integrity-monitoring', $agent->id_agent) }}">
          <span class="mdi mdi-shield"></span> Integrity monitoring
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small active" href="{{ route('agent.sca', $agent->id_agent) }}">
          <span class="mdi mdi-clock-outline"></span> SCA
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.vulnerabilities', $agent->id_agent) }}">
          <span class="mdi mdi-bug"></span> Vulnerabilities
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.mitre-attack', $agent->id_agent) }}">
          <span class="mdi mdi-target"></span> MITRE ATT&amp;CK
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="#">
          More <span class="mdi mdi-chevron-down"></span>
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

<div class="content-wrapper">
    <!-- MAIN CONTENT -->
     <div class="row mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Compliance overview</span>
                    <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
                </div>
                <div class="card-body py-3 small">

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Checks evolution</span>
                    <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
                </div>
                <div class="card-body py-3 small">

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Top 5 failed checks</span>
                    <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
                </div>
                <div class="card-body py-3 small">

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Top 5 compliance rules</span>
                    <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
                </div>
                <div class="card-body py-3 small">

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Compliance status</span>
                    <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
                </div>
                <div class="card-body py-3 small">

                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">SCA Checks</span>
                    <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
                </div>
                <div class="card-body py-3 small">

                </div>
            </div>
        </div>
     </div>
</div>
