# WordPress Schema System

> AI-friendly, schema-based custom post type and ACF field management system for WordPress

## 🚀 Overview

WordPress Schema System is a modern, developer-friendly approach to managing custom post types and custom fields in WordPress. Instead of clicking through admin interfaces or writing verbose PHP code, you define your content structure in simple YAML or JSON files. The system automatically:

- ✅ Registers WordPress custom post types
- ✅ Generates ACF field groups programmatically
- ✅ Exposes everything via WordPress REST API
- ✅ Creates TypeScript type definitions for frontend development
- ✅ Provides WP-CLI commands for automation
- ✅ Works seamlessly with AI assistants (like Claude) for schema generation

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

## 📋 Features

### Core Features
- **Schema-Based Configuration**: Define post types and fields in YAML or JSON
- **ACF Integration**: Automatic ACF field group generation with all field types
- **REST API First**: All post types and fields exposed via REST API by default
- **TypeScript Generation**: Auto-generate TypeScript interfaces for frontend apps
- **WP-CLI Integration**: Full command-line interface for schema management
- **Validation**: Built-in schema validation with helpful error messages
- **AI-Friendly**: Designed to work seamlessly with AI coding assistants

### Supported Field Types
- **Basic**: text, textarea, number, email, url, password
- **Choice**: select, checkbox, radio, true_false
- **Content**: wysiwyg, oembed, image, file, gallery
- **Relational**: post_object, relationship, taxonomy, user
- **Advanced**: repeater, group, flexible_content
- **Special**: date_picker, time_picker, date_time_picker, color_picker, google_map, link

## 🛠️ Installation

### Requirements
- WordPress 5.0+
- PHP 7.4+
- **Advanced Custom Fields (ACF) 5.0+ or ACF Pro** ⚠️ **REQUIRED** - This system is built on top of ACF
- YAML parser: php-yaml extension OR symfony/yaml package
- WP-CLI (optional, for CLI commands)

### ⚠️ Important: ACF Plugin Dependency

**This system requires ACF to be installed and activated.** It does NOT replace ACF - instead, it provides a modern, AI-friendly layer on top of ACF that:
- Defines fields in YAML/JSON instead of clicking through the UI
- Generates ACF field groups programmatically
- Adds TypeScript type generation
- Provides WP-CLI commands

**Think of it as:**
```
WordPress Schema System (YAML/JSON schemas)
           ↓
      ACF Plugin (field engine)
           ↓
   WordPress (storage)
```

Without ACF, the schema system will not work.

### Setup

1. **Install ACF (if not already installed):**

```bash
# Via WP-CLI
wp plugin install advanced-custom-fields --activate

# Or ACF Pro (recommended for repeater, flexible content, etc.)
# Download from https://www.advancedcustomfields.com/
wp plugin install /path/to/advanced-custom-fields-pro.zip --activate
```

**⚠️ Verify ACF is active before proceeding!**

2. **Copy the system to your WordPress installation:**

```bash
cp -r wordpress-schema-system /path/to/wordpress/wp-content/plugins/
```

3. **Activate the schema system plugin:**

Via WordPress admin:
- Go to Plugins → Installed Plugins
- Find "WordPress Schema System"
- Click "Activate"

Via WP-CLI:
```bash
wp plugin activate wordpress-schema-system
```

4. **Create schema directory:**

The plugin automatically creates `wp-content/schemas/post-types/` and installs example schemas.

## 📝 Quick Start

### Creating Your First Schema

1. **Create a schema file:**

Create `wp-content/schemas/post-types/book.yaml`:

```yaml
post_type: book
label: Books
singular_label: Book
description: Book catalog with authors and reviews
public: true
show_in_rest: true
menu_icon: dashicons-book
supports:
  - title
  - editor
  - thumbnail
has_archive: true

fields:
  isbn:
    type: text
    label: "ISBN"
    required: true
    placeholder: "978-3-16-148410-0"

  author:
    type: text
    label: "Author Name"
    required: true

  published_date:
    type: date_picker
    label: "Publication Date"
    display_format: "m/d/Y"
    return_format: "Y-m-d"

  price:
    type: number
    label: "Price"
    min: 0
    step: 0.01
    prepend: "$"

  genre:
    type: select
    label: "Genre"
    choices:
      fiction: "Fiction"
      non_fiction: "Non-Fiction"
      mystery: "Mystery"
      sci_fi: "Science Fiction"
      biography: "Biography"

  rating:
    type: number
    label: "Rating"
    min: 0
    max: 5
    step: 0.1

  book_cover:
    type: image
    label: "Book Cover"
    return_format: "array"
    preview_size: "medium"

rest_api:
  enabled: true
  base: books
```

2. **The system automatically:**
- Registers the `book` post type
- Creates ACF field group with all fields
- Exposes via REST API at `/wp-json/wp/v2/books`
- Makes it editable in WordPress admin

