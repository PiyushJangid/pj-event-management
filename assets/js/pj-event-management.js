/**
 * PJ Event Management Frontend Scripts
 */

(function($) {
    'use strict';
    
    // Debug message to identify script loading
    console.log('PJ Event Management script loaded at', new Date().toISOString());
    
    // Check if script was loaded before to prevent duplicate handlers
    if (window.PJEventsInitialized) {
        console.warn('PJ Event Management script loaded multiple times. Preventing duplicate initialization.');
        return;
    }
    
    // Mark as initialized
    window.PJEventsInitialized = true;
    
    // Main configuration object
    const PJEvents = {
        config: {
            selectors: {
                container: '.pj-events-container, .pj-upcoming-events',
                grid: '.pj-events-grid',
                card: '.pj-event-card',
                infiniteScroll: '.pj-events-infinite-scroll',
                pagination: '.pj-events-pagination',
                paginationLinks: '.pj-events-pagination a.page-numbers',
                filterForm: '.pj-event-filter-form',
                filterSelect: '.pj-event-filter-select',
                filterItems: '.pj-event-filter-item'
            },
            classes: {
                visible: 'pj-event-visible',
                loading: 'is-loading'
            },
            animation: {
                duration: 400,
                scrollOffset: 60,
                staggerDelay: 100
            }
        },
        
        /**
         * Initialize all event functionality
         */
        init: function() {
            this.setupEventListeners();
            this.initFeatures();
        },
        
        /**
         * Set up all event listeners
         */
        setupEventListeners: function() {
            // Form submission for adding/editing events
            $('#pj-event-form').on('submit', this.handleFormSubmission);
            
            // Delete event functionality - use event delegation for dynamically loaded content
            $(document).on('click', '.pj-delete-event', this.showDeleteModal);
            $(document).on('click', '#pj-cancel-delete', this.hideDeleteModal);
            $(document).on('click', '#pj-confirm-delete', this.confirmDeleteEvent);
            $(document).on('click', '#pj-delete-event-modal', this.closeModalOnOutsideClick);
            
            // Auto-submit filter dropdown on change
            $(this.config.selectors.filterSelect).on('change', this.handleFilterChange);
            
            // Use event delegation for pagination links
            $(document).on('click', this.config.selectors.paginationLinks, this.handlePaginationClick);
        },
        
        /**
         * Initialize various features
         */
        initFeatures: function() {
            this.initLazyLoading();
            this.initFilters();
            this.initInfiniteScroll();
            this.animateEventCards();
        },
        
        /**
         * Enable image lazy loading if available
         */
        initLazyLoading: function() {
            if ('loading' in HTMLImageElement.prototype) {
                const images = document.querySelectorAll('.pj-event-thumbnail img');
                images.forEach(img => {
                    img.setAttribute('loading', 'lazy');
                });
            }
        },
        
        /**
         * Initialize filter functionality
         */
        initFilters: function() {
            if ($(this.config.selectors.filterItems).length) {
                $(this.config.selectors.filterItems).on('click', function(e) {
                    e.preventDefault();
                    
                    // Get filter value
                    const filterValue = $(this).data('filter');
                    
                    // Update active class
                    $(PJEvents.config.selectors.filterItems).removeClass('active');
                    $(this).addClass('active');
                    
                    // Filter events
                    if (filterValue === 'all') {
                        $(PJEvents.config.selectors.card).show();
                    } else {
                        $(PJEvents.config.selectors.card).hide();
                        $(PJEvents.config.selectors.card + '[data-category="' + filterValue + '"]').show();
                    }
                    
                    // Re-layout grid
                    $(PJEvents.config.selectors.grid).css('opacity', '0');
                    setTimeout(function() {
                        $(PJEvents.config.selectors.grid).css('opacity', '1');
                    }, 300);
                });
            }
        },
        
        /**
         * Handle filter dropdown change
         */
        handleFilterChange: function() {
            if ($(this).val()) {
                $(this).closest('form').submit();
            }
        },
        
        /**
         * Initialize infinite scroll functionality
         */
        initInfiniteScroll: function() {
            // Check for both shortcode and Elementor widget containers
            var $containers = $(this.config.selectors.infiniteScroll);
            
            if ($containers.length === 0) {
                // Try to find any Elementor widgets with our content
                $containers = $('.elementor-widget-container ' + this.config.selectors.infiniteScroll);
                if ($containers.length === 0) {
                    return;
                }
            }
            
            // Process each container
            $containers.each(function() {
                var $container = $(this);
                var $status = $container.find('.pj-infinite-scroll-status');
                var $grid = $container.parent().find(PJEvents.config.selectors.grid);
                var currentPage = parseInt($container.data('page'), 10) || 1;
                var maxPages = parseInt($container.data('max'), 10) || 1;
                
                // Hide status messages initially
                $status.find('.pj-infinite-scroll-last').hide();
                $status.find('.pj-infinite-scroll-error').hide();
                $status.find('.pj-infinite-scroll-request').hide();
                
                // Don't show loading indicator if we're already at max pages
                if (currentPage >= maxPages) {
                    $status.find('.pj-infinite-scroll-last').show();
                    return;
                }
                
                // Setup intersection observer for auto-loading
                if ('IntersectionObserver' in window) {
                    var loadMoreObserver = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            // Only trigger loading if the element is intersecting the viewport
                            // and we haven't reached the max pages
                            if (entry.isIntersecting && !$container.data('loading') && currentPage < maxPages) {
                                PJEvents.loadMoreEvents($container, $grid, $status, currentPage, maxPages);
                            }
                        });
                    }, {
                        rootMargin: '0px 0px 600px 0px' // Load when 600px from viewport
                    });
                    
                    loadMoreObserver.observe($container[0]);
                }
            });
        },
        
        /**
         * Load more events via AJAX
         */
        loadMoreEvents: function($container, $grid, $status, currentPage, maxPages) {
            // Don't load if already loading or at max pages
            if ($container.data('loading') || currentPage >= maxPages) {
                return;
            }
            
            $container.data('loading', true);
            $status.find('.pj-infinite-scroll-request').show();
            
            // Get current URL and parameters
            var url = window.location.href;
            var params = new URLSearchParams(window.location.search);
            
            // Update page parameter for the next page
            params.set('paged', currentPage + 1);
            
            // Get existing event IDs to prevent duplicates
            var existingEventIds = [];
            $(this.config.selectors.card).each(function() {
                var eventId = $(this).data('event-id');
                if (eventId) {
                    existingEventIds.push(eventId);
                }
            });
            
            // Make ajax call
            $.ajax({
                url: url.split('?')[0] + '?' + params.toString(),
                type: 'GET',
                success: function(response) {
                    var $html = $(response);
                    var $newItems = $html.find(PJEvents.config.selectors.grid + ' ' + PJEvents.config.selectors.card);
                    var uniqueItemsAdded = false;
                    
                    // Filter out duplicates and append new items
                    if ($newItems.length > 0) {
                        // Prepare new items, skipping any that already exist
                        var $uniqueItems = $newItems.filter(function() {
                            var eventId = $(this).data('event-id');
                            return !eventId || existingEventIds.indexOf(eventId) === -1;
                        });
                        
                        if ($uniqueItems.length > 0) {
                            // Add new items to the grid
                            $uniqueItems.each(function() {
                                $(this).removeClass(PJEvents.config.classes.visible);
                            });
                            
                            $grid.append($uniqueItems);
                            uniqueItemsAdded = true;
                            
                            // Update page counter
                            currentPage++;
                            $container.data('page', currentPage);
                            
                            // Animate new cards after a short delay
                            setTimeout(function() {
                                PJEvents.animateEventCards();
                            }, 50);
                        }
                    }
                    
                    // If no new unique items were added or we've reached max pages, show end message
                    if (!uniqueItemsAdded || currentPage >= maxPages) {
                        $status.find('.pj-infinite-scroll-last').show();
                    }
                },
                error: function() {
                    // Show error message
                    $status.find('.pj-infinite-scroll-error').show();
                },
                complete: function() {
                    // Reset loading state
                    $container.data('loading', false);
                    $status.find('.pj-infinite-scroll-request').hide();
                }
            });
        },
        
        /**
         * Animate event cards on scroll
         */
        animateEventCards: function() {
            const cards = document.querySelectorAll(this.config.selectors.card + ':not(.' + this.config.classes.visible + ')');
            
            if (cards.length === 0) {
                return; // No cards to animate
            }
            
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // Add a small staggered delay based on the card's position for a nicer effect
                            const delay = Array.from(cards).indexOf(entry.target) * this.config.animation.staggerDelay;
                            
                            setTimeout(() => {
                                entry.target.classList.add(this.config.classes.visible);
                            }, delay);
                            
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.15,
                    rootMargin: '0px 0px 50px 0px'
                });
                
                cards.forEach(card => {
                    observer.observe(card);
                });
            } else {
                // Fallback for browsers that don't support IntersectionObserver
                // Add a small delay to each card for a staggered animation effect
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add(this.config.classes.visible);
                    }, index * this.config.animation.staggerDelay);
                });
            }
        },
        
        /**
         * Handle pagination click for smooth scrolling
         */
        handlePaginationClick: function(e) {
            // Don't interrupt the actual pagination if modifier keys are pressed
            if (e.ctrlKey || e.metaKey || e.shiftKey) {
                return true;
            }
            
            // Find the closest events container
            var $container = $(this).closest(PJEvents.config.selectors.container);
            
            // Only apply smooth scroll if we found a container
            if ($container.length > 0) {
                // Calculate position (account for fixed headers or use data attribute)
                var offset = parseInt($container.data('scroll-offset') || PJEvents.config.animation.scrollOffset);
                var scrollPos = $container.offset().top - offset;
                
                // Smooth scroll animation
                $('html, body').animate({
                    scrollTop: scrollPos
                }, PJEvents.config.animation.duration);
            }
        },
        
        /**
         * Handle form submission (add/edit event)
         */
        handleFormSubmission: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $response = $('#pj-form-response');
            const isEdit = $form.find('input[name="post_id"]').length > 0;
            
            // Prevent duplicate submissions
            if ($form.data('submitting')) {
                console.warn('Form submission already in progress. Preventing duplicate submission.');
                return;
            }
            
            // Mark form as being submitted
            $form.data('submitting', true);
            
            // Show loading state
            $form.find('#pj-submit-event').prop('disabled', true).text(isEdit ? 'Updating...' : 'Adding...');
            
            // Get form data
            const formData = new FormData($form[0]);
            formData.append('action', isEdit ? 'pj_edit_event' : 'pj_add_event');
            formData.append('nonce', pj_event_management.nonce);
            
            // Submit via AJAX
            $.ajax({
                url: pj_event_management.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Reset button state
                    $form.find('#pj-submit-event').prop('disabled', false).text(isEdit ? 'Update Event' : 'Add Event');
                    
                    if (response.success) {
                        // Show success message
                        $response.removeClass('error').addClass('success').html(response.data.message).show();
                        
                        // Reset form if adding new event
                        if (!isEdit) {
                            $form[0].reset();
                            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('pj_event_content')) {
                                tinyMCE.get('pj_event_content').setContent('');
                            }
                        }
                        
                        // Removed auto-redirect to keep user on the form page after submission
                    } else {
                        // Show error message
                        $response.removeClass('success').addClass('error').html(response.data.message).show();
                    }
                    
                    // Reset submission flag
                    $form.data('submitting', false);
                },
                error: function(xhr, status, error) {
                    // Reset button state
                    $form.find('#pj-submit-event').prop('disabled', false).text(isEdit ? 'Update Event' : 'Add Event');
                    
                    // Show error message
                    $response.removeClass('success').addClass('error').html('An error occurred while processing your request.').show();
                    
                    // Reset submission flag
                    $form.data('submitting', false);
                }
            });
        },
        
        /**
         * Show delete confirmation modal
         */
        showDeleteModal: function(e) {
            e.preventDefault();
            PJEvents.eventToDelete = $(this).data('id');
            $('#pj-delete-event-modal').fadeIn(200);
        },
        
        /**
         * Hide delete confirmation modal
         */
        hideDeleteModal: function() {
            $('#pj-delete-event-modal').fadeOut(200);
            PJEvents.eventToDelete = null;
        },
        
        /**
         * Close modal when clicking outside
         */
        closeModalOnOutsideClick: function(e) {
            if (e.target === this) {
                PJEvents.hideDeleteModal();
            }
        },
        
        /**
         * Confirm delete event
         */
        confirmDeleteEvent: function() {
            if (!PJEvents.eventToDelete) {
                alert('No event ID found to delete');
                return;
            }
            
            // Show loading state
            var $button = $(this);
            $button.prop('disabled', true).text('Deleting...');
            
            console.log('Deleting event ID:', PJEvents.eventToDelete);
            
            // Debug info
            if (!pj_event_management || !pj_event_management.ajax_url) {
                console.error('AJAX URL not found. pj_event_management object:', pj_event_management);
                alert('Error: AJAX URL not found. Plugin script not properly loaded.');
                $button.prop('disabled', false).text('Delete');
                return;
            }
            
            // Send delete request via AJAX
            $.ajax({
                url: pj_event_management.ajax_url,
                type: 'POST',
                data: {
                    action: 'pj_delete_event',
                    post_id: PJEvents.eventToDelete,
                    nonce: pj_event_management.nonce
                },
                success: function(response) {
                    console.log('Delete response:', response);
                    
                    // Hide modal
                    PJEvents.hideDeleteModal();
                    
                    if (response.success) {
                        // Try to find the row by different selectors
                        var $row = $('tr[data-event-id="' + PJEvents.eventToDelete + '"]');
                        
                        // If row found, remove it
                        if ($row.length) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                
                                // If no events left, show the "no events" message
                                if ($('.pj-events-table tbody tr').length === 0) {
                                    $('.pj-events-table').replaceWith('<p class="pj-no-events">No events found.</p>');
                                }
                            });
                        } else {
                            // If row not found by ID, try to refresh the page
                            console.warn('Row not found for ID:', PJEvents.eventToDelete);
                            alert('Event deleted successfully. Refreshing page to update the list.');
                            window.location.reload();
                        }
                    } else {
                        alert(response.data?.message || 'Error deleting event');
                    }
                    
                    // Reset button state
                    $button.prop('disabled', false).text('Delete');
                },
                error: function(xhr, status, error) {
                    console.error('Delete error:', error, xhr.responseText);
                    
                    // Hide modal
                    PJEvents.hideDeleteModal();
                    
                    // Show error message
                    alert('An error occurred while deleting the event: ' + error);
                    
                    // Reset button state
                    $button.prop('disabled', false).text('Delete');
                }
            });
        }
    };
    
    // Helper function for date formatting
    window.pjFormatDate = function(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString(document.documentElement.lang || 'en-US', options);
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        PJEvents.init();
    });

})(jQuery); 