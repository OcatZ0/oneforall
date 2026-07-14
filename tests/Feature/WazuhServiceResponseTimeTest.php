<?php

namespace Tests\Feature;

use App\Services\WazuhService;
use Tests\TestCase;

/**
 * Benchmarking test — records real network response time for every WazuhService
 * method that hits the Wazuh REST API. Not a correctness test: Wazuh may be
 * unreachable depending on the environment, so failures are tolerated and timed
 * anyway. Run with `php artisan test --filter WazuhServiceResponseTimeTest` and
 * read the timing table printed to STDERR after the test runs.
 */
class WazuhServiceResponseTimeTest extends TestCase
{
    private WazuhService $wazuh;
    private array $timings = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->wazuh = app(WazuhService::class);
    }

    protected function tearDown(): void
    {
        if (!empty($this->timings)) {
            fwrite(STDERR, "\n\n=== Wazuh API response times ===\n");
            foreach ($this->timings as $method => $ms) {
                fwrite(STDERR, sprintf("%-30s %8.1f ms\n", $method, $ms));
            }
            fwrite(STDERR, "=================================\n");
        }
        parent::tearDown();
    }

    /** Runs $fn, records elapsed time under $label regardless of outcome, returns $fn's result (or null on failure). */
    private function time(string $label, \Closure $fn): mixed
    {
        $start  = microtime(true);
        $result = null;
        try {
            $result = $fn();
        } catch (\Throwable $e) {
            // still record the timing even if the call throws
        }
        $this->timings[$label] = (microtime(true) - $start) * 1000;
        return $result;
    }

    public function test_wazuh_endpoint_response_times(): void
    {
        $token = $this->time('getToken', fn () => $this->wazuh->getToken());

        if (!$token) {
            $this->markTestSkipped('Wazuh API unreachable in this environment — see recorded getToken timing above.');
        }

        $agentsResult = $this->time('getAgents', fn () => $this->wazuh->getAgents($token, 0, 10));
        $agentId      = $agentsResult['agents'][0]['id'] ?? '000';

        $this->time('getAgent', fn () => $this->wazuh->getAgent($token, $agentId));

        $policies = $this->time('getSCAPolicies', fn () => $this->wazuh->getSCAPolicies($token, $agentId));
        $policyId = $policies[0]['policy_id'] ?? null;

        if ($policyId) {
            $this->time('getSCAChecks', fn () => $this->wazuh->getSCAChecks($token, $agentId, $policyId, 10, 0));
        }

        $this->time('getVulnerabilities', fn () => $this->wazuh->getVulnerabilities($token, $agentId, 10, 0));
        $this->time('getVulnerabilityCounts', fn () => $this->wazuh->getVulnerabilityCounts($token, $agentId));
        $this->time('getVulnerabilitiesLastScan', fn () => $this->wazuh->getVulnerabilitiesLastScan($token, $agentId));
        $this->time('getInventoryHardware', fn () => $this->wazuh->getInventoryHardware($token, $agentId));
        $this->time('getInventoryOS', fn () => $this->wazuh->getInventoryOS($token, $agentId));
        $this->time('getInventoryNetInterfaces', fn () => $this->wazuh->getInventoryNetInterfaces($token, $agentId, 10, 0));
        $this->time('getInventoryNetPorts', fn () => $this->wazuh->getInventoryNetPorts($token, $agentId, 10, 0));
        $this->time('getInventoryNetAddr', fn () => $this->wazuh->getInventoryNetAddr($token, $agentId, 10, 0));
        $this->time('getInventoryHotfixes', fn () => $this->wazuh->getInventoryHotfixes($token, $agentId, 10, 0));
        $this->time('getInventoryPackages', fn () => $this->wazuh->getInventoryPackages($token, $agentId, 10, 0));
        $this->time('getInventoryProcesses', fn () => $this->wazuh->getInventoryProcesses($token, $agentId, 10, 0));

        // The timing table itself is the point of this test; just assert it was collected.
        $this->assertNotEmpty($this->timings);
    }
}
