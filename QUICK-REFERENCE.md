# WordPress Schema System - Quick Reference

## ⚠️ IMPORTANT: Requires ACF Plugin

**This system requires Advanced Custom Fields (ACF) to be installed and active.**

```bash
# Install ACF first (REQUIRED)
wp plugin install advanced-custom-fields --activate

# Verify ACF is active
wp plugin list --status=active | grep acf
```

**The schema system is built ON TOP OF ACF, not as a replacement. Without ACF, it will not work!**

---

## 🚀 Quick Start (30 seconds)

```bash
# 1. Create schema file
cat > wp-content/schemas/post-types/book.yaml << 'EOF'
post_type: book
label: Books
fields:
  author:
    type: text
    label: "Author"
  price:
    type: number
    label: "Price"
    prepend: "$"
EOF

# 2. Register (automatic on page load, or manual)
wp schema register

# 3. Use via REST API
curl http://localhost/wp-json/wp/v2/books
```

## 📋 Essential Commands

```bash
# List all schemas
wp schema list

# Show schema details
wp schema info <post_type>

# Validate schema
wp schema validate <post_type>

# Export TypeScript types
wp schema export <post_type>

# Export all schemas
wp schema export-all

# Flush rewrite rules
wp schema flush
```

## 📝 Schema Template

```yaml
post_type: example          # Required: snake_case, max 20 chars
label: Examples             # Required: Plural display name
singular_label: Example     # Optional: Singular name
description: Description    # Optional
public: true                # Default: true
show_in_rest: true         # Default: true (enable REST API)
menu_icon: dashicons-star  # Optional: Dashicon name

supports:                   # WordPress features
  - title
  - editor
  - thumbnail

fields:
  field_name:              # Field key: snake_case
    type: text             # Required: Field type
    label: "Label"         # Required: Display name
    required: false        # Optional: Is required
    placeholder: "Text"    # Optional
    instructions: "Help"   # Optional

rest_api:
  enabled: true
  base: examples           # Optional: Custom REST base
```

## 🎨 Common Field Types

| Type | Use For | Example |
|------|---------|---------|
| `text` | Short text | Name, title |
| `textarea` | Long text | Description, bio |
| `number` | Numbers | Price, quantity |
| `email` | Email addresses | Contact email |
| `url` | URLs | Website link |
| `select` | Dropdown | Category, status |
| `checkbox` | Multiple choices | Features, tags |
| `radio` | Single choice | Size, color |
| `true_false` | Boolean | Featured, active |
| `date_picker` | Dates | Birth date |
| `image` | Single image | Avatar, logo |
| `gallery` | Multiple images | Product photos |
| `wysiwyg` | Rich text | Article content |
| `repeater` | List of items | Team members |
| `relationship` | Related posts | Related products |

## 💡 Field Type Examples

### Text
```yaml
name:
  type: text
  label: "Full Name"
  required: true
  maxlength: 100
```

### Number
```yaml
price:
  type: number
  label: "Price"
  min: 0
  step: 0.01
  prepend: "$"
```

### Select
```yaml
status:
  type: select
  label: "Status"
  choices:
    active: "Active"
    inactive: "Inactive"
  default_value: "active"
```

### True/False
```yaml
featured:
  type: true_false
  label: "Featured"
  ui: true
  ui_on_text: "Yes"
  ui_off_text: "No"
```

### Image
```yaml
photo:
  type: image
  label: "Photo"
  return_format: "array"
  preview_size: "medium"
```

### Repeater
```yaml
team:
  type: repeater
  label: "Team Members"
  layout: "table"
  sub_fields:
    name:
      type: text
      label: "Name"
    role:
      type: text
      label: "Role"
```

### Relationship
```yaml
related:
  type: relationship
  label: "Related Posts"
  post_type:
    - post
    - product
  max: 5
```

## 🔗 REST API Patterns

### GET List
```bash
curl http://localhost/wp-json/wp/v2/<post_type>
```

### GET Single
```bash
curl http://localhost/wp-json/wp/v2/<post_type>/123
```

### POST Create
```bash
curl -X POST http://localhost/wp-json/wp/v2/<post_type> \
  -H "Authorization: Basic $(echo -n user:pass | base64)" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Item",
    "status": "publish",
    "acf": {
      "field_name": "value"
    }
  }'
```

### PUT Update
```bash
curl -X PUT http://localhost/wp-json/wp/v2/<post_type>/123 \
  -H "Authorization: Basic $(echo -n user:pass | base64)" \
  -H "Content-Type: application/json" \
  -d '{
    "acf": {
      "field_name": "new value"
    }
  }'
```

