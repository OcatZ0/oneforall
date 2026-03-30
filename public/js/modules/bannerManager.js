/**
 * Banner Manager - Handles promotional banner visibility with cookie persistence
 */
import { UI, SELECTORS, CSS_CLASSES } from '../utils/constants.js';

export function initBannerManager() {
  const proBanner = document.querySelector(SELECTORS.PRO_BANNER);
  const bannerClose = document.querySelector(SELECTORS.BANNER_CLOSE);

  if (!proBanner || !bannerClose) {
    return;
  }

  // Check cookie and show/hide banner accordingly
  const cookieValue = $.cookie(UI.BANNER_COOKIE_NAME);

  if (cookieValue !== "true") {
    proBanner.classList.add(CSS_CLASSES.DISPLAY_FLEX);
  } else {
    proBanner.classList.add(CSS_CLASSES.DISPLAY_NONE);
  }

  // Handle banner close button
  bannerClose.addEventListener('click', function() {
    proBanner.classList.add(CSS_CLASSES.DISPLAY_NONE);
    proBanner.classList.remove(CSS_CLASSES.DISPLAY_FLEX);

    // Set cookie to expire in 24 hours
    const date = new Date();
    date.setTime(date.getTime() + UI.BANNER_COOKIE_EXPIRATION_MS);
    $.cookie(UI.BANNER_COOKIE_NAME, "true", { expires: date });
  });
}
