# WordPress Schema Format Specification

## Overview

This schema format allows AI and developers to define WordPress custom post types and custom fields in a simple, declarative YAML/JSON format. The system automatically generates ACF field groups, TypeScript types, and REST API documentation.

## Schema Structure

### Post Type Schema

```yaml
# Basic structure
post_type: string              # Post type slug (max 20 chars, lowercase, underscores)
label: string                  # Plural display name
singular_label: string         # Singular display name (optional, defaults to label)
description: string            # Post type description (optional)
public: boolean                # Public visibility (default: true)
show_in_rest: boolean         # REST API exposure (default: true)
menu_icon: string             # Dashicon name or URL (optional)
supports:                      # Array of WordPress features
  - title
  - editor
  - thumbnail
  - excerpt
  - custom-fields
hierarchical: boolean          # Pages-like hierarchy (default: false)
has_archive: boolean          # Enable archive page (default: true)
rewrite:
  slug: string                # URL slug (optional)
  with_front: boolean         # Prepend front base (default: true)

# Custom fields definition
fields:
  field_name:                 # Field key (snake_case)
    type: string              # Field type (see Field Types below)
    label: string             # Display label
    required: boolean         # Is required (default: false)
    default_value: any        # Default value (optional)
    placeholder: string       # Placeholder text (optional)
    instructions: string      # Help text (optional)
    conditional_logic: array  # Conditional display rules (optional)
    wrapper:                  # Wrapper classes/width (optional)
      width: string           # CSS width (e.g., "50")
      class: string           # CSS classes
    # Type-specific options below
```

## Field Types

### Basic Fields

#### text
```yaml
field_name:
  type: text
  label: "Full Name"
  required: true
  maxlength: 100
  placeholder: "Enter full name"
```

#### textarea
```yaml
description:
  type: textarea
  label: "Description"
  rows: 4
  maxlength: 500
  new_lines: "br"  # br, wpautop, or "" (no formatting)
```

#### number
```yaml
price:
  type: number
  label: "Price"
  min: 0
  max: 9999.99
  step: 0.01
  prepend: "$"
  append: "USD"
```

#### email
```yaml
contact_email:
  type: email
  label: "Email Address"
  required: true
```

#### url
```yaml
website:
  type: url
  label: "Website URL"
  placeholder: "https://example.com"
```

#### password
```yaml
api_key:
  type: password
  label: "API Key"
```

### Choice Fields

#### select
```yaml
department:
  type: select
  label: "Department"
  choices:
    engineering: "Engineering"
    marketing: "Marketing"
    sales: "Sales"
  default_value: "engineering"
  allow_null: false
  multiple: false  # Allow multiple selections
```

#### checkbox
```yaml
features:
  type: checkbox
  label: "Features"
  choices:
    wifi: "WiFi"
    parking: "Parking"
    pool: "Swimming Pool"
  layout: "vertical"  # vertical or horizontal
```

#### radio
```yaml
status:
  type: radio
  label: "Status"
  choices:
    active: "Active"
    inactive: "Inactive"
    pending: "Pending"
  default_value: "pending"
  layout: "horizontal"
```

#### true_false
```yaml
is_featured:
  type: true_false
  label: "Featured Product"
  default_value: false
  ui: true  # Prettier slider UI
  ui_on_text: "Yes"
  ui_off_text: "No"
```

### Content Fields

#### wysiwyg
```yaml
bio:
  type: wysiwyg
  label: "Biography"
  tabs: "all"  # all, visual, text
  toolbar: "full"  # full, basic
  media_upload: true
```

#### oembed
```yaml
video_url:
  type: oembed
  label: "Video URL"
  placeholder: "https://www.youtube.com/watch?v=..."
```

#### image
```yaml
avatar:
  type: image
  label: "Avatar"
  return_format: "array"  # array, url, id
  preview_size: "medium"
  library: "all"  # all, uploadedTo
```

#### file
```yaml
resume:
  type: file
  label: "Resume (PDF)"
  return_format: "array"
  mime_types: "pdf,doc,docx"
```

#### gallery
```yaml
product_images:
  type: gallery
  label: "Product Images"
  min: 1
  max: 10
  insert: "append"
  library: "all"
```

### Relational Fields

#### post_object
```yaml
related_products:
  type: post_object
  label: "Related Products"
  post_type:
    - product
  allow_null: true
  multiple: true
  return_format: "object"  # object or id
```