3. **Verify it works:**

```bash
# Via WP-CLI
wp schema list
wp schema info book

# Via REST API
curl https://your-site.com/wp-json/wp/v2/books
```

4. **Generate TypeScript types:**

```bash
wp schema export book --output=/path/to/frontend/types

# Or export all schemas
wp schema export-all --output=/path/to/frontend/types
```

This creates `book.ts` with TypeScript interfaces:

```typescript
export interface BookACF {
  isbn: string;
  author: string;
  published_date: string;
  price: number;
  genre: 'fiction' | 'non_fiction' | 'mystery' | 'sci_fi' | 'biography';
  rating: number;
  book_cover: WPImage;
}

export interface Book {
  id: number;
  title: {rendered: string};
  acf: BookACF;
  // ... other WordPress fields
}
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
```

**AI Response:**
The AI will generate a complete YAML schema that you can directly save to your schemas directory. The schema will include appropriate field types, validation, and configuration.

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

## 🖥️ WP-CLI Commands

Complete command reference:

### List all schemas
```bash
wp schema list
```

Output:
```
post_type  label      fields  rest_api
contact    Contacts   8       Yes
product    Products   12      Yes
book       Books      7       Yes
```

### Show schema details
```bash
wp schema info <post_type>

# Examples
wp schema info contact
wp schema info product --format=json
wp schema info book --format=yaml
```

### Validate schema
```bash
wp schema validate <post_type>

# Example
wp schema validate contact
```

Output will show any validation errors or confirm the schema is valid.

### Create new schema from prompt
```bash
wp schema create <post_type> --prompt="description"

# Example
wp schema create event --prompt="Event management with date, time, location, and attendees"

# Overwrite existing schema
wp schema create event --prompt="..." --overwrite
```

### Export to TypeScript
```bash
# Single schema
wp schema export <post_type> [--output=<path>]

# All schemas
wp schema export-all [--output=<path>]

# Examples
wp schema export contact
wp schema export product --output=/var/www/frontend/src/types
wp schema export-all --output=./typescript
```

### Register schemas manually
```bash
# Register all schemas
wp schema register

# Register specific post type
wp schema register --post_type=contact
```

### Flush rewrite rules
```bash
wp schema flush
```

Run this after adding new post types or changing slugs.

## 📚 Schema Format

See [SCHEMA-FORMAT.md](./SCHEMA-FORMAT.md) for complete documentation of:
- All field types and options
- Conditional logic
- Validation rules
- REST API configuration
- Examples for every field type

### Quick Reference

```yaml
post_type: string              # Required: Post type slug (max 20 chars)
label: string                  # Required: Plural display name
singular_label: string         # Optional: Singular name
description: string            # Optional: Description
public: boolean                # Default: true
show_in_rest: boolean         # Default: true
menu_icon: string             # Dashicon or URL
supports: array               # WordPress features
hierarchical: boolean         # Default: false
has_archive: boolean          # Default: true
rewrite:                      # URL rewrite settings
  slug: string
  with_front: boolean

fields:                       # Custom fields
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

## 🎨 Admin Interface

Access the admin dashboard at: **WordPress Admin → Schema System**

The dashboard shows:
- All registered schemas
- Field counts
- REST API endpoints
- Quick links to edit posts
- System information
- WP-CLI command reference

## 🔌 REST API

### Endpoints

All schemas are automatically exposed via REST API:

```
GET  /wp-json/wp/v2/{post_type}           # List posts
GET  /wp-json/wp/v2/{post_type}/{id}      # Get single post
POST /wp-json/wp/v2/{post_type}           # Create post
PUT  /wp-json/wp/v2/{post_type}/{id}      # Update post
DELETE /wp-json/wp/v2/{post_type}/{id}    # Delete post
```

### Schema Management API

```
GET /wp-json/schema-system/v1/schemas              # List all schemas
GET /wp-json/schema-system/v1/schemas/{post_type}  # Get specific schema
```

### Example: Creating a Post

```bash
curl -X POST https://your-site.com/wp-json/wp/v2/contacts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "John Doe",
    "status": "publish",
    "acf": {
      "contact_name": "John Doe",
      "contact_email": "john.doe@example.com",
      "contact_department": "engineering"
    }
  }'
```

### Example: Fetching Posts

```bash
curl https://your-site.com/wp-json/wp/v2/contacts
```

Response includes ACF fields:
```json
[
  {
    "id": 123,
    "title": {"rendered": "John Doe"},
    "acf": {
      "contact_name": "John Doe",
      "contact_email": "john.doe@example.com",
      "contact_department": "engineering",
      "contact_phone": "+1-555-1234"
    }
  }
]
```

## 🔧 Advanced Usage

### Repeater Fields

```yaml
fields:
  team_members:
    type: repeater
    label: "Team Members"
    min: 1
    max: 10
    layout: "table"
    button_label: "Add Member"
    sub_fields:
      name:
        type: text
        label: "Name"
        required: true
      role:
        type: text
        label: "Role"
      email:
        type: email
        label: "Email"
