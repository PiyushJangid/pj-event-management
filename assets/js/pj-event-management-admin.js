(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Initialize date and time pickers for metabox
        if ($.fn.datepicker && $('#pj_event_date').length) {
            $('#pj_event_date').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        }
        
        if ($.fn.timepicker && $('#pj_event_time').length) {
            $('#pj_event_time').timepicker({
                timeFormat: 'HH:mm'
            });
        }
        
    });
    
})(jQuery); 