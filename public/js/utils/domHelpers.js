/**
 * DOM Helpers - Resilient DOM selection utilities
 */

/**
 * Find file upload input and browse button elements using data attributes
 * @param {HTMLElement} container - The container element (form group)
 * @returns {Object} Object with 'input' and 'browse' elements
 */
export function findFileUploadElements(container) {
  const fileInput = container.querySelector('[data-file-input]');
  const browseBtr = container.querySelector('[data-file-browse]');
  const formControl = container.querySelector('.form-control');

  return {
    input: fileInput,
    browse: browseBtr,
    display: formControl
  };
}
