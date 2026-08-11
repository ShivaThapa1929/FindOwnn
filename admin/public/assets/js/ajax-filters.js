/**
 * Universal AJAX Filtering System
 * Converts any page with filters/search to use AJAX instead of page reloads
 * 
 * Usage: Add data-ajax-container="containerId" to the container that should be updated
 */

(function() {
  'use strict';

  // Configuration
  const config = {
    containerSelector: '[data-ajax-container]',
    searchInputDelay: 500,
    loadingClass: 'ajax-loading'
  };

  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll(config.containerSelector);
    
    containers.forEach(container => {
      const containerId = container.getAttribute('data-ajax-container');
      console.log('Initializing AJAX filters for:', containerId);
      
      // Find all form elements
      const searchInputs = container.querySelectorAll('input[type="text"][name="search"]');
      const filterSelects = container.querySelectorAll('select');
      const filterButtons = container.querySelectorAll('[data-ajax-filter]');
      const clearButtons = container.querySelectorAll('[data-ajax-clear]');
      const forms = container.querySelectorAll('form');
      
      let searchTimeout;
      
      // Prevent form submissions
      forms.forEach(form => {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          fetchResults(container);
        });
      });
      
      // Search inputs with debounce
      searchInputs.forEach(input => {
        input.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => fetchResults(container), config.searchInputDelay);
        });
        
        input.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchResults(container);
          }
        });
      });
      
      // Filter selects
      filterSelects.forEach(select => {
        select.addEventListener('change', () => fetchResults(container));
      });
      
      // Filter buttons (like role filters)
      filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const filterName = this.getAttribute('data-ajax-filter');
          const filterValue = this.getAttribute('data-ajax-value');
          
          // Update active state
          container.querySelectorAll(`[data-ajax-filter="${filterName}"]`).forEach(btn => {
            btn.classList.remove('btn-primary', 'active');
            btn.classList.add('btn-outline-secondary');
          });
          this.classList.remove('btn-outline-secondary');
          this.classList.add('btn-primary', 'active');
          
          fetchResults(container, { [filterName]: filterValue });
        });
      });
      
      // Clear buttons
      clearButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Clear all inputs
          searchInputs.forEach(input => input.value = '');
          filterSelects.forEach(select => {
            if (select.name === 'status' || select.name === 'role') {
              select.value = 'all';
            } else if (select.name === 'sort') {
              select.value = 'newest';
            } else {
              select.value = '';
            }
          });
          
          fetchResults(container);
        });
      });
      
      // Handle filter tag removals
      document.addEventListener('click', function(e) {
        const removeLink = e.target.closest('[data-ajax-remove]');
        if (removeLink && container.contains(removeLink)) {
          e.preventDefault();
          const filterType = removeLink.getAttribute('data-ajax-remove');
          
          // Clear the specific filter
          const input = container.querySelector(`input[name="${filterType}"]`);
          const select = container.querySelector(`select[name="${filterType}"]`);
          
          if (input) {
            input.value = '';
          } else if (select) {
            if (filterType === 'status' || filterType === 'role') {
              select.value = 'all';
            } else if (filterType === 'sort') {
              select.value = 'newest';
            } else {
              select.value = '';
            }
          }
          
          fetchResults(container);
        }
      });
    });
  });
  
  /**
   * Fetch results via AJAX
   */
  function fetchResults(container, extraParams = {}) {
    const containerId = container.getAttribute('data-ajax-container');
    const resultContainer = document.getElementById(containerId);
    
    if (!resultContainer) {
      console.error('Result container not found:', containerId);
      return;
    }
    
    // Show loading state
    container.classList.add(config.loadingClass);
    resultContainer.style.opacity = '0.5';
    
    // Build query parameters
    const params = new URLSearchParams(extraParams);
    
    // Get all input values
    const inputs = container.querySelectorAll('input[type="text"], input[type="hidden"], select');
    inputs.forEach(input => {
      if (input.name && input.value && input.value !== 'all' && input.value !== '') {
        // Skip default values
        if (input.name === 'sort' && input.value === 'newest') return;
        if (input.name === 'status' && input.value === 'all') return;
        if (input.name === 'role' && input.value === 'all') return;
        
        params.append(input.name, input.value);
      }
    });
    
    // Add AJAX flag
    params.append('ajax', '1');
    
    const url = window.location.pathname + '?' + params.toString();
    console.log('Fetching:', url);
    
    // Fetch data
    fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html'
      },
      credentials: 'same-origin'
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.text();
    })
    .then(html => {
      // Parse response
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      // Update container
      const newContent = doc.getElementById(containerId);
      if (newContent) {
        resultContainer.innerHTML = newContent.innerHTML;
        resultContainer.style.opacity = '1';
        console.log('Content updated');
      } else {
        console.error('Container not found in response:', containerId);
      }
      
      // Update URL without reload
      const displayParams = new URLSearchParams(params);
      displayParams.delete('ajax');
      const newUrl = displayParams.toString() ? '?' + displayParams.toString() : window.location.pathname;
      window.history.pushState({}, '', newUrl);
    })
    .catch(error => {
      console.error('Fetch error:', error);
      alert('Error loading results. Please try again.');
      resultContainer.style.opacity = '1';
    })
    .finally(() => {
      container.classList.remove(config.loadingClass);
    });
  }
  
  // Make it globally available
  window.AjaxFilters = {
    refresh: function(containerId) {
      const container = document.querySelector(`[data-ajax-container="${containerId}"]`);
      if (container) {
        fetchResults(container);
      }
    }
  };
})();
