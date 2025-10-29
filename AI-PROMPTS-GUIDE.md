# AI Prompts Guide for WordPress Schema System

This guide provides example prompts and patterns for using AI assistants (Claude, ChatGPT, etc.) to create WordPress schemas.

## 🎯 How AI Can Help

The WordPress Schema System is designed to work seamlessly with AI assistants. AI can:

1. **Generate complete schemas from natural language descriptions**
2. **Convert existing WordPress code to schemas**
3. **Optimize and improve schemas**
4. **Create TypeScript types**
5. **Debug validation errors**
6. **Suggest field types and configurations**

## 📝 Example Prompts

### 1. Creating a New Schema

#### Simple Content Type

**Prompt:**
```
Create a WordPress schema for a team member directory.
Include:
- Full name
- Job title
- Department (Engineering, Marketing, Sales, HR)
- Email address
- Phone number
- Bio
- Profile photo
- LinkedIn URL
```

**AI Output:**
```yaml
post_type: team_member
label: Team Members
singular_label: Team Member
description: Company team member directory
public: true
show_in_rest: true
menu_icon: dashicons-groups
supports:
  - title
  - thumbnail
has_archive: true

fields:
  full_name:
    type: text
    label: "Full Name"
    required: true
    maxlength: 100

  job_title:
    type: text
    label: "Job Title"
    required: true
    placeholder: "Senior Developer"

  department:
    type: select
    label: "Department"
    required: true
    choices:
      engineering: "Engineering"
      marketing: "Marketing"
      sales: "Sales"
      hr: "Human Resources"

  email_address:
    type: email
    label: "Email Address"
    required: true

  phone_number:
    type: text
    label: "Phone Number"
    placeholder: "+1 (555) 123-4567"

  bio:
    type: wysiwyg
    label: "Biography"
    tabs: "all"
    toolbar: "basic"

  profile_photo:
    type: image
    label: "Profile Photo"
    return_format: "array"
    preview_size: "medium"

  linkedin_url:
    type: url
    label: "LinkedIn Profile"
    placeholder: "https://linkedin.com/in/username"

rest_api:
  enabled: true
  base: team-members
```

#### E-commerce Product

**Prompt:**
```
Create a WordPress schema for an e-commerce product catalog.
Requirements:
- Product name, SKU, and description
- Regular price and sale price
- Inventory tracking (stock quantity, in stock status)
- Product categories and tags (taxonomies)
- Multiple product images (gallery)
- Product specifications (repeater: name/value pairs)
- Related products
- Shipping options
- Product badge (new, bestseller, limited, exclusive)
```

**AI Output:** (See product.yaml in examples)

#### Event Management

**Prompt:**
```
Create a WordPress schema for event management.
I need:
- Event title and description
- Start date/time and end date/time
- Location (venue name, address, Google Maps)
- Event type (conference, workshop, webinar, meetup)
- Capacity and registration status
- Ticket price
- Speakers (repeater with name, bio, photo)
- Event schedule/agenda
- Registration URL
```

### 2. Converting Existing Code to Schema

**Prompt:**
```
Convert this WordPress custom post type registration code to a YAML schema:

```php
register_post_type('portfolio', [
    'labels' => [
        'name' => 'Portfolio Items',
        'singular_name' => 'Portfolio Item'
    ],
    'public' => true,
    'has_archive' => true,
    'supports' => ['title', 'editor', 'thumbnail'],
]);

// ACF fields
if( function_exists('acf_add_local_field_group') ):
acf_add_local_field_group([
    'key' => 'group_portfolio',
    'title' => 'Portfolio Details',
    'fields' => [
        [
            'key' => 'field_client',
            'label' => 'Client Name',
            'name' => 'client_name',
            'type' => 'text',
        ],
        [
            'key' => 'field_url',
            'label' => 'Project URL',
            'name' => 'project_url',
            'type' => 'url',
        ],
    ],
    'location' => [
        [
            [
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'portfolio',
            ],
        ],
    ],
]);
endif;
```
```

**AI Output:**
```yaml
post_type: portfolio
label: Portfolio Items
singular_label: Portfolio Item
public: true
show_in_rest: true
has_archive: true
supports:
  - title
  - editor
  - thumbnail

fields:
  client_name:
    type: text
    label: "Client Name"

  project_url:
    type: url
    label: "Project URL"

rest_api:
  enabled: true
```

### 3. Optimizing Schemas

**Prompt:**
```
Review and improve this schema for a restaurant menu system:

```yaml
post_type: menu_item
label: Menu Items

