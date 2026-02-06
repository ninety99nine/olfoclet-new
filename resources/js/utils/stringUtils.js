export function capitalize(value) {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
}

export function capitalizeAll(value) {
    if (!value) return '';
    return value.split(' ').map(word => {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
}

export function isNotEmpty(value) {
  return !isEmpty(value);
}

export function isEmpty(value) {

  // Handle null or undefined
  if (value == null) {
    return true;
  }

  // Handle strings
  if (typeof value === 'string') {
    return value.trim() === '';
  }

  // Handle objects (including arrays)
  if (typeof value === 'object') {
    return Object.keys(value).length === 0;
  }

  // Handle other types (numbers, booleans, etc.)
  return false;

}

/**
 * Formats milliseconds into a human-readable duration string
 * - Shows decimals for seconds only when no minutes are present and decimalPlaces > 0
 * - Always shows whole seconds when minutes/hours are included
 *
 * @param {number} milliseconds - Duration in milliseconds
 * @param {number} [decimalPlaces=0] - Number of decimal places for seconds (when applicable)
 * @returns {string} Formatted duration (e.g. "4.7s", "1m 23s", "45s", "—")
 */
export function formatDuration(milliseconds, decimalPlaces = 0) {
  // Handle invalid / zero / falsy input
  if (!milliseconds || milliseconds <= 0) {
    return '—';
  }

  const totalSeconds = milliseconds / 1000;

  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds - minutes * 60;   // keep fractional part

  if (minutes > 0) {
    // When minutes are present → show whole seconds (standard UX)
    const wholeSeconds = Math.floor(seconds);
    return `${minutes}m ${wholeSeconds}s`;
  }

  // Only seconds → apply decimal places if requested
  if (decimalPlaces > 0) {
    // Format with desired decimals, remove trailing zeros
    let formatted = seconds.toFixed(decimalPlaces);
    formatted = formatted.replace(/\.?0+$/, ''); // clean up trailing zeros & dot
    return `${formatted}s`;
  }

  // No decimals requested → whole seconds
  return `${Math.floor(seconds)}s`;
}

/**
 * Converts strings to camelCase (e.g., "first_name", "First-Name", "first name" -> "firstName")
 * @param {string} value
 * @returns {string}
 */
export function toCamelCase(value) {
    if (!value) return '';

    return value
        .replace(/[-_]+/g, ' ')          // Replace underscores and hyphens with spaces
        .replace(/[^\w\s]/g, '')         // Remove all non-word characters (except spaces)
        .replace(/\s+(.)/g, (_, char) => char.toUpperCase()) // Capitalize char after spaces
        .replace(/\s/g, '')              // Remove remaining spaces
        .replace(/^(.)/, char => char.toLowerCase());        // Ensure first char is lowercase
}
