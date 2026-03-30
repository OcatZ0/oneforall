/**
 * Chart Factory - Creates Chart.js instances with common configurations
 */

export function createChart(elementId, type, data, customOptions = {}) {
  const canvas = document.getElementById(elementId);
  if (!canvas) {
    console.warn(`Canvas element with ID "${elementId}" not found`);
    return null;
  }

  const ctx = canvas.getContext('2d');

  // Default responsive options
  const defaultOptions = {
    responsive: true,
    maintainAspectRatio: true,
    ...customOptions
  };

  return new Chart(ctx, {
    type,
    data,
    options: defaultOptions
  });
}

/**
 * Create a bar chart with common styling
 */
export function createBarChart(elementId, labels, datasets, customOptions = {}) {
  const defaultOptions = {
    responsive: true,
    maintainAspectRatio: true,
    layout: {
      padding: {
        left: 0,
        right: 0,
        top: 20,
        bottom: 0
      }
    },
    scales: {
      yAxes: [{
        display: true,
        gridLines: {
          display: true,
          drawBorder: false
        },
        ticks: {
          display: true,
          fontColor: "#b1b0b0",
          fontSize: 10,
          padding: 10
        }
      }],
      xAxes: [{
        stacked: false,
        ticks: {
          beginAtZero: true,
          fontColor: "#b1b0b0",
          fontSize: 10
        },
        gridLines: {
          color: "rgba(0, 0, 0, 0)",
          display: false
        },
        barPercentage: 0.9,
        categoryPercentage: 0.7
      }]
    },
    legend: {
      display: false
    },
    ...customOptions
  };

  return createChart(elementId, 'bar', { labels, datasets }, defaultOptions);
}

/**
 * Create a line chart with common styling
 */
export function createLineChart(elementId, labels, datasets, customOptions = {}) {
  const defaultOptions = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      xAxes: [{
        display: true,
        ticks: {
          display: false
        },
        gridLines: {
          display: false,
          drawBorder: false,
          color: 'transparent',
          zeroLineColor: '#eeeeee'
        }
      }],
      yAxes: [{
        display: true,
        ticks: {
          display: true,
          autoSkip: false,
          maxRotation: 0,
          fontColor: "#b1b0b0",
          fontSize: 10,
          padding: 18
        },
        gridLines: {
          drawBorder: false,
          color: "#f8f8f8",
          zeroLineColor: "#f8f8f8"
        }
      }]
    },
    legend: {
      display: false
    },
    elements: {
      line: {
        tension: 0
      },
      point: {
        radius: 0
      }
    },
    tooltips: {
      enabled: true
    },
    ...customOptions
  };

  return createChart(elementId, 'line', { labels, datasets }, defaultOptions);
}

/**
 * Create a horizontal bar chart
 */
export function createHorizontalBarChart(elementId, labels, datasets, customOptions = {}) {
  const defaultOptions = {
    responsive: true,
    maintainAspectRatio: true,
    layout: {
      padding: {
        left: -7,
        right: 0,
        top: 0,
        bottom: 0
      }
    },
    scales: {
      yAxes: [{
        display: true,
        gridLines: {
          display: false,
          drawBorder: false
        },
        ticks: {
          display: true,
          fontColor: "#b1b0b0",
          fontSize: 10,
          padding: 10
        },
        barPercentage: 1,
        categoryPercentage: 0.6
      }],
      xAxes: [{
        display: true,
        stacked: false,
        ticks: {
          display: false,
          beginAtZero: true,
          fontColor: "#b1b0b0",
          fontSize: 10
        },
        gridLines: {
          display: true,
          drawBorder: false,
          lineWidth: 1,
          color: "#f5f5f5",
          zeroLineColor: "#f5f5f5"
        }
      }]
    },
    legend: {
      display: false
    },
    elements: {
      point: {
        radius: 3,
        backgroundColor: '#ff4c5b'
      }
    },
    ...customOptions
  };

  return createChart(elementId, 'horizontalBar', { labels, datasets }, defaultOptions);
}
