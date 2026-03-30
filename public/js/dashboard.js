import { createChart, createBarChart, createLineChart, createHorizontalBarChart } from './utils/chartFactory.js';
import { COLORS } from './utils/constants.js';

(function($) {
  'use strict';
  $(function() {

    // ===== Audience Chart =====
    if ($("#audience-chart").length) {
      createBarChart('audience-chart',
        ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        [
          {
            type: 'line',
            fill: false,
            data: [100, 230, 130, 140, 270, 140],
            borderColor: COLORS.danger
          },
          {
            label: 'Offline Sales',
            data: [100, 230, 340, 340, 260, 340],
            backgroundColor: COLORS.secondary
          },
          {
            label: 'Online Sales',
            data: [130, 190, 250, 250, 190, 260],
            backgroundColor: COLORS.primary
          }
        ],
        {
          layout: {
            padding: { left: 0, right: 0, top: 20, bottom: 0 }
          },
          scales: {
            yAxes: [{
              gridLines: { color: COLORS.lightGray },
              ticks: { min: 0, max: 400, stepSize: 100 }
            }]
          },
          elements: { point: { backgroundColor: COLORS.danger } }
        }
      );
    }

    // ===== Balance Chart =====
    if ($("#balance-chart").length) {
      const balanceData = {
        labels: ["Mon","Tue","Wed","Thu","Fri","Sat","Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun","Mon","Tue","Wed","Thu"],
        datasets: [{
          data: [2600, 1400, 2200, 1200, 2300, 2400, 2700, 1200, 2800, 2600, 1250, 1900, 1800, 2800, 2800, 1200, 2500, 2600, 1800, 1200, 2000, 1800, 2700, 1600, 2800, 2000, 2100, 1200, 2000, 1200, 1200, 2500],
          borderColor: COLORS.success,
          borderWidth: 3,
          fill: false,
          label: "services"
        }]
      };

      createLineChart('balance-chart',
        balanceData.labels,
        balanceData.datasets,
        {
          plugins: { filler: { propagate: false } },
          scales: {
            yAxes: [{
              ticks: {
                stepSize: 1000,
                max: 3000,
                callback: function(value) {
                  const ranges = [
                    { divider: 1e6, suffix: 'M' },
                    { divider: 1e3, suffix: 'k' }
                  ];
                  for (let i = 0; i < ranges.length; i++) {
                    if (value >= ranges[i].divider) {
                      return (value / ranges[i].divider).toString() + ranges[i].suffix;
                    }
                  }
                  return value;
                }
              }
            }]
          }
        }
      );
    }

    // ===== Task Chart =====
    if ($("#task-chart").length) {
      createBarChart('task-chart',
        ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug"],
        [{
          label: 'Profit',
          data: [-3, -5, -5, 3, 4, -5, -1, 9],
          backgroundColor: COLORS.error
        }],
        {
          scales: {
            yAxes: [{
              gridLines: { color: '#f1f3f9' },
              ticks: { min: -10, max: 10, stepSize: 10 }
            }],
            xAxes: [{
              display: false,
              categoryPercentage: 1
            }]
          }
        }
      );
    }

    // ===== Regional Chart =====
    if ($("#regional-chart").length) {
      createHorizontalBarChart('regional-chart',
        ["12", "8", "4", "0"],
        [
          {
            label: 'Income',
            data: [400, 360, 360, 360],
            backgroundColor: COLORS.primary
          },
          {
            label: 'Expenses',
            data: [320, 190, 180, 140],
            backgroundColor: COLORS.warning
          }
        ],
        {
          legendCallback: function(chart) {
            const text = [];
            text.push('<div class="item me-4 d-flex align-items-center">');
            text.push(`<div class="item-box me-2" style="background-color: ${chart.data.datasets[0].backgroundColor}"></div><p class="text-black mb-0">${chart.data.datasets[0].label}</p>`);
            text.push('</div>');
            text.push('<div class="item d-flex align-items-center">');
            text.push(`<div class="item-box me-2" style="background-color: ${chart.data.datasets[1].backgroundColor}"></div><p class="text-black mb-0">${chart.data.datasets[1].label}</p>`);
            text.push('</div>');
            return text.join('');
          }
        }
      );
      const regionalChart = Chart.helpers.getCanvas(document.getElementById('regional-chart')).chart;
      if (regionalChart && document.querySelector('#regional-chart-legend')) {
        document.querySelector('#regional-chart-legend').innerHTML = regionalChart.generateLegend();
      }
    }

    // ===== Activity Chart =====
    if ($("#activity-chart").length) {
      createBarChart('activity-chart',
        ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug"],
        [
          {
            label: 'Profit',
            data: [320, 300, 340, 320, 315, 270, 290, 310, 340, 335, 300, 320, 300, 340, 320, 315, 270, 290, 310, 340, 335, 300, 320, 300, 340, 320, 315, 270, 290, 310, 340, 335, 300, 320, 300, 340, 320, 315, 270, 290, 310, 340, 335, 300],
            backgroundColor: COLORS.warning
          },
          {
            label: 'Target',
            data: [540, 500, 600, 540, 535, 470, 490, 510, 540, 535, 500, 540, 500, 450, 570, 535, 470, 490, 510, 540, 535, 500, 540, 500, 470, 500, 535, 470, 490, 510, 540, 535, 500, 540, 500, 490, 590, 505, 470, 490, 510, 540, 535, 500],
            backgroundColor: COLORS.secondary
          }
        ],
        {
          scales: {
            yAxes: [{
              display: false,
              ticks: { max: 600, stepSize: 100 }
            }],
            xAxes: [{
              display: false,
              stacked: true,
              barPercentage: 0.8,
              categoryPercentage: 0.9
            }]
          }
        }
      );
    }

    // ===== Status Chart =====
    if ($("#status-chart").length) {
      const statusLabels = ["IA", "RI", "NY", "CO", "MI", "FL", "IL", "PA", "LA", "NJ", "CA", "TX", "LA", "PQ", "RF", "JG"];
      const repeatedLabels = Array(6).fill(statusLabels).flat().slice(0, 96).concat(statusLabels.slice(0, 10));

      createLineChart('status-chart',
        repeatedLabels,
        [
          {
            data: [30,40,34,48,35,43,40,48,38,39,35,45,32,33,28,22,24,23,36,28,31,22,32,27,30,25,36,30,38,34,30,27,30,26,26,18,23,31,18,19,17,19,17,17,14,16,15,17,10,15,9,14,13,20,18,15,12,16,17,14,20,10,19,12,12,16,11,17,15,17,9,8,12,15,10,15,16,20,18,20,18,28,28,33,23,38,20,28,23,24,17,14,21,15,24,11,13,13,19,13,15,18,10,20,22,28],
            backgroundColor: COLORS.teal,
            borderColor: COLORS.teal,
            borderWidth: 0,
            fill: 'origin',
            label: "purchases"
          },
          {
            data: [60,70,64,78,65,73,70,78,68,69,65,75,62,63,58,52,54,53,66,58,61,52,62,57,60,55,66,60,68,64,60,57,60,56,56,48,53,61,48,49,47,49,47,47,34,36,35,37,40,35,39,44,43,50,48,45,42,46,37,44,50,40,39,42,32,36,41,47,45,47,39,38,42,45,40,45,46,50,48,50,48,58,58,63,53,68,50,58,53,54,47,44,51,45,54,41,43,43,49,43,45,48,40,50,52,58],
            backgroundColor: COLORS.gray,
            borderColor: COLORS.gray,
            borderWidth: 1,
            fill: 'origin',
            label: "services"
          },
          {
            data: [90, 100, 94, 108, 95, 103, 100, 108, 98, 99, 95, 105, 92, 93, 88, 82, 84, 83, 96, 88, 91, 82, 92, 87, 90, 85, 96, 90, 98, 94, 90, 87, 90, 86, 86, 78, 83, 91, 78, 79, 77, 79, 77, 77, 64, 66, 65, 67, 70, 65, 69, 74, 73, 80, 78, 75, 72, 76, 67, 74, 80, 70, 69, 72, 62, 66, 71, 77, 75, 77, 69, 68, 72, 75, 70, 75, 76, 80, 78, 80, 78, 88, 88, 93, 83, 98, 80, 88, 83, 84, 77, 74, 81, 75, 84, 71, 73, 73, 79, 73, 75, 78, 70, 80, 82, 88],
            backgroundColor: COLORS.secondary,
            borderColor: COLORS.secondary,
            borderWidth: 1,
            fill: 'origin',
            label: "services"
          }
        ],
        {
          scales: {
            yAxes: [{
              display: false,
              ticks: { min: 0, max: 110, stepSize: 10 }
            }]
          }
        }
      );
    }

  });
})(jQuery);
