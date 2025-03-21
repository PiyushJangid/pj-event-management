# PJ Event Management

A comprehensive WordPress plugin for creating, managing, and displaying events on your website.

## Features

- **Event Management**: Create, edit, and delete events with custom details like date, time, and location
- **Flexible Display Options**: Show events in grids or lists with customizable layouts
- **Filtering Capabilities**: Filter events by upcoming, past, or all events
- **Elementor Integration**: Dedicated Elementor widget for seamless page builder integration
- **Shortcodes**: Multiple shortcodes for displaying events anywhere on your site
- **Responsive Design**: Mobile-friendly layouts that adapt to any screen size
- **User Authorization**: Control who can manage events on your site
- **Customizable Templates**: Easily style events to match your website's design

## Installation

1. Download the plugin zip file
2. Go to your WordPress admin → Plugins → Add New
3. Click "Upload Plugin" and select the downloaded zip file
4. Activate the plugin

## Quick Start

### Display Events with Shortcode

Add this shortcode to any page or post to display upcoming events:

```
[pj_events]
```

### Display Events with Elementor

1. Edit a page with Elementor
2. Search for "PJ Events" in the widget panel
3. Drag the widget to your desired location
4. Customize display options in the widget settings

## Shortcodes

### Basic Shortcode
Display upcoming events with default settings:
```
[pj_events]
```

#### Shortcode with Options
```
[pj_events per_page="6" title="Upcoming Events" columns="3" show_filter_toggle="yes"]
```

### Event Management Shortcode

For admin users to manage events. Displays 10 events per page by default.

```
[pj_all_events_management]
```

#### Parameters:

- `per_page` - Number of events to display (default: 10)
- `title` - Optional heading for the management section
- `pagination` - Pagination style (default: "standard")

Example:
```
[pj_all_events_management per_page="15" title="Manage Events"]
```

### Add/Edit Event Shortcode

Create a form for adding or editing events:

```
[pj_add_edit_event]
```

## Elementor Widget

The plugin adds a "PJ Events" widget to Elementor with the following options:

### Content Options

- **Widget Title**: Optional heading for the events section
- **Date Filter**: Show upcoming, past, or all events
- **Posts Per Page**: Number of events to display
- **Columns**: Number of columns in the grid (1-4)
- **Pagination Type**: Standard pagination, load more button, infinite scroll, or none
- **Show Filter Toggle**: Enable filtering between upcoming, past, and all events
- **Display Options**: Toggle visibility of date, time, location, and excerpt

### Style Options

- **Title Color**: Color for the widget title
- **Event Title Color**: Color for individual event titles
- **Meta Info Color**: Color for date, time, and location information
- **Button Colors**: Customize button background and text colors
- **Spacing**: Control spacing between events

## Plugin Settings

Access plugin settings at **Events → Settings** in your WordPress admin.

### General Settings

- **Events Per Page**: Default number of events to display (used when not specified in shortcode)
- **Date Format**: Format for displaying event dates
- **Time Format**: Format for displaying event times
- **Enable Caching**: Cache events to improve performance
- **User Roles**: Select which user roles can manage events

### Display Settings

- **Layout**: Default layout for events display
- **Image Size**: Default size for event featured images
- **Excerpt Length**: Number of words to show in excerpts
- **Read More Text**: Text for the "read more" button

## Advanced Usage

### Custom Templates

To override the default templates, copy the template files from the plugin's `templates` directory to your theme in a folder named `pj-event-management`.

### CSS Customization

The plugin includes these CSS classes for styling:

- `.pj-upcoming-events` - Main container
- `.pj-events-title` - Section title
- `.pj-events-grid` - Events grid container
- `.pj-event-card` - Individual event container
- `.pj-event-thumbnail` - Event featured image
- `.pj-event-title` - Event title
- `.pj-event-meta` - Container for date, time, location
- `.pj-event-date` - Event date
- `.pj-event-time` - Event time
- `.pj-event-location` - Event location
- `.pj-event-excerpt` - Event excerpt
- `.pj-event-toggle-filter` - Filter toggle buttons

### JavaScript Events

The plugin triggers these events for developers:

- `pj_events_loaded` - Fired after events are loaded
- `pj_events_filtered` - Fired after events are filtered
- `pj_more_events_loaded` - Fired after more events are loaded via AJAX

Example usage:
```javascript
jQuery(document).on('pj_events_loaded', function() {
  console.log('Events have been loaded');
});
```

### Hooks and Filters

For developers, the plugin provides these hooks:

#### Actions:
- `pj_event_before_render` - Before events are rendered
- `pj_event_after_render` - After events are rendered
- `pj_event_before_form` - Before the add/edit form is displayed
- `pj_event_after_form` - After the add/edit form is displayed
- `pj_event_saved` - After an event is saved

#### Filters:
- `pj_event_query_args` - Modify event query arguments
- `pj_event_display_date` - Modify the displayed date format
- `pj_event_display_time` - Modify the displayed time format
- `pj_event_excerpt_length` - Modify excerpt length
- `pj_event_meta_fields` - Modify event meta fields

## Troubleshooting

### Common Issues

1. **Events not displaying**: Check if date filter is set correctly
2. **Styling issues**: Make sure your theme CSS is not conflicting
3. **Pagination not working**: Ensure permalinks are set correctly
4. **Images not displaying**: Check if featured images are set for events
5. **Event deletion not working**: Clear browser cache or check console for errors
6. **AJAX operations failing**: Verify nonce configuration and user permissions

### Support

For additional help:
1. Check the plugin documentation
2. Contact support at support@piyushjangid.in

## Credits

Developed by Piyush Jangid
Website: https://piyushjangid.in 