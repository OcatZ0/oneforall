/**
 * Sidebar Collapse - Handles sidebar collapse/expand behavior
 */
import { SELECTORS, CSS_CLASSES } from '../utils/constants.js';

export function initSidebar() {
  const sidebar = $(SELECTORS.SIDEBAR);
  const body = $(SELECTORS.BODY);

  // Close other submenus when opening one
  sidebar.on('show.bs.collapse', SELECTORS.COLLAPSE, function() {
    sidebar.find(SELECTORS.COLLAPSE + '.' + CSS_CLASSES.SHOW).collapse('hide');
  });

  // Toggle sidebar visibility on minimize button click
  $(SELECTORS.MINIMIZE_BTN).on("click", function() {
    if (body.hasClass('sidebar-toggle-display')) {
      body.toggleClass(CSS_CLASSES.SIDEBAR_HIDDEN);
    } else {
      body.toggleClass(CSS_CLASSES.SIDEBAR_ICON_ONLY);
    }
  });
}
