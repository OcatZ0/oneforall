/**
 * Template - Orchestrates various UI behaviors (navigation, sidebar, navbar, banners)
 */
import { initNavigation } from './modules/navigationHighlighter.js';
import { initSidebar } from './modules/sidebarCollapse.js';
import { initNavbarScroll } from './modules/navbarScroll.js';
import { initBannerManager } from './modules/bannerManager.js';
import { SELECTORS, CSS_CLASSES } from './utils/constants.js';

(function($) {
  'use strict';
  $(function() {
    // Initialize all UI modules
    initNavigation();
    initSidebar();
    initNavbarScroll();
    initBannerManager();

    // Add input helper styling to form controls
    $(SELECTORS.FORM_CHECK_LABEL).append('<i class="' + CSS_CLASSES.INPUT_HELPER + '"></i>');
  });
})(jQuery);