#### relationship
```yaml
team_members:
  type: relationship
  label: "Team Members"
  post_type:
    - employee
  filters:
    - search
    - post_type
  min: 1
  max: 5
  return_format: "object"
```

#### taxonomy
```yaml
categories:
  type: taxonomy
  label: "Categories"
  taxonomy: "category"
  field_type: "checkbox"  # checkbox, multi_select, radio, select
  add_term: true  # Allow adding new terms
  save_terms: true  # Save to post
  load_terms: true  # Load from post
  return_format: "object"  # object or id
```

#### user
```yaml
assigned_to:
  type: user
  label: "Assigned To"
  role:
    - editor
    - administrator
  allow_null: true
  multiple: false
  return_format: "array"
```

### Advanced Fields

#### repeater
```yaml
social_links:
  type: repeater
  label: "Social Links"
  min: 0
  max: 10
  layout: "table"  # table, block, row
  button_label: "Add Link"
  sub_fields:
    platform:
      type: select
      label: "Platform"
      choices:
        linkedin: "LinkedIn"
        twitter: "Twitter"
        github: "GitHub"
    url:
      type: url
      label: "URL"
```

#### group
```yaml
address:
  type: group
  label: "Address"
  layout: "block"  # block or row
  sub_fields:
    street:
      type: text
      label: "Street"
    city:
      type: text
      label: "City"
    postal_code:
      type: text
      label: "Postal Code"
```

#### flexible_content
```yaml
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
        image:
          type: image
          label: "Background Image"
    text_block:
      label: "Text Block"
      sub_fields:
        content:
          type: wysiwyg
          label: "Content"
```

### Special Fields

#### date_picker
```yaml
birth_date:
  type: date_picker
  label: "Birth Date"
  display_format: "m/d/Y"
  return_format: "Y-m-d"
  first_day: 1  # 0 = Sunday, 1 = Monday
```

#### time_picker
```yaml
meeting_time:
  type: time_picker
  label: "Meeting Time"
  display_format: "g:i a"
  return_format: "H:i:s"
```

#### date_time_picker
```yaml
event_datetime:
  type: date_time_picker
  label: "Event Date & Time"
  display_format: "m/d/Y g:i a"
  return_format: "Y-m-d H:i:s"
  first_day: 1
```

#### color_picker
```yaml
brand_color:
  type: color_picker
  label: "Brand Color"
  default_value: "#2271b1"
  enable_opacity: true
```

#### google_map
```yaml
location:
  type: google_map
  label: "Location"
  center_lat: 37.7749
  center_lng: -122.4194
  zoom: 12
```

#### link
```yaml
cta_button:
  type: link
  label: "Call to Action"
  return_format: "array"  # array or url
```

## Conditional Logic

```yaml
fields:
  show_video:
    type: true_false
    label: "Include Video"

  video_url:
    type: oembed
    label: "Video URL"
    conditional_logic:
      - field: show_video
        operator: "=="
        value: true
```

Multiple conditions:
```yaml
conditional_logic:
  - # AND condition (all must be true)
    - field: field_name
      operator: "=="
      value: "value"
    - field: another_field
      operator: "!="
      value: "value"
  - # OR condition (alternative group)
    - field: third_field
      operator: ">"
      value: 10
```

Operators: `==`, `!=`, `>`, `<`, `>=`, `<=`, `contains`, `!contains`

## REST API Configuration

```yaml
rest_api:
  enabled: true
  base: "products"  # Custom REST base (optional)
  show_in_rest: true
  rest_controller_class: "WP_REST_Posts_Controller"  # Custom controller (optional)

fields:
  field_name:
    show_in_rest: true  # Expose field in REST API
    rest_schema:  # Custom REST schema (optional)
      type: string
      format: email
```

## Validation Rules

```yaml
fields:
  email:
    type: email
    validation:
      required: true
      format: email
      custom: "validate_company_email"  # Custom PHP function

  age:
    type: number
    validation:
      min: 18
      max: 120
      required: true

  username:
    type: text
    validation:
      pattern: "^[a-zA-Z0-9_]{3,20}$"
      unique: true  # Must be unique across all posts
```

## Complete Example: Product CPT

