/**
 * Navbar Scroll - Handles fixed navbar behavior on scroll
 */
import { UI, SELECTORS, CSS_CLASSES } from '../utils/constants.js';

export function initNavbarScroll() {
  const navbar = $(SELECTORS.NAVBAR);
  const body = $(SELECTORS.BODY);

  $(window).scroll(function() {
    const isDesktop = window.matchMedia(UI.NAVBAR_BREAKPOINT).matches;
    const isMobile = window.matchMedia(UI.NAVBAR_MOBILE_BREAKPOINT).matches;
    const scrollTop = $(window).scrollTop();

    if (isDesktop) {
      if (scrollTop >= UI.NAVBAR_SCROLL_THRESHOLD) {
        navbar.addClass(CSS_CLASSES.NAVBAR_MINI + ' ' + CSS_CLASSES.FIXED_TOP);
        body.addClass(CSS_CLASSES.NAVBAR_FIXED_TOP);
      } else {
        navbar.removeClass(CSS_CLASSES.NAVBAR_MINI + ' ' + CSS_CLASSES.FIXED_TOP);
        body.removeClass(CSS_CLASSES.NAVBAR_FIXED_TOP);
      }
    }

    if (isMobile) {
      navbar.addClass(CSS_CLASSES.NAVBAR_MINI + ' ' + CSS_CLASSES.FIXED_TOP);
      body.addClass(CSS_CLASSES.NAVBAR_FIXED_TOP);
    }
  });
}