fields:
  name:
    type: text
    label: "Name"

  price:
    type: number
    label: "Price"

  category:
    type: text
    label: "Category"
```

Suggest improvements for:
- Missing required fields
- Better field types
- Validation rules
- Additional useful fields
- REST API optimization
```

**AI Output:**
```yaml
post_type: menu_item
label: Menu Items
singular_label: Menu Item
description: Restaurant menu items with pricing and dietary information
public: true
show_in_rest: true
menu_icon: dashicons-food
supports:
  - title
  - editor
  - thumbnail
has_archive: true

fields:
  # IMPROVED: Made required with better configuration
  item_name:
    type: text
    label: "Item Name"
    required: true
    maxlength: 100
    placeholder: "Grilled Salmon"

  # IMPROVED: Added currency symbol and validation
  price:
    type: number
    label: "Price"
    required: true
    min: 0
    step: 0.01
    prepend: "$"

  # IMPROVED: Changed to select with predefined options
  category:
    type: select
    label: "Category"
    required: true
    choices:
      appetizer: "Appetizers"
      main_course: "Main Courses"
      dessert: "Desserts"
      beverage: "Beverages"
      side: "Side Dishes"

  # NEW: Added description
  description:
    type: textarea
    label: "Description"
    rows: 3
    maxlength: 300
    placeholder: "Describe the dish..."

  # NEW: Added dietary information
  dietary_tags:
    type: checkbox
    label: "Dietary Information"
    choices:
      vegetarian: "Vegetarian"
      vegan: "Vegan"
      gluten_free: "Gluten-Free"
      dairy_free: "Dairy-Free"
      spicy: "Spicy"
    layout: "horizontal"

  # NEW: Added allergen warnings
  allergens:
    type: checkbox
    label: "Contains Allergens"
    choices:
      nuts: "Nuts"
      dairy: "Dairy"
      eggs: "Eggs"
      shellfish: "Shellfish"
      soy: "Soy"
      gluten: "Gluten"

  # NEW: Added availability status
  available:
    type: true_false
    label: "Currently Available"
    default_value: true
    ui: true
    ui_on_text: "Available"
    ui_off_text: "Not Available"

  # NEW: Added image
  item_image:
    type: image
    label: "Item Photo"
    return_format: "array"
    preview_size: "medium"

  # NEW: Added preparation time
  prep_time:
    type: number
    label: "Preparation Time"
    append: "minutes"
    min: 0

  # NEW: Added serving size
  serving_size:
    type: text
    label: "Serving Size"
    placeholder: "Serves 2-3"

rest_api:
  enabled: true
  base: menu-items
```

### 4. Creating Complex Schemas

**Prompt:**
```
Create a WordPress schema for a real estate property listing system with:

Basic Information:
- Property title and description
- Property type (house, condo, apartment, townhouse, land)
- Listing status (available, pending, sold, off-market)
- Price
- Address (street, city, state, zip)
- Location on map

Property Details:
- Square footage
- Lot size
- Bedrooms and bathrooms count
- Year built
- Parking spaces
- Property features (checklist: pool, garage, garden, etc.)

Media:
- Image gallery
- Virtual tour URL
- Video tour

Agent Information:
- Agent name
- Agent photo
- Contact email and phone

Additional:
- Open house dates (repeater)
- Similar properties (relationship)
```

**AI Output:** (Complex schema with groups, repeaters, and relationships)

### 5. Debugging Validation Errors

**Prompt:**
```
I'm getting validation errors for this schema:

```yaml
post_type: my cool product
label: Products

fields:
  price-amount:
    type: price
    label: Price

  isAvailable:
    type: bool
```

Error messages:
- "post_type must contain only lowercase letters, numbers, and underscores"
- "Field key 'price-amount' must contain only lowercase letters, numbers, and underscores"
- "Field 'price-amount' has invalid type: price"
- "Field 'isAvailable' missing required property: label is missing"

Please fix these errors and suggest improvements.
```

**AI Output:**
```yaml
# FIXED: Converted spaces to underscores, made lowercase
post_type: my_cool_product
label: Products
singular_label: Product
description: Product catalog
public: true
show_in_rest: true

fields:
  # FIXED: Changed hyphen to underscore, changed type to 'number'
  price_amount:
    type: number
    label: "Price"
    required: true
    min: 0
    step: 0.01
    prepend: "$"

  # FIXED: Changed to snake_case, changed type to 'true_false'
  is_available:
    type: true_false
    label: "Available"
    default_value: true
    ui: true
    ui_on_text: "Available"
    ui_off_text: "Out of Stock"

rest_api:
  enabled: true
```

## 💡 Tips for Working with AI

### 1. Be Specific

**❌ Bad prompt:**
```
Create a product schema
```

**✅ Good prompt:**
```
Create a WordPress schema for a digital product marketplace with:
- Product title, description, and price
- Product type (ebook, course, template, software)
- Download file
- Preview images (gallery)
- Author information
- License type (personal, commercial, extended)
- Tags for categorization
```

### 2. Provide Context

**Example:**
```
I'm building a WordPress site for a university course catalog.
Each course needs: course code, title, description, credits,
instructor name, semester offered, prerequisites (relationship to other courses),
syllabus file (PDF), and enrollment capacity.

The REST API will be consumed by a React frontend.
Please create a schema with appropriate field types and REST API configuration.
```

### 3. Iterate and Refine

**First prompt:**
```
Create a blog post schema with author, date, and categories
```

**Follow-up prompt:**
```
Add the following to the blog schema:
- Estimated reading time
- Featured image with different sizes
- Related posts
- Social media share counts
- SEO meta fields (title, description, keywords)
```

### 4. Ask for Explanations

**Prompt:**
```
Create a schema for customer testimonials, and explain:
- Why you chose each field type
- What the REST API response will look like
- How to query related testimonials in the frontend
```

### 5. Request Validation

**Prompt:**
```
Here's my schema for a job posting system:
[paste schema]

Please review and check for:
- Any validation errors
- Missing required fields
- Potential performance issues with SQLite
- REST API optimization
- Security considerations
```

## 🔄 Common Schema Patterns

### 1. Content with Media Gallery

```
Create a schema for [TYPE] with:
- Title and description
- Image gallery (5-10 images)
- Video embed
- Categories (taxonomy)
- Tags (taxonomy)
```

### 2. Directory/Listing

```
Create a schema for [TYPE] directory with:
- Name and description
- Contact information (email, phone)
- Location (address + map)
- Categories
- Featured image
- Website URL
- Social media links (repeater)
```

### 3. Event/Booking

```
Create a schema for [TYPE] with:
- Title and description
- Start and end date/time
- Location
- Capacity
- Registration status
- Price/ticket information
- Gallery
```

### 4. Product/Catalog

```
Create a schema for [TYPE] catalog with:
- Name, SKU, description
- Price and sale price
- Inventory (stock quantity, availability)
- Categories and tags
- Image gallery
- Specifications (repeater)
- Related items (relationship)
```

## 🎓 Advanced AI Techniques

### Generate Multiple Related Schemas

**Prompt:**
```
Create a complete WordPress schema system for a learning management platform:

1. Course schema with:
   - Course information
   - Curriculum
   - Instructor (relationship)
   - Enrolled students
   - Price

2. Lesson schema with:
   - Lesson content
   - Course (relationship)
   - Duration
   - Video/materials

3. Instructor schema with:
   - Profile information
   - Courses taught (relationship)
   - Bio and credentials

Make sure all schemas are properly related and REST API enabled.
```

### Convert Database Schema to WordPress Schema

**Prompt:**
```
I have this MySQL database schema for a library system:

```sql
CREATE TABLE books (
  id INT PRIMARY KEY,
  title VARCHAR(200),
  author VARCHAR(100),
  isbn VARCHAR(20),
  published_year INT,
  genre VARCHAR(50),
  available BOOLEAN
);
```

Convert this to a WordPress schema with appropriate field types.
```

### Generate TypeScript Types Alongside Schema

**Prompt:**
```
Create a WordPress schema for a blog comment system, and also generate:
1. The YAML schema file
2. TypeScript interfaces for the REST API response
3. Example REST API query code in TypeScript
```

## 📚 Learning Resources

- **Schema Format**: See `SCHEMA-FORMAT.md` for complete field type reference
- **Examples**: Check `schemas/post-types/` for real-world examples
- **CLI**: Run `wp schema --help` for command reference
- **REST API**: WordPress REST API Handbook

## 🎯 Best Practices

1. **Start Simple**: Begin with basic fields, then iterate
2. **Use Examples**: Reference existing schemas as templates
3. **Validate Often**: Run `wp schema validate <post_type>` frequently
4. **Test REST API**: Check endpoints after creating schemas
5. **Version Control**: Keep schemas in git
6. **Document**: Add descriptions and instructions to fields
7. **Think REST First**: Design with API consumption in mind

---

**Happy schema building with AI! 🤖**
