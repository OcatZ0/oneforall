/**
 * Navigation Highlighter - Highlights active nav links based on current URL
 */
import { SELECTORS, CSS_CLASSES } from '../utils/constants.js';

export function initNavigation() {
  const sidebar = $(SELECTORS.SIDEBAR);
  const current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');

  function addActiveClass(element) {
    const href = element.attr('href');

    if (current === "") {
      // For root URL
      if (href && href.indexOf("index.html") !== -1) {
        element.parents(SELECTORS.NAV_ITEM).last().addClass(CSS_CLASSES.ACTIVE);
        if (element.parents(SELECTORS.SUBMENU).length) {
          element.closest(SELECTORS.COLLAPSE).addClass(CSS_CLASSES.SHOW);
          element.addClass(CSS_CLASSES.ACTIVE);
        }
      }
    } else {
      // For other URLs
      if (href && href.indexOf(current) !== -1) {
        element.parents(SELECTORS.NAV_ITEM).last().addClass(CSS_CLASSES.ACTIVE);
        if (element.parents(SELECTORS.SUBMENU).length) {
          element.closest(SELECTORS.COLLAPSE).addClass(CSS_CLASSES.SHOW);
          element.addClass(CSS_CLASSES.ACTIVE);
        }
        if (element.parents('.submenu-item').length) {
          element.addClass(CSS_CLASSES.ACTIVE);
        }
      }
    }
  }

  $(SELECTORS.NAV_LINK, sidebar).each(function() {
    addActiveClass($(this));
  });
}
