/**
 * Alpine.js initialization
 * Registers plugins and starts Alpine
 */

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Register plugins
Alpine.plugin(collapse);

// Start Alpine
Alpine.start();

// Export for use in other modules if needed
export default Alpine;