## 🤖 AI Prompt Template

```
Create a WordPress schema for [TYPE] with:
- [FIELD 1 description]
- [FIELD 2 description]
- [FIELD 3 description]

Output format: YAML
Include appropriate field types and validation.
```

**Example:**
```
Create a WordPress schema for a recipe site with:
- Recipe name and description
- Cooking time and difficulty level
- Ingredients list (repeater)
- Step-by-step instructions
- Category (breakfast, lunch, dinner, dessert)
- Servings count
- Image gallery

Output format: YAML
Include appropriate field types and validation.
```

## 📊 TypeScript Usage

### Generate Types
```bash
wp schema export book --output=./frontend/src/types
```

### Use in Code
```typescript
import { Book, BookACF } from './types/book';

// Fetch with types
const books: Book[] = await fetch('/wp-json/wp/v2/books')
  .then(res => res.json());

// Access ACF fields
books.forEach(book => {
  console.log(book.title.rendered);
  console.log(book.acf.author);
  console.log(book.acf.price);
});

// Create new book
const newBook: Partial<Book> = {
  title: { rendered: 'New Book' },
  acf: {
    author: 'John Doe',
    price: 29.99
  }
};
```

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| **ACF not installed** | `wp plugin install advanced-custom-fields --activate` |
| **Error: undefined function acf_add_local_field_group** | ACF plugin is not active - activate it first! |
| Schema not loading | Check file permissions and syntax |
| Fields not showing | 1. Verify ACF is active: `wp plugin list \| grep acf` |
| REST API 404 | Run `wp schema flush` |
| Validation errors | Run `wp schema validate <post_type>` |
| YAML parse error | Install php-yaml extension |

## 📂 File Locations

```
wp-content/
├── plugins/
│   └── wordpress-schema-system/    # Plugin files
└── schemas/
    └── post-types/                 # Your schema files
        ├── book.yaml
        ├── product.yaml
        └── contact.yaml
```

## 🎯 Best Practices

1. **Naming:**
   - Post types: `book`, `team_member`, `product`
   - Fields: `author_name`, `product_sku`, `event_date`

2. **Required Fields:**
   - Mark essential fields as `required: true`
   - Provide clear `instructions`

3. **REST API:**
   - Always enable: `show_in_rest: true`
   - Use custom base for clean URLs

4. **Validation:**
   - Run `wp schema validate` before committing
   - Test REST API endpoints

5. **Version Control:**
   - Keep schemas in git
   - Document changes

## 🚦 Status Codes

| Command | Output |
|---------|--------|
| `wp schema list` | Shows all registered schemas |
| `wp schema info <type>` | Shows field details |
| `wp schema validate <type>` | ✓ Valid or ✗ Errors |

## 📱 Admin Dashboard

Access at: **WordPress Admin → Schema System**

Features:
- View all registered schemas
- See field counts
- Check REST API endpoints
- System information
- Command reference

## 🌐 API Endpoints

| Endpoint | Purpose |
|----------|---------|
| `/wp-json/wp/v2/<post_type>` | CRUD operations |
| `/wp-json/schema-system/v1/schemas` | List schemas |
| `/wp-json/schema-system/v1/schemas/<type>` | Get schema |

## ⚡ Performance Tips

1. **Limit repeater fields:** `max: 20`
2. **Use conditional logic** to hide unnecessary fields
3. **Enable only needed `supports`**
4. **Use `select` instead of `text`** for fixed options
5. **Return format:** Use `id` instead of `object` when possible

## 🎓 Learning Path

1. **5 min:** Create simple text/number schema
2. **15 min:** Add select, checkbox, image fields
3. **30 min:** Use repeaters and relationships
4. **1 hour:** Master TypeScript integration
5. **2 hours:** Build complete application

## 📚 Documentation Links

- **Full Reference:** `SCHEMA-FORMAT.md`
- **User Guide:** `README.md`
- **AI Guide:** `AI-PROMPTS-GUIDE.md`
- **Integration:** `docs/SCHEMA-SYSTEM-INTEGRATION.md`

## 💬 Need Help?

1. Check validation: `wp schema validate <type>`
2. View logs: `tail -f wp-content/debug.log`
3. Test REST API: `curl http://localhost/wp-json/wp/v2/<type>`
4. Ask AI: "Debug my WordPress schema: [paste schema]"

---

**Save this file for quick reference! 📌**
