/**
 * File Upload - Handles file input display
 *
 * Expected HTML structure:
 * <div class="file-upload-group" data-file-upload-group>
 *   <input type="file" class="file-upload-default" data-file-input>
 *   <input type="text" class="form-control">
 *   <button class="file-upload-browse" data-file-browse>Browse</button>
 * </div>
 */
import { findFileUploadElements } from './utils/domHelpers.js';

(function($) {
  'use strict';
  $(function() {
    // Handle browse button clicks
    $('[data-file-browse]').on('click', function() {
      const container = $(this).closest('[data-file-upload-group]')[0];
      if (!container) return;

      const elements = findFileUploadElements(container);
      if (elements.input) {
        elements.input.click();
      }
    });

    // Handle file input changes
    $('[data-file-input]').on('change', function() {
      const container = $(this).closest('[data-file-upload-group]')[0];
      if (!container) return;

      const elements = findFileUploadElements(container);
      if (elements.display) {
        // Remove fake path (Windows security feature)
        const filename = this.value.replace(/C:\\fakepath\\/i, '');
        $(elements.display).val(filename);
      }
    });
  });
})(jQuery);
