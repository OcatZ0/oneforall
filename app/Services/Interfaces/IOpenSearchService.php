<?php

namespace App\Services\Interfaces;

use Carbon\Carbon;

interface IOpenSearchService
{
    public function getAlertTrendLast7Days($agentIds = null, $isAdmin = true);
    public function getAlertSeverityDistribution($agentIds = null, $isAdmin = true);
    public function getTotalAlertCount($agentIds = null, $isAdmin = true);
    public function getAgentEvolutionByTimeRange($timeRange = '24h', $agentIds = null, $baseTime = null, $isAdmin = true);
    public function getOsDistribution($agentIds = null, $isAdmin = true);
    public function getTopTriggeredRules($limit = 5, $agentIds = null, $isAdmin = true);
    public function getTopAgentsByAlerts($limit = 5, $agentIds = null, $isAdmin = true);
    public function getFimEvents($agentId, $limit = 5);
    public function getEventsCountEvolution($agentId, $timeRange = '24h');
    public function getAgentAlertStats($agentId);
    public function getAgentCompliance($agentId, $complianceType = 'gdpr', $timeRange = '30d');
    public function getEventsCountEvolutionByCompliance($agentId, $complianceType = 'gdpr', $timeRange = '24h');
    public function getSecurityEventsMetrics($agentId, $timeRange = 'now-24h');
    public function getAlertGroupsEvolution($agentId, $timeRange = '24h');
    public function getTopAlerts($agentId, $timeRange = '24h', $limit = 5);
    public function getTopRuleGroups($agentId, $timeRange = '24h', $limit = 5);
    public function getTopPCIDSS($agentId, $timeRange = '24h', $limit = 5);
    public function getRecentAlerts($agentId, $timeRange = '24h', $limit = 10);
    public function getAlertsEvolutionByLevel($agentId, $timeRange = '24h');
    public function getMitreTactics($agentId, $timeRange = '24h');
}