```

### Flexible Content

```yaml
fields:
  page_builder:
    type: flexible_content
    label: "Page Builder"
    button_label: "Add Section"
    layouts:
      hero:
        label: "Hero Section"
        sub_fields:
          title:
            type: text
            label: "Title"
          background_image:
            type: image
            label: "Background"
      text_block:
        label: "Text Block"
        sub_fields:
          content:
            type: wysiwyg
            label: "Content"
```

### Conditional Logic

```yaml
fields:
  has_discount:
    type: true_false
    label: "On Sale"

  discount_percentage:
    type: number
    label: "Discount %"
    min: 0
    max: 100
    conditional_logic:
      - field: has_discount
        operator: "=="
        value: true
```

### Relationships

```yaml
fields:
  related_products:
    type: relationship
    label: "Related Products"
    post_type:
      - product
    filters:
      - search
      - post_type
    max: 5
    return_format: "object"
```

## 📦 File Structure

```
wordpress-schema-system/
├── admin/
│   └── dashboard.php           # Admin interface
├── cli/
│   └── SchemaCommand.php       # WP-CLI commands
├── generators/
│   └── TypeScriptGenerator.php # TypeScript type generation
├── lib/
│   ├── SchemaParser.php        # YAML/JSON parser
│   ├── ACFFieldGenerator.php   # ACF field group generator
│   └── AIHelper.php            # AI helper functions
├── schemas/
│   └── post-types/             # Example schemas
│       ├── contact.yaml
│       └── product.yaml
├── wordpress-schema-system.php # Main plugin file
├── README.md                   # This file
└── SCHEMA-FORMAT.md            # Complete schema documentation
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

### Schema not appearing

1. Check schema file syntax:
```bash
wp schema validate <post_type>
```

2. Check file permissions:
```bash
ls -la wp-content/schemas/post-types/
```

3. Manually register:
```bash
wp schema register
```

### ACF fields not showing (ACF is active)

1. Verify ACF is active:
```bash
wp plugin list | grep acf
```

2. Check if field group was registered:
- Go to WordPress Admin → Custom Fields
- Look for field group matching your post type

3. Check for PHP errors:
```bash
tail -f wp-content/debug.log
```

### REST API not working

1. Check if REST API is enabled in schema:
```yaml
show_in_rest: true
```

2. Flush rewrite rules:
```bash
wp schema flush
```

3. Test REST API endpoint:
```bash
curl https://your-site.com/wp-json/wp/v2/<post_type>
```

### YAML parsing errors

Install YAML parser:

```bash
# Via apt (Ubuntu/Debian)
sudo apt-get install php-yaml

# Via pecl
pecl install yaml

# Or use Symfony YAML (via Composer in your theme/plugin)
composer require symfony/yaml
```

## 🤝 Integration with IgniStack Sandbox

This system is designed for the IgniStack Sandbox environment (WordPress with SQLite):

1. **Install ACF in Dockerfile:**
```dockerfile
# Install ACF first (REQUIRED)
RUN wp plugin install advanced-custom-fields --activate --allow-root --path=/home/flexy/wordpress

# Then install schema system
COPY wordpress-schema-system /home/flexy/wordpress/wp-content/plugins/
RUN wp plugin activate wordpress-schema-system --allow-root --path=/home/flexy/wordpress
```

2. **Or activate in init.sh:**
```bash
# Ensure ACF is active
wp plugin activate advanced-custom-fields --allow-root

# Then activate schema system
wp plugin activate wordpress-schema-system --allow-root
```

3. **Mount schemas directory:**
```bash
docker run -v $(pwd)/schemas:/home/flexy/wordpress/wp-content/schemas ...
```

4. **Use with workspace:**
```bash
# Schemas in your workspace are automatically loaded
./host-scripts/create-ignis-sandbox.sh \
  --name dev \
  --mount /path/to/project \
  --wp-instance my-wp
```

## 📄 License

MIT License - see LICENSE file for details

## 🙏 Credits

- Built for [IgniStack Sandbox](https://github.com/your-repo/ignistack-sandbox)
- Integrates with [Advanced Custom Fields](https://www.advancedcustomfields.com/)
- Designed for AI-assisted development with Claude, ChatGPT, and other LLMs

## 🚦 Getting Help

- **Documentation**: See [SCHEMA-FORMAT.md](./SCHEMA-FORMAT.md)
- **CLI Help**: Run `wp schema --help`
- **Admin Dashboard**: WordPress Admin → Schema System
- **Issues**: Open an issue on GitHub
- **AI Assistance**: This system is designed to work with AI - ask Claude or ChatGPT for help!

---

**Made with ❤️ for modern WordPress development**
