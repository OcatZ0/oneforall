/**
 * Application-wide constants
 */

// Color Palette
export const COLORS = {
  primary: '#1cbccd',
  secondary: '#6640b2',
  danger: '#ff4c5b',
  success: '#1faf47',
  warning: '#ffbf36',
  error: '#f83e37',
  teal: '#00cccb',
  gray: '#d8d8d8',
  lightGray: '#f8f8f8',
  borderGray: '#b1b0b0',
  bgGray: '#f5f5f5'
};

// Chart Configuration
export const CHART_CONFIG = {
  responsive: true,
  maintainAspectRatio: true,
  legend: {
    display: false
  }
};

// UI Constants
export const UI = {
  NAVBAR_SCROLL_THRESHOLD: 197,
  NAVBAR_BREAKPOINT: '(min-width: 991px)',
  NAVBAR_MOBILE_BREAKPOINT: '(max-width: 991px)',
  BANNER_COOKIE_NAME: 'spica-free-banner',
  BANNER_COOKIE_EXPIRATION_MS: 24 * 60 * 60 * 1000 // 24 hours
};

// CSS Classes
export const CSS_CLASSES = {
  ACTIVE: 'active',
  SHOW: 'show',
  DISPLAY_FLEX: 'd-flex',
  DISPLAY_NONE: 'd-none',
  SIDEBAR_HIDDEN: 'sidebar-hidden',
  SIDEBAR_ICON_ONLY: 'sidebar-icon-only',
  SIDEBAR_TOGGLE_DISPLAY: 'sidebar-toggle-display',
  NAVBAR_MINI: 'navbar-mini',
  NAVBAR_FIXED_TOP: 'navbar-fixed-top',
  FIXED_TOP: 'fixed-top'
};

// Selectors
export const SELECTORS = {
  BODY: 'body',
  CONTENT_WRAPPER: '.content-wrapper',
  CONTAINER_SCROLLER: '.container-scroller',
  FOOTER: '.footer',
  SIDEBAR: '.sidebar',
  NAVBAR: '.navbar:not(.top-navbar)',
  NAV_ITEM: '.nav-item',
  NAV_LINK: '.nav li a',
  COLLAPSE: '.collapse',
  SUBMENU: '.sub-menu',
  MINIMIZE_BTN: '[data-toggle="minimize"]',
  FORM_CHECK_LABEL: '.form-check label, .form-radio label',
  INPUT_HELPER: '.input-helper',
  PRO_BANNER: '#proBanner',
  BANNER_CLOSE: '#bannerClose'
};
