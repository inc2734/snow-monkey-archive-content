# Snow Monkey Archive Content

![CI](https://github.com/inc2734/snow-monkey-archive-content/workflows/CI/badge.svg)

This is a plugin for Premium WordPress Theme Snow Monkey. You can display the contents of the page on the archive pages.

## Build

```bash
$ npm install
$ composer install
```

## Filter hooks

### snow_monkey_archive_content_enable_assignment_author

Disables assignment to author archives in the customizer.
Already assigned pages will not be unassigned, so unassign them in advance.

```
add_filter( 'snow_monkey_archive_content_enable_assignment_author', '__return_false' );
```

### snow_monkey_archive_content_enable_assignment_category

Disables assignment to category archives in the customizer.
Already assigned pages will not be unassigned, so unassign them in advance.

```
add_filter( 'snow_monkey_archive_content_enable_assignment_category', '__return_false' );
```

### snow_monkey_archive_content_enable_assignment_custom-post-archive

Disables assignment to custom post archives in the customizer.
Already assigned pages will not be unassigned, so unassign them in advance.

```
add_filter( 'snow_monkey_archive_content_enable_assignment_custom-post-archive', '__return_false' );
```

### snow_monkey_archive_content_enable_assignment_custom-taxonomy

Disables assignment to custom taxonomy archives in the customizer.
Already assigned pages will not be unassigned, so unassign them in advance.

```
add_filter( 'snow_monkey_archive_content_enable_assignment_custom-taxonomy', '__return_false' );
```

### snow_monkey_archive_content_enable_assignment_home

Disables assignment to home in the customizer.
Already assigned pages will not be unassigned, so unassign them in advance.

```
add_filter( 'snow_monkey_archive_content_enable_assignment_home', '__return_false' );
```

### snow_monkey_archive_content_enable_assignment_post-tag

Disables assignment to tag archives in the customizer.
Already assigned pages will not be unassigned, so unassign them in advance.

```
add_filter( 'snow_monkey_archive_content_enable_assignment_post-tag', '__return_false' );
```