```yaml
# schemas/post-types/product.yaml
post_type: product
label: Products
singular_label: Product
description: Product catalog with pricing and inventory
public: true
show_in_rest: true
menu_icon: dashicons-products
supports:
  - title
  - editor
  - thumbnail
  - excerpt
has_archive: true
rewrite:
  slug: products
  with_front: false

fields:
  # Basic Information
  product_name:
    type: text
    label: "Product Name"
    required: true
    maxlength: 200

  sku:
    type: text
    label: "SKU"
    required: true
    placeholder: "PROD-001"
    validation:
      unique: true
      pattern: "^[A-Z0-9-]+$"

  # Pricing
  price:
    type: number
    label: "Price"
    required: true
    min: 0
    step: 0.01
    prepend: "$"

  sale_price:
    type: number
    label: "Sale Price"
    min: 0
    step: 0.01
    prepend: "$"
    instructions: "Leave empty if not on sale"

  # Inventory
  stock_quantity:
    type: number
    label: "Stock Quantity"
    default_value: 0
    min: 0

  in_stock:
    type: true_false
    label: "In Stock"
    default_value: true
    ui: true

  # Categories
  product_categories:
    type: taxonomy
    label: "Categories"
    taxonomy: product_category
    field_type: checkbox
    add_term: true
    save_terms: true
    load_terms: true

  # Media
  product_images:
    type: gallery
    label: "Product Images"
    min: 1
    max: 10
    instructions: "Upload product images (JPG or PNG)"

  # Specifications
  specifications:
    type: repeater
    label: "Specifications"
    layout: table
    button_label: "Add Specification"
    sub_fields:
      spec_name:
        type: text
        label: "Name"
      spec_value:
        type: text
        label: "Value"

  # Related Products
  related_products:
    type: relationship
    label: "Related Products"
    post_type:
      - product
    max: 5
    return_format: object

rest_api:
  enabled: true
  base: products
```

## JSON Format Alternative

The same schema can be defined in JSON:

```json
{
  "post_type": "product",
  "label": "Products",
  "singular_label": "Product",
  "fields": {
    "product_name": {
      "type": "text",
      "label": "Product Name",
      "required": true
    }
  }
}
```

## AI Integration Notes

When generating schemas from natural language prompts:

1. **Extract entity name** → `post_type` (singular, snake_case)
2. **Identify attributes** → `fields` with appropriate types
3. **Detect relationships** → use `post_object`, `relationship`, or `taxonomy`
4. **Infer field types**:
   - "email" → `email`
   - "price", "amount", "quantity" → `number`
   - "description", "bio" → `textarea` or `wysiwyg`
   - "image", "photo", "picture" → `image` or `gallery`
   - "category", "tag" → `taxonomy`
   - "link", "URL" → `url` or `link`
5. **Add validation**: Required fields, min/max for numbers, formats
6. **Enable REST API**: Set `show_in_rest: true` by default

## TypeScript Type Generation

The system generates TypeScript interfaces:

```typescript
// Generated from product.yaml
export interface Product {
  id: number;
  title: string;
  product_name: string;
  sku: string;
  price: number;
  sale_price?: number;
  stock_quantity: number;
  in_stock: boolean;
  product_categories: Category[];
  product_images: ImageGallery;
  specifications: Array<{
    spec_name: string;
    spec_value: string;
  }>;
  related_products: Product[];
}

export interface ProductACF {
  product_name: string;
  sku: string;
  price: number;
  sale_price?: number;
  stock_quantity: number;
  in_stock: boolean;
  product_categories: number[] | Category[];
  product_images: number[] | WPImage[];
  specifications: Array<{
    spec_name: string;
    spec_value: string;
  }>;
  related_products: number[] | Product[];
}
```

## File Naming Convention

- Post types: `schemas/post-types/{post_type}.yaml`
- Taxonomies: `schemas/taxonomies/{taxonomy}.yaml`
- Use snake_case for filenames
- Extensions: `.yaml` or `.json`

## Validation

The system validates:
- Required fields presence
- Field type correctness
- Post type slug length (max 20 chars)
- Field key naming (snake_case, no special chars)
- Conditional logic field references
- Taxonomy existence
- Post type references in relationships

## Best Practices

1. **Naming**: Use clear, descriptive names
2. **Organization**: Group related fields logically
3. **Validation**: Add appropriate validation rules
4. **REST API**: Enable for headless WordPress use
5. **Documentation**: Use `instructions` for field help text
6. **Defaults**: Provide sensible default values
7. **Required fields**: Mark essential fields as required
8. **Performance**: Limit repeater/flexible content max values
9. **SQLite**: Avoid excessive nesting (3 levels max)
10. **Version control**: Keep schemas in git
