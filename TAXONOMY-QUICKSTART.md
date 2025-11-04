# Taxonomy Management Quick Start Guide

This guide will help you quickly get started with taxonomy management in ignis-schema-wp.

## Table of Contents
- [Installation](#installation)
- [Basic Concepts](#basic-concepts)
- [Creating Your First Taxonomy](#creating-your-first-taxonomy)
- [Hierarchical vs Flat Taxonomies](#hierarchical-vs-flat-taxonomies)
- [Adding ACF Fields to Taxonomies](#adding-acf-fields-to-taxonomies)
- [Connecting Taxonomies to Post Types](#connecting-taxonomies-to-post-types)
- [Working with Terms](#working-with-terms)
- [TypeScript Integration](#typescript-integration)
- [Common Patterns](#common-patterns)

---

## Installation

The taxonomy feature is built into ignis-schema-wp. No additional installation required.

**Requirements:**
- WordPress 5.0+
- ACF (Advanced Custom Fields) plugin
- PHP 7.4+ with YAML extension (or symfony/yaml)
- WP-CLI (for command-line operations)

---

## Basic Concepts

### What is a Taxonomy?

A taxonomy is a way to group and classify content in WordPress. Examples:
- **Categories** (hierarchical) - Electronics > Computers > Laptops
- **Tags** (flat) - "new", "sale", "featured"
- **Custom taxonomies** - Product types, Locations, Skills, etc.

### Schema-Based Approach

With ignis-schema-wp, you define taxonomies in YAML/JSON files:
- **Location:** `wp-content/schemas/taxonomies/your-taxonomy.yaml`
- **Benefits:** Version control, AI-friendly, type-safe, portable

---

## Creating Your First Taxonomy

### Step 1: Create the Schema File

Create `wp-content/schemas/taxonomies/movie-genre.yaml`:

```yaml
taxonomy: movie-genre
label: Movie Genres
singular_label: Movie Genre
description: Classification system for movies by genre
public: true
show_in_rest: true
hierarchical: true

rewrite:
  slug: genres
  with_front: false

post_types:
  - movie

fields:
  genre_color:
    type: color_picker
    label: Genre Color
    instructions: Choose a color to represent this genre
    default_value: '#2271b1'

rest_api:
  enabled: true
```

### Step 2: Validate the Schema

```bash
wp schema validate movie-genre --type=taxonomy
```

### Step 3: Register the Taxonomy

The taxonomy is automatically registered on WordPress init. Alternatively, manually trigger:

```bash
wp schema register --type=taxonomy
```

### Step 4: Verify Registration

```bash
wp schema list --type=taxonomy
wp taxonomy list | grep movie-genre
```

---

## Hierarchical vs Flat Taxonomies

### Hierarchical Taxonomies (Categories)

Use for parent-child relationships:

```yaml
taxonomy: location
label: Locations
hierarchical: true  # Enables parent-child

# Example structure:
# World
#   ├─ North America
#   │   ├─ USA
#   │   └─ Canada
#   └─ Europe
#       ├─ UK
#       └─ France
```

**Features:**
- Parent-child relationships
- Nested structure
- Hierarchical URL structure

### Flat Taxonomies (Tags)

Use for simple labels without hierarchy:

```yaml
taxonomy: mood
label: Moods
hierarchical: false  # No parent-child

# Example: happy, sad, excited, calm, energetic
```

**Features:**
- No parent-child
- Flat structure
- Tag cloud display
- Quick selection interface

---

## Adding ACF Fields to Taxonomies

### Basic Field Types

```yaml
fields:
  # Image field
  icon:
    type: image
    label: Icon
    return_format: url
    preview_size: thumbnail

  # Color picker
  color:
    type: color_picker
    label: Color
    default_value: '#ffffff'

  # Text field
  tagline:
    type: text
    label: Tagline
    maxlength: 100

  # Number field
  priority:
    type: number
    label: Display Priority
    default_value: 0
    min: 0
    max: 100

  # True/False toggle
  featured:
    type: true_false
    label: Featured
    ui: true
    ui_on_text: 'Yes'
    ui_off_text: 'No'

  # Select dropdown
  visibility:
    type: select
    label: Visibility
    choices:
      public: Public
      private: Private
      hidden: Hidden
    default_value: public

  # Textarea
  description:
    type: textarea
    label: Description
    rows: 4
    maxlength: 500
```

### Advanced Field Types

```yaml
fields:
  # Repeater for multiple items
  features:
    type: repeater
    label: Key Features
    layout: table
    button_label: Add Feature
    sub_fields:
      feature_name:
        type: text
        label: Feature
      feature_icon:
        type: text
        label: Icon Class

  # Group for related fields
  seo:
    type: group
    label: SEO Settings
    layout: block
    sub_fields:
      meta_title:
        type: text
        label: Meta Title
      meta_description:
        type: textarea
        label: Meta Description
        rows: 3

  # Image gallery
  gallery:
    type: gallery
    label: Image Gallery
    min: 1
    max: 10

  # WYSIWYG editor
  long_description:
    type: wysiwyg
    label: Long Description
    tabs: all
    toolbar: full
    media_upload: true
```

---

## Connecting Taxonomies to Post Types

### Method 1: Define in Taxonomy Schema

```yaml
# taxonomies/product-category.yaml
taxonomy: product-category
label: Product Categories
post_types:
  - product
  - variant
```

### Method 2: Define in Post Type Schema

```yaml
# post-types/product.yaml
post_type: product
label: Products
taxonomies:
  - product-category
  - product-tag
```

### Method 3: Both (Recommended)

Define in both places - the system automatically merges them:

```yaml
# taxonomies/product-category.yaml
post_types:
  - product

# post-types/product.yaml
taxonomies:
  - product-category
```

**Benefit:** Clear documentation of relationships from both perspectives.

---

## Working with Terms

### Create Terms via CLI

```bash
# Create a parent term
wp term create movie-genre "Action" --description="Action-packed movies"

# Create a child term
wp term create movie-genre "Superhero" --parent=1 --description="Superhero movies"

# Create multiple terms
wp term create movie-genre "Comedy"
wp term create movie-genre "Drama"
wp term create movie-genre "Sci-Fi"
```

### List Terms

```bash
wp term list movie-genre --format=table
```

### Update Term Meta (ACF)

```bash
# Set ACF field value
wp term meta update 1 genre_color "#ff0000"
```

### Assign Terms to Posts

```bash
# Assign term to post
wp post term add 123 movie-genre 1

# List post's terms
wp post term list 123 movie-genre
```

---

## TypeScript Integration

### Generate Types

```bash
# Generate for single taxonomy
wp schema export movie-genre --type=taxonomy --output=./src/types

# Generate all types
wp schema export_all --type=all --output=./src/types
```

### Use in Frontend Code

```typescript
import { MovieGenre, MovieGenreACF } from './types/taxonomies/movie-genre';

// Type-safe term object
const genre: MovieGenre = {
  term_id: 1,
  name: 'Action',
  slug: 'action',
  taxonomy: 'movie-genre',
  description: 'Action-packed movies',
  parent: 0,
  count: 42,
  acf: {
    genre_color: '#ff0000'
  }
};

// API request with types
async function fetchGenres(): Promise<MovieGenre[]> {
  const response = await fetch('/wp-json/wp/v2/movie-genre');
  return response.json();
}

// Create new term with type safety
const createRequest: MovieGenreCreateRequest = {
  name: 'Horror',
  slug: 'horror',
  description: 'Scary movies',
  acf: {
    genre_color: '#000000'
  }
};
```

---

## Common Patterns

### E-Commerce Product Categorization

```yaml
taxonomy: product-category
label: Product Categories
hierarchical: true
post_types: [product]

fields:
  category_image:
    type: image
    label: Category Image
    return_format: url

  category_icon:
    type: image
    label: Category Icon
    return_format: url

  featured_products:
    type: relationship
    label: Featured Products
    post_type: [product]
    max: 8

  banner:
    type: image
    label: Banner Image
    return_format: url

  seo_description:
    type: textarea
    label: SEO Description
    rows: 3
```

### Blog Post Topics with Tags

```yaml
taxonomy: topic
label: Topics
hierarchical: false
post_types: [post]

fields:
  topic_color:
    type: color_picker
    label: Badge Color

  topic_icon:
    type: text
    label: Icon Class
    placeholder: 'fas fa-tag'

  related_topics:
    type: relationship
    label: Related Topics
    post_type: [topic]
    max: 5
```

### Location-Based Classification

```yaml
taxonomy: location
label: Locations
hierarchical: true
post_types: [event, venue, restaurant]

fields:
  coordinates:
    type: group
    label: Coordinates
    sub_fields:
      latitude:
        type: number
        label: Latitude
        step: 0.000001
      longitude:
        type: number
        label: Longitude
        step: 0.000001

  timezone:
    type: select
    label: Timezone
    choices:
      utc: UTC
      est: Eastern
      cst: Central
      pst: Pacific

  location_image:
    type: image
    label: Location Image
```

### Skill Level System

```yaml
taxonomy: skill-level
label: Skill Levels
hierarchical: true
post_types: [course, tutorial, lesson]

fields:
  difficulty_number:
    type: number
    label: Difficulty (1-10)
    min: 1
    max: 10

  difficulty_color:
    type: color_picker
    label: Display Color

  estimated_hours:
    type: number
    label: Estimated Learning Hours

  prerequisites:
    type: relationship
    label: Prerequisites
    post_type: [course]
```

### Multi-Language Content Tags

```yaml
taxonomy: content-tag
label: Content Tags
hierarchical: false
post_types: [post, page, product]

fields:
  tag_translations:
    type: repeater
    label: Translations
    layout: table
    sub_fields:
      language:
        type: select
        label: Language
        choices:
          en: English
          es: Spanish
          fr: French
          de: German
      translation:
        type: text
        label: Translation

  tag_category:
    type: select
    label: Tag Category
    choices:
      feature: Feature
      benefit: Benefit
      technical: Technical
      marketing: Marketing
```

---

## Best Practices

### 1. Naming Conventions

```yaml
# Good
taxonomy: product-category
taxonomy: content-tag
taxonomy: skill-level

# Avoid
taxonomy: ProductCategory  # No camelCase
taxonomy: product_category # Use hyphens, not underscores
taxonomy: prod-cat         # Be descriptive
```

### 2. Hierarchical Choice

**Use Hierarchical (true) when:**
- Content has clear parent-child relationships
- You need nested categorization
- Example: Geographic locations, product categories

**Use Flat (false) when:**
- Tags are independent labels
- No relationships between terms
- Example: Keywords, moods, colors

### 3. Field Organization

```yaml
fields:
  # Group related fields logically

  # Visual/Display fields
  icon: { type: image }
  color: { type: color_picker }
  banner: { type: image }

  # Content fields
  tagline: { type: text }
  description: { type: textarea }

  # SEO fields
  seo_title: { type: text }
  seo_description: { type: textarea }

  # Settings
  featured: { type: true_false }
  display_order: { type: number }
```

### 4. REST API Slugs

```yaml
rewrite:
  slug: short-url        # Use short, clear URLs
  with_front: false      # Usually false for cleaner URLs
  hierarchical: true     # Match taxonomy hierarchical setting
```

---

## Troubleshooting

### Taxonomy Not Showing

1. **Validate schema:**
   ```bash
   wp schema validate your-taxonomy --type=taxonomy
   ```

2. **Check if registered:**
   ```bash
   wp taxonomy list | grep your-taxonomy
   ```

3. **Flush rewrite rules:**
   ```bash
   wp schema flush
   ```

### ACF Fields Not Appearing

1. **Verify ACF is active:**
   ```bash
   wp plugin list | grep advanced-custom-fields
   ```

2. **Check field group registration:**
   - Fields are registered on `acf/init` hook
   - Check PHP error logs for issues

3. **Clear WordPress cache**

### TypeScript Generation Issues

1. **Check file permissions:**
   ```bash
   ls -la wp-content/schemas/taxonomies/
   ```

2. **Validate schema first:**
   ```bash
   wp schema validate your-taxonomy --type=taxonomy
   ```

3. **Check output directory exists and is writable**

---

## Additional Resources

- **Full Schema Format:** See `SCHEMA-FORMAT.md`
- **Implementation Report:** See `TAXONOMY-IMPLEMENTATION-REPORT.md`
- **ACF Field Types:** See `SCHEMA-FORMAT.md` field types section
- **WP-CLI Reference:** Run `wp schema --help`

---

## Support

For issues or questions:
1. Check schema validation: `wp schema validate <taxonomy> --type=taxonomy`
2. Review error logs: `wp-content/debug.log`
3. Test with minimal schema first
4. Review example schemas in `schemas/taxonomies/`

---

**Happy taxonomy building! 🎉**
