# AI README - WordPress Schema System

This guide is designed specifically for AI assistants to help with the usage of the WordPress Schema System plugin.

## 🚀 Overview

WordPress Schema System lets you define custom post types, taxonomies, and ACF fields using simple YAML/JSON schemas. The system automatically registers them in WordPress and generates ACF field groups.

## 📁 Schema Directory Structure

**IMPORTANT:** Always place schemas in these directories:

- **Post Type Schemas:** `wp-content/schemas/post-types/`
- **Taxonomy Schemas:** `wp-content/schemas/taxonomies/`

The system does **NOT** load schemas from the plugin directory (`wp-content/plugins/ignis-schema-wp/schemas/`).

## 📝 Schema File Structure

### Post Type Schema Example (`wp-content/schemas/post-types/movie.yaml`)

```yaml
post_type: movie
label: Movies
singular_label: Movie
description: Movie catalog with ratings and cast
public: true
show_in_rest: true
menu_icon: dashicons-video-alt2
supports:
  - title
  - editor
  - thumbnail
has_archive: true

# Taxonomies for this post type
taxonomies:
  - movie-genre
  - movie-tag

fields:
  rating:
    type: number
    label: Rating (0-10)
    min: 0
    max: 10
    step: 0.1
    required: true

  release_date:
    type: date_picker
    label: Release Date
    display_format: "m/d/Y"
    return_format: "Y-m-d"

  trailer_url:
    type: url
    label: Trailer URL
    placeholder: "https://youtube.com/watch?v=..."
```

### Taxonomy Schema Example (`wp-content/schemas/taxonomies/movie-genre.yaml`)

```yaml
taxonomy: movie-genre
label: Movie Genres
singular_label: Movie Genre
description: Hierarchical movie genre classification
hierarchical: true
public: true
show_in_rest: true
show_admin_column: true

# Post types that use this taxonomy
post_types:
  - movie

fields:
  genre_color:
    type: color_picker
    label: Genre Color
    instructions: "Color used for genre badges"
    default_value: '#2271b1'

  genre_icon:
    type: image
    label: Genre Icon
    instructions: "Upload an icon for this genre (recommended: 64x64px)"
    return_format: url
    preview_size: thumbnail
```

## 🔄 Bidirectional Relationships

Relationships can be defined in either location:

**Option 1: In taxonomy schema**
```yaml
# wp-content/schemas/taxonomies/movie-genre.yaml
post_types:
  - movie
  - tv-show
```

**Option 2: In post type schema**
```yaml
# wp-content/schemas/post-types/movie.yaml
taxonomies:
  - movie-genre
  - movie-tag
```

The system automatically merges relationships from both locations.

## ⚠️ Important Requirements

1. **Advanced Custom Fields (ACF) plugin must be installed and active** - This is required for the schema system to work
2. PHP YAML parser extension OR Symfony YAML package (for YAML schema files)
3. Schema files must be placed in the correct directories (`wp-content/schemas/`)

## 📋 Supported Field Types

- `text`, `textarea`, `number`, `email`, `url`, `password`
- `select`, `checkbox`, `radio`, `true_false`
- `wysiwyg`, `oembed`, `image`, `file`, `gallery`
- `post_object`, `relationship`, `taxonomy`, `user`
- `repeater`, `group`, `flexible_content` (ACF Pro)
- `date_picker`, `time_picker`, `date_time_picker`
- `color_picker`, `google_map`, `link`

## 🧠 AI Usage Tips

When generating schemas:

1. Always use lowercase letters, numbers, and underscores for post_type/taxonomy slugs
2. Post type slugs must be 20 characters or less
3. Taxonomy slugs must be 32 characters or less
4. Place generated schemas in the appropriate directories
5. Remember that ACF fields will be automatically generated based on your schema fields
6. Always include `show_in_rest: true` if you want REST API access
7. Consider using `supports: [title, editor, thumbnail]` for typical post types

## 🧰 WP-CLI Commands (for validation and management)

```bash
# Validate schemas
wp schema validate movie --type=post-type
wp schema validate movie-genre --type=taxonomy

# List all schemas
wp schema list --type=all

# Export TypeScript types
wp schema export movie --type=post-type --output=./types
```