# WordPress Schema System (ignis-schema-wp)

> AI-friendly schema-based custom post type and taxonomy management system for WordPress

[![Version](https://img.shields.io/badge/version-1.1.0-blue.svg)](https://github.com/your-repo/ignis-schema-wp)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 🎯 Overview

WordPress Schema System is a powerful plugin that lets you define custom post types, taxonomies, and ACF fields using simple YAML/JSON schemas. Say goodbye to clicking through WordPress admin screens and hello to version-controlled, AI-friendly, type-safe content structures.

### What This Plugin Does

Instead of clicking through admin interfaces or writing verbose PHP code, you define your content structure in simple YAML or JSON files. The system automatically:

- ✅ Registers WordPress custom post types
- ✅ Registers WordPress taxonomies (categories and tags)
- ✅ Generates ACF field groups programmatically for both post types and taxonomies
- ✅ Exposes everything via WordPress REST API
- ✅ Creates TypeScript type definitions for frontend development
- ✅ Provides WP-CLI commands for automation
- ✅ Works seamlessly with AI assistants (like Claude) for schema generation

### Key Features

- 📝 **Schema-First Design** - Define everything in YAML/JSON files
- 🔧 **Complete ACF Integration** - All 25+ field types supported for both post types and taxonomies
- 📦 **TypeScript Generation** - Automatic type definitions for frontend development
- 🤖 **AI-Friendly** - Natural language schema generation
- 🎨 **Taxonomy Management** - Full support for hierarchical and flat taxonomies with custom fields
- 🚀 **REST API Ready** - Full REST API support out of the box
- 💻 **WP-CLI Integration** - Powerful command-line tools
- 🔄 **Bidirectional Relationships** - Flexible post type ↔ taxonomy connections
- 📊 **Admin Dashboard** - Visual overview of all schemas

## 🎯 Why Schema-First?

**Traditional Approach:**
- Click through WordPress admin UI to create post types
- Configure ACF fields manually in the admin interface
- Export to PHP or JSON for version control
- Difficult for AI to understand and modify
- Hard to maintain consistency across environments

**Schema-First Approach:**
- Define everything in a single, clear YAML/JSON file
- Version controlled from the start
- AI can easily read, generate, and modify schemas
- Automatic validation and type safety
- One source of truth for your data model

## 🛠️ Installation

### Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **⚠️ Advanced Custom Fields (ACF) 5.0+ or ACF Pro** - **REQUIRED** (see below)
- **YAML Parser:** php-yaml extension OR symfony/yaml package
- **WP-CLI:** (optional) For command-line operations

### ⚠️ Important: ACF Plugin Dependency

**This system requires ACF to be installed and activated.** It does NOT replace ACF - instead, it provides a modern, AI-friendly layer on top of ACF that:
- Defines fields in YAML/JSON instead of clicking through the UI
- Generates ACF field groups programmatically
- Adds TypeScript type generation
- Provides WP-CLI commands
- Adds taxonomy term meta support

**Think of it as:**
```
WordPress Schema System (YAML/JSON schemas)
           ↓
      ACF Plugin (field engine)
           ↓
   WordPress (storage)
```

**Without ACF, the schema system will not work.**

### Setup Steps

1. **Install ACF (if not already installed):**

```bash
# Via WP-CLI
wp plugin install advanced-custom-fields --activate

# Or ACF Pro (recommended for repeater, flexible content, etc.)
# Download from https://www.advancedcustomfields.com/
wp plugin install /path/to/advanced-custom-fields-pro.zip --activate
```

**⚠️ Verify ACF is active before proceeding!**

2. **Install the plugin:**

```bash
# Via Git
cd wp-content/plugins
git clone https://github.com/your-repo/ignis-schema-wp.git
wp plugin activate ignis-schema-wp

# Or manual installation
# Download and extract to wp-content/plugins/ignis-schema-wp
# Then activate via WordPress admin or WP-CLI
```

3. **Install YAML parser (choose one):**

```bash
# Option 1: PHP YAML extension (recommended)
pecl install yaml

# Option 2: Symfony YAML package
composer require symfony/yaml
```

4. **Create schema directories:**

The plugin automatically creates these directories on activation:
- `wp-content/schemas/post-types/`
- `wp-content/schemas/taxonomies/`

Example schemas are installed for reference.

## 🚀 Quick Start

### Example 1: Movie Database

#### Create Post Type Schema

Create `wp-content/schemas/post-types/movie.yaml`:

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

  director:
    type: text
    label: Director
    required: true

  box_office:
    type: number
    label: Box Office Revenue
    prepend: "$"
    step: 0.01

rest_api:
  enabled: true
  base: movies
```

#### Create Taxonomy Schemas

Create `wp-content/schemas/taxonomies/movie-genre.yaml`:

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

  genre_description_long:
    type: wysiwyg
    label: Long Description
    media_upload: false
    toolbar: basic

rest_api:
  enabled: true
  base: movie-genres
```

Create `wp-content/schemas/taxonomies/movie-tag.yaml`:

```yaml
taxonomy: movie-tag
label: Movie Tags
singular_label: Movie Tag
hierarchical: false
public: true
show_in_rest: true

post_types:
  - movie

fields:
  tag_badge_color:
    type: color_picker
    label: Badge Color
    default_value: '#888888'

  tag_priority:
    type: select
    label: Priority
    choices:
      low: Low Priority
      normal: Normal
      high: High Priority
    default_value: normal
```

### Verify Your Schemas

```bash
# List all schemas
wp schema list --type=all

# Validate specific schemas
wp schema validate movie --type=post-type
wp schema validate movie-genre --type=taxonomy
wp schema validate movie-tag --type=taxonomy

# Get detailed information
wp schema info movie --type=post-type
wp schema info movie-genre --type=taxonomy
```

### Generate TypeScript Types

```bash
# Generate types for all schemas
wp schema export_all --type=all --output=./src/types

# Or generate individually
wp schema export movie --type=post-type --output=./src/types
wp schema export movie-genre --type=taxonomy --output=./src/types
```

This creates TypeScript interfaces like:

```typescript
// post-types/movie.ts
export interface MovieACF {
  rating: number;
  release_date: string;
  trailer_url: string;
  director: string;
  box_office: number;
}

export interface Movie {
  id: number;
  title: { rendered: string };
  acf: MovieACF;
  movie_genre: number[];
  movie_tag: number[];
}

// taxonomies/movie-genre.ts
export interface MovieGenreACF {
  genre_color: string;
  genre_icon: string;
  genre_description_long: string;
}

export interface MovieGenre extends WPTerm {
  taxonomy: 'movie-genre';
  acf: MovieGenreACF;
}
```

### Use in Your Application

```typescript
// Fetch movies with genre data
const response = await fetch('/wp-json/wp/v2/movies?_embed');
const movies: Movie[] = await response.json();

movies.forEach(movie => {
  console.log(movie.title.rendered);
  console.log(`Rating: ${movie.acf.rating}/10`);
  console.log(`Director: ${movie.acf.director}`);
});

// Fetch movie genres
const genresResponse = await fetch('/wp-json/wp/v2/movie-genres');
const genres: MovieGenre[] = await genresResponse.json();

genres.forEach(genre => {
  console.log(genre.name);
  console.log(`Color: ${genre.acf.genre_color}`);
  console.log(`Icon: ${genre.acf.genre_icon}`);
});
```

## 💻 WP-CLI Commands

### List Schemas

```bash
# List all schemas (post types and taxonomies)
wp schema list --type=all

# List only post types
wp schema list --type=post-type

# List only taxonomies
wp schema list --type=taxonomy
```

### Schema Information

```bash
# Get detailed information
wp schema info movie --type=post-type
wp schema info movie-genre --type=taxonomy

# View as JSON or YAML
wp schema info movie --format=json --type=post-type
wp schema info movie-genre --format=yaml --type=taxonomy
```

### Validate Schemas

```bash
# Validate specific schema
wp schema validate movie --type=post-type
wp schema validate movie-genre --type=taxonomy

# The validator checks:
# - Required fields
# - Field type validity
# - Character limits
# - Configuration consistency
```

### Create Schemas from Natural Language

```bash
# Create from AI-friendly prompt
wp schema create book --type=post-type \
  --prompt="Book catalog with ISBN, author, publisher, price, and cover image"

wp schema create book-category --type=taxonomy \
  --prompt="Book categories with icons, colors, and featured status"

# Overwrite existing schema
wp schema create event --type=post-type \
  --prompt="Event management system" \
  --overwrite
```

### Export TypeScript

```bash
# Export single schema
wp schema export movie --type=post-type --output=./types
wp schema export movie-genre --type=taxonomy --output=./types

# Export all schemas (post types and taxonomies)
wp schema export_all --type=all --output=./types

# Export only taxonomies
wp schema export_all --type=taxonomy --output=./types/taxonomies
```

### Register Schemas Manually

```bash
# Register all schemas
wp schema register --type=all

# Register specific type
wp schema register --type=post-type --slug=movie
wp schema register --type=taxonomy --slug=movie-genre
```

### Import Schema Files

```bash
# Import a YAML file from any location (no need to remember the exact schema path)
wp schema import /path/to/product.yaml

# Import as a specific type
wp schema import ~/Downloads/event.yaml --type=post-type
wp schema import ./category.yaml --type=taxonomy

# Import with custom slug
wp schema import ./my-schema.yaml --slug=custom-name

# Overwrite existing schema
wp schema import product.yaml --overwrite

# The import command will:
# - Validate the schema file
# - Copy it to the correct schemas directory (wp-content/schemas/post-types or wp-content/schemas/taxonomies)
# - Provide next steps for registration
```

### Flush Rewrite Rules

```bash
# Run this after adding new post types or changing slugs
wp schema flush
```

## 📚 Schema Format

See [SCHEMA-FORMAT.md](./ignis-schema-wp/SCHEMA-FORMAT.md) for complete documentation.

### Quick Reference

#### Post Type Schema

```yaml
post_type: string              # Required: Post type slug (max 20 chars)
label: string                  # Required: Plural display name
singular_label: string         # Optional: Singular name
description: string            # Optional: Description
public: boolean                # Default: true
show_in_rest: boolean         # Default: true (required for REST API)
menu_icon: string             # Dashicon class or URL
supports: array               # WordPress features
  - title
  - editor
  - thumbnail
hierarchical: boolean         # Default: false
has_archive: boolean          # Default: true
taxonomies: array             # Taxonomies to associate
  - taxonomy-slug

rewrite:                      # URL rewrite settings
  slug: string
  with_front: boolean

fields:                       # Custom ACF fields
  field_name:
    type: string              # Required: Field type
    label: string             # Required: Display label
    required: boolean         # Default: false
    default_value: any        # Optional
    # ... type-specific options

rest_api:                     # REST API settings
  enabled: boolean
  base: string
```

#### Taxonomy Schema

```yaml
taxonomy: string              # Required: Taxonomy slug (max 32 chars)
label: string                 # Required: Plural display name
singular_label: string        # Optional: Singular name
description: string           # Optional: Description
hierarchical: boolean         # true = categories, false = tags
public: boolean               # Default: true
show_in_rest: boolean        # Default: true (required for REST API)
show_admin_column: boolean   # Default: true
post_types: array            # Post types that use this taxonomy
  - post-type-slug

rewrite:                     # URL rewrite settings
  slug: string
  with_front: boolean
  hierarchical: boolean

fields:                      # Custom ACF term meta fields
  field_name:
    type: string             # Required: Field type
    label: string            # Required: Display label
    # ... same field options as post types

rest_api:                    # REST API settings
  enabled: boolean
  base: string
```

## 🔄 Bidirectional Relationships

Connect taxonomies to post types using either approach:

**Option 1: Define in Taxonomy Schema**
```yaml
# taxonomies/movie-genre.yaml
taxonomy: movie-genre
post_types:
  - movie
  - tv-show
```

**Option 2: Define in Post Type Schema**
```yaml
# post-types/movie.yaml
post_type: movie
taxonomies:
  - movie-genre
  - movie-tag
```

**Option 3: Both** (System automatically merges)
```yaml
# taxonomies/movie-genre.yaml
post_types: [movie]

# post-types/movie.yaml
taxonomies: [movie-genre]
```

The system automatically resolves and merges relationships from both sources.

## 🎨 Supported Field Types

All ACF field types are supported for both post types and taxonomy terms:

### Basic Fields
- `text` - Single line text input
- `textarea` - Multi-line text
- `number` - Numeric input with min/max
- `email` - Email address
- `url` - URL with validation
- `password` - Password input

### Choice Fields
- `select` - Dropdown select
- `checkbox` - Multiple checkboxes
- `radio` - Radio buttons
- `true_false` - Toggle switch

### Content Fields
- `wysiwyg` - Rich text editor
- `oembed` - Embed URL (YouTube, etc.)
- `image` - Image upload
- `file` - File upload
- `gallery` - Multiple images

### Relational Fields
- `post_object` - Select posts
- `relationship` - Advanced post selector
- `taxonomy` - Select taxonomy terms
- `user` - Select users

### Advanced Fields
- `repeater` - Repeatable field groups (ACF Pro)
- `group` - Group fields together
- `flexible_content` - Flexible layouts (ACF Pro)

### Special Fields
- `date_picker` - Date selection
- `time_picker` - Time selection
- `date_time_picker` - Date and time
- `color_picker` - Color selection
- `google_map` - Map location picker
- `link` - Link with title and target

See [SCHEMA-FORMAT.md](./ignis-schema-wp/SCHEMA-FORMAT.md) for detailed field configuration options.

## 🎯 Real-World Examples

### E-Commerce Product System

**Post Type: Product**
```yaml
post_type: product
label: Products
taxonomies:
  - product-category
  - product-tag

fields:
  price:
    type: number
    label: Price
    prepend: $
    step: 0.01
    required: true

  sku:
    type: text
    label: SKU
    required: true

  stock_quantity:
    type: number
    label: Stock Quantity
    min: 0

  product_images:
    type: gallery
    label: Product Images
    min: 1

  specifications:
    type: repeater
    label: Specifications
    sub_fields:
      spec_name:
        type: text
        label: Name
      spec_value:
        type: text
        label: Value
```

**Taxonomy: Product Category**
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

  category_color:
    type: color_picker
    label: Brand Color

  featured:
    type: true_false
    label: Featured Category
    ui: true

  seo_description:
    type: textarea
    label: SEO Description
    maxlength: 160
```

### Blog with Topics

**Taxonomy: Topic** (hierarchical)
```yaml
taxonomy: topic
label: Topics
hierarchical: true
post_types: [post]

fields:
  topic_icon:
    type: text
    label: Icon Class
    placeholder: "fa-newspaper"

  topic_color:
    type: color_picker
    label: Topic Color

  related_posts:
    type: relationship
    label: Featured Posts
    post_type: [post]
    max: 5
    return_format: id
```

**Taxonomy: Mood** (flat/tags)
```yaml
taxonomy: mood
label: Moods
hierarchical: false
post_types: [post]

fields:
  mood_color:
    type: color_picker
    label: Mood Color

  mood_emoji:
    type: text
    label: Emoji
    maxlength: 2
```

### Course Platform

**Post Type: Course**
```yaml
post_type: course
label: Courses
taxonomies:
  - skill-level
  - subject

fields:
  duration_hours:
    type: number
    label: Duration (hours)
    min: 1

  instructor:
    type: user
    label: Instructor
    role: [instructor, administrator]

  course_materials:
    type: repeater
    label: Course Materials
    sub_fields:
      material_title:
        type: text
        label: Title
      material_file:
        type: file
        label: File
        return_format: array
```

**Taxonomy: Skill Level**
```yaml
taxonomy: skill-level
label: Skill Levels
hierarchical: true
post_types: [course, tutorial]

fields:
  difficulty_number:
    type: number
    label: Difficulty (1-10)
    min: 1
    max: 10

  difficulty_color:
    type: color_picker
    label: Display Color

  prerequisites:
    type: relationship
    label: Required Courses
    post_type: [course]
    return_format: object
```

## 📊 Admin Dashboard

Access the dashboard at: **WordPress Admin → Schema System**

The dashboard displays:

### Post Types Section
- List of registered post types
- Field counts
- REST API status and endpoints
- Quick links to manage posts

### Taxonomies Section
- List of registered taxonomies
- Hierarchical vs flat indicator
- Associated post types
- Field counts
- REST API status

### System Information
- Schema directory locations
- ACF plugin status
- YAML parser availability
- REST API base URL
- WP-CLI availability

### WP-CLI Commands
Quick reference for all available commands

## 🔌 REST API

### Schema Management Endpoints

```
GET /wp-json/schema-system/v1/post-types              # List all post type schemas
GET /wp-json/schema-system/v1/post-types/{post_type}  # Get specific post type schema
GET /wp-json/schema-system/v1/taxonomies              # List all taxonomy schemas
GET /wp-json/schema-system/v1/taxonomies/{taxonomy}   # Get specific taxonomy schema
```

### WordPress Standard Endpoints

Your custom post types and taxonomies are automatically available via REST API:

```
# Post types
GET    /wp-json/wp/v2/movies           # List movies
GET    /wp-json/wp/v2/movies/{id}      # Get single movie
POST   /wp-json/wp/v2/movies           # Create movie
PUT    /wp-json/wp/v2/movies/{id}      # Update movie
DELETE /wp-json/wp/v2/movies/{id}      # Delete movie

# Taxonomies
GET    /wp-json/wp/v2/movie-genre      # List movie genres
GET    /wp-json/wp/v2/movie-genre/{id} # Get single genre
POST   /wp-json/wp/v2/movie-genre      # Create genre
PUT    /wp-json/wp/v2/movie-genre/{id} # Update genre
DELETE /wp-json/wp/v2/movie-genre/{id} # Delete genre
```

### Example: Creating a Movie with Genres

```bash
curl -X POST https://your-site.com/wp-json/wp/v2/movies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Inception",
    "status": "publish",
    "movie_genre": [1, 3],
    "acf": {
      "rating": 8.8,
      "release_date": "2010-07-16",
      "director": "Christopher Nolan",
      "trailer_url": "https://youtube.com/watch?v=YoHD9XEInc0",
      "box_office": 829895144
    }
  }'
```

### Example: Fetching Movies with ACF Data

```bash
curl https://your-site.com/wp-json/wp/v2/movies
```

Response includes ACF fields:
```json
[
  {
    "id": 123,
    "title": {"rendered": "Inception"},
    "movie_genre": [1, 3],
    "movie_tag": [5, 8, 12],
    "acf": {
      "rating": 8.8,
      "release_date": "2010-07-16",
      "director": "Christopher Nolan",
      "trailer_url": "https://youtube.com/watch?v=YoHD9XEInc0",
      "box_office": 829895144
    }
  }
]
```

## 🤖 Using with AI (Claude, ChatGPT, etc.)

### AI-Assisted Schema Creation

The system is designed to work seamlessly with AI assistants. Here's how:

**Prompt Example:**
```
Create a WordPress schema for a real estate property listing system.
I need properties with:
- Address and location
- Price and square footage
- Number of bedrooms and bathrooms
- Property type (house, condo, apartment, land)
- Status (available, pending, sold)
- Image gallery
- Virtual tour URL
- Agent contact information

Also create taxonomies for:
- Property locations (hierarchical: City → Neighborhood)
- Property features (flat tags: pool, garage, fireplace, etc.)
```

**AI Response:**
The AI will generate complete YAML schemas that you can directly save to your schemas directories.

### AI Helper Functions

The system includes AI helper functions in `lib/AIHelper.php`:

```php
// Infer field type from field name
AIHelper::inferFieldType('email_address');  // Returns: 'email'
AIHelper::inferFieldType('price');          // Returns: 'number'
AIHelper::inferFieldType('is_featured');    // Returns: 'true_false'

// Generate field configuration suggestions
AIHelper::suggestFieldConfig('image');      // Returns recommended settings

// Sanitize and validate
AIHelper::sanitizePostTypeSlug('Product Catalog');  // Returns: 'product_catalog'
AIHelper::sanitizeFieldKey('User Email');           // Returns: 'user_email'
```

## 🐛 Troubleshooting

### ⚠️ ACF Plugin Not Installed/Active

**This is the #1 issue!** The schema system requires ACF to function.

**Symptoms:**
- Fields don't appear in WordPress admin
- Error: "Call to undefined function acf_add_local_field_group()"
- Schemas validate but nothing happens

**Solution:**
```bash
# Check if ACF is installed
wp plugin list | grep acf

# If not installed
wp plugin install advanced-custom-fields --activate

# If installed but not active
wp plugin activate advanced-custom-fields

# Verify it's working
wp plugin list --status=active | grep acf
```

**In IgniStack Docker:**
```bash
docker exec <container-name> wp plugin list --allow-root | grep acf
docker exec <container-name> wp plugin activate advanced-custom-fields --allow-root
```

### Schema Files Not Loading

1. **Check file permissions:**
```bash
ls -la wp-content/schemas/post-types/
ls -la wp-content/schemas/taxonomies/
```

2. **Validate schema syntax:**
```bash
wp schema validate movie --type=post-type
wp schema validate movie-genre --type=taxonomy
```

3. **Manually register schemas:**
```bash
wp schema register --type=all
```

### ACF Fields Not Showing (ACF is Active)

1. **Verify ACF is active:**
```bash
wp plugin list | grep acf
```

2. **Check if field group was registered:**
- Go to WordPress Admin → Custom Fields
- Look for field group matching your post type/taxonomy

3. **Check for PHP errors:**
```bash
tail -f wp-content/debug.log
```

4. **Test with simple schema:**
Create a minimal schema with just one text field to isolate the issue.

### Taxonomy Terms Not Displaying Custom Fields

1. **Ensure ACF is active** (most common issue)

2. **Verify taxonomy is registered:**
```bash
wp taxonomy list --format=csv --fields=name,label
```

3. **Check field group location rules:**
- ACF field groups for taxonomies must use location rule: `taxonomy == taxonomy-slug`
- This is automatically handled by TaxonomyFieldGenerator

4. **Flush rewrite rules:**
```bash
wp schema flush
wp rewrite flush
```

### REST API Not Working

1. **Check if REST API is enabled in schema:**
```yaml
show_in_rest: true

rest_api:
  enabled: true
  base: custom-slug  # optional
```

2. **Flush rewrite rules:**
```bash
wp schema flush
```

3. **Test REST API endpoint:**
```bash
# Post types
curl https://your-site.com/wp-json/wp/v2/<post_type>

# Taxonomies
curl https://your-site.com/wp-json/wp/v2/<taxonomy>
```

4. **Check WordPress REST API is enabled:**
```bash
curl https://your-site.com/wp-json/
```

### YAML Parsing Errors

**Symptom:**
```
PHP Warning: yaml_parse_file(): scanning error encountered during parsing
```

**Common Causes:**

1. **Unquoted colons in values:**
```yaml
# ❌ Wrong
instructions: Upload an icon (recommended: 64x64px)

# ✅ Correct
instructions: "Upload an icon (recommended: 64x64px)"
```

2. **Inconsistent indentation:**
```yaml
# ❌ Wrong
fields:
  field_name:
  type: text

# ✅ Correct
fields:
  field_name:
    type: text
```

3. **Missing YAML parser:**
```bash
# Install php-yaml extension
pecl install yaml

# Or use Symfony YAML
composer require symfony/yaml
```

### TypeScript Generation Issues

1. **Check output directory permissions:**
```bash
mkdir -p /path/to/output
chmod 755 /path/to/output
```

2. **Verify schemas are valid:**
```bash
wp schema validate movie --type=post-type
```

3. **Test generation:**
```bash
wp schema export movie --type=post-type --output=/tmp/test
cat /tmp/test/post-types/movie.ts
```

## 🤝 Integration with IgniStack Sandbox

This system is designed for the IgniStack Sandbox environment (WordPress with SQLite):

### 1. Install in Dockerfile

```dockerfile
# Install ACF first (REQUIRED)
RUN wp plugin install advanced-custom-fields --activate --allow-root --path=/home/flexy/wordpress

# Install ignis-schema-wp
COPY ignis-schema-wp /home/flexy/wordpress/wp-content/plugins/
RUN wp plugin activate ignis-schema-wp --allow-root --path=/home/flexy/wordpress

# Create schema directories
RUN mkdir -p /home/flexy/wordpress/wp-content/schemas/post-types
RUN mkdir -p /home/flexy/wordpress/wp-content/schemas/taxonomies
```

### 2. Or Activate in init.sh

```bash
#!/bin/bash
cd /home/flexy/wordpress

# Ensure ACF is active (REQUIRED)
wp plugin activate advanced-custom-fields --allow-root

# Activate schema system
wp plugin activate ignis-schema-wp --allow-root

# Copy custom schemas if needed
if [ -d "/workspace/schemas" ]; then
    cp -r /workspace/schemas/* wp-content/schemas/
fi

# Register all schemas
wp schema register --type=all --allow-root

# Flush rewrite rules
wp schema flush --allow-root
```

### 3. Mount Schemas Directory

```bash
# In docker-compose.yml or run command
docker run -v $(pwd)/schemas:/home/flexy/wordpress/wp-content/schemas ...
```

### 4. Use with Workspace

```bash
# Schemas in your workspace are automatically loaded
./host-scripts/create-ignis-sandbox.sh \
  --name dev \
  --mount /path/to/project \
  --wp-instance my-wp
```

### 5. Development Workflow

```bash
# 1. Edit schemas in your workspace
vim schemas/post-types/my-custom-type.yaml

# 2. Validate
docker exec my-wp wp schema validate my-custom-type --type=post-type --allow-root

# 3. Generate TypeScript
docker exec my-wp wp schema export_all --type=all --output=/workspace/frontend/types --allow-root

# 4. Flush cache
docker exec my-wp wp schema flush --allow-root
```

## 🏗️ Architecture

### Directory Structure

```
wp-content/
├── schemas/
│   ├── post-types/
│   │   ├── movie.yaml
│   │   ├── book.yaml
│   │   └── product.yaml
│   └── taxonomies/
│       ├── movie-genre.yaml
│       ├── product-category.yaml
│       └── product-tag.yaml
└── plugins/
    └── ignis-schema-wp/
        ├── lib/
        │   ├── SchemaParser.php            # YAML/JSON parsing
        │   ├── ACFFieldGenerator.php       # ACF field groups for post types
        │   ├── TaxonomyFieldGenerator.php  # ACF term meta for taxonomies
        │   └── AIHelper.php                # AI helper functions
        ├── generators/
        │   └── TypeScriptGenerator.php     # TypeScript type generation
        ├── cli/
        │   └── SchemaCommand.php           # WP-CLI commands
        ├── admin/
        │   └── dashboard.php               # Admin dashboard
        ├── schemas/                        # Example schemas
        │   ├── post-types/
        │   └── taxonomies/
        └── wordpress-schema-system.php     # Main plugin file
```

### Plugin Initialization Flow

```
1. Plugin Activation
   └─> Create schema directories
   └─> Install example schemas

2. WordPress 'init' Hook (Priority 5)
   └─> Load post type schemas from /schemas/post-types/
   └─> Load taxonomy schemas from /schemas/taxonomies/

3. WordPress 'init' Hook (Priority 10)
   └─> Register custom post types via register_post_type()

4. WordPress 'init' Hook (Priority 15)
   └─> Resolve bidirectional relationships
   └─> Register taxonomies via register_taxonomy()
   └─> Associate taxonomies with post types

5. ACF 'acf/init' Hook (Priority 10)
   └─> Generate ACF field groups for post types
   └─> Generate ACF term meta for taxonomies
   └─> Register as local field groups (no DB)

6. WordPress Admin
   └─> Register admin menu
   └─> Display dashboard with all schemas

7. WP-CLI
   └─> Register schema commands
```

### How Bidirectional Relationships Work

The system automatically resolves taxonomy-post type relationships defined in either location:

```php
// In SchemaParser::resolvePostTypeTaxonomyRelations()
$relations = [];

// 1. Collect from taxonomy schemas
foreach ($taxonomy_schemas as $taxonomy => $schema) {
    if (!empty($schema['post_types'])) {
        $relations[$taxonomy] = $schema['post_types'];
    }
}

// 2. Collect from post type schemas
foreach ($post_type_schemas as $post_type => $schema) {
    if (!empty($schema['taxonomies'])) {
        foreach ($schema['taxonomies'] as $taxonomy) {
            if (!in_array($post_type, $relations[$taxonomy] ?? [])) {
                $relations[$taxonomy][] = $post_type;
            }
        }
    }
}

// 3. Return merged relationships
return $relations;
```

This allows you to define relationships in the most natural location for your use case.

## 📦 File Structure

### Core Plugin Files

- `wordpress-schema-system.php` - Main plugin file, initialization
- `lib/SchemaParser.php` - Schema loading, validation, WordPress args conversion
- `lib/ACFFieldGenerator.php` - ACF field group generation for post types
- `lib/TaxonomyFieldGenerator.php` - ACF term meta generation for taxonomies
- `lib/AIHelper.php` - AI-friendly helper functions
- `generators/TypeScriptGenerator.php` - TypeScript type generation
- `cli/SchemaCommand.php` - WP-CLI command implementation
- `admin/dashboard.php` - WordPress admin dashboard

### Documentation

- `README.md` - This file
- `SCHEMA-FORMAT.md` - Complete schema format reference
- `TAXONOMY-QUICKSTART.md` - Quick start guide for taxonomies
- `TAXONOMY-IMPLEMENTATION-REPORT.md` - Technical implementation details
- `CHANGELOG.md` - Version history

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Built on WordPress and Advanced Custom Fields
- Inspired by modern schema-first approaches
- Designed for AI-friendly development with Claude, ChatGPT, and other LLMs

## 📞 Support

- **Documentation:** See the docs in `/ignis-schema-wp/` folder
- **Issues:** [GitHub Issues](https://github.com/your-repo/ignis-schema-wp/issues)
- **CLI Help:** Run `wp schema --help` or `wp help schema`
- **Admin Dashboard:** WordPress Admin → Schema System

## 🗺️ Roadmap

- [x] Custom post type management
- [x] ACF field integration for post types
- [x] TypeScript type generation for post types
- [x] WP-CLI commands for post types
- [x] Taxonomy management (v1.1.0)
- [x] ACF term meta for taxonomies (v1.1.0)
- [x] TypeScript generation for taxonomies (v1.1.0)
- [x] Bidirectional relationships (v1.1.0)
- [ ] Visual schema builder in admin
- [ ] Schema migration tools
- [ ] Import/export schema packages
- [ ] Enhanced AI schema generation
- [ ] GraphQL support
- [ ] Automated testing suite
- [ ] Multi-site support
- [ ] Schema versioning

## 🚦 Getting Help

### Quick Links

- **Quick Start:** See examples above
- **Schema Format:** [SCHEMA-FORMAT.md](./ignis-schema-wp/SCHEMA-FORMAT.md)
- **Taxonomy Guide:** [TAXONOMY-QUICKSTART.md](./ignis-schema-wp/TAXONOMY-QUICKSTART.md)
- **CLI Commands:** Run `wp help schema`
- **Admin Dashboard:** WordPress Admin → Schema System
- **AI Assistance:** This system is designed to work with AI - ask Claude or ChatGPT for help!

### Common Questions

**Q: Do I need ACF Pro?**
A: Free ACF works for most field types. ACF Pro is required only for: repeater, flexible_content, gallery, and clone fields.

**Q: Can I use this with Gutenberg blocks?**
A: Yes! ACF fields work seamlessly with Gutenberg. You can also use ACF blocks.

**Q: Can I migrate existing ACF field groups?**
A: Yes, export your existing ACF fields to PHP/JSON, then convert them to YAML schemas.

**Q: Does this work with WordPress multisite?**
A: Currently single-site only. Multisite support is on the roadmap.

**Q: Can I use this in production?**
A: Yes! The plugin uses WordPress and ACF standard APIs. Always test thoroughly first.

---

**Made with ❤️ for WordPress developers who love clean, maintainable code**
