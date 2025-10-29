# ACF Dependency Clarification

## ⚠️ Critical Information

**The WordPress Schema System REQUIRES Advanced Custom Fields (ACF) to function.**

This is the #1 source of confusion for new users, so let's make it crystal clear.

## 🔍 What This System Is

The WordPress Schema System is **NOT** a replacement for ACF. It is a **modern interface layer on top of ACF**.

Think of it like this:

```
┌─────────────────────────────────────┐
│  WordPress Schema System            │
│  (YAML/JSON schemas)                │
│  - Declarative field definitions    │
│  - TypeScript type generation       │
│  - WP-CLI commands                  │
│  - AI-friendly interface            │
└──────────────┬──────────────────────┘
               │ converts to
               ↓
┌─────────────────────────────────────┐
│  Advanced Custom Fields (ACF)       │
│  - Field rendering engine           │
│  - Data validation & sanitization   │
│  - Database storage                 │
│  - Admin UI                         │
│  - REST API exposure                │
└──────────────┬──────────────────────┘
               │ stores in
               ↓
┌─────────────────────────────────────┐
│  WordPress Database                 │
└─────────────────────────────────────┘
```

## 🎯 The Relationship

### What the Schema System Does

- ✅ Defines fields in YAML/JSON (instead of UI clicks)
- ✅ Validates schema structure
- ✅ Converts schemas to ACF field group arrays
- ✅ Calls `acf_add_local_field_group()` to register fields
- ✅ Generates TypeScript types from schemas
- ✅ Provides WP-CLI commands for automation
- ✅ Makes schemas AI-friendly and version-controllable

### What ACF Does

- ✅ Provides `acf_add_local_field_group()` function
- ✅ Renders field UI in WordPress admin
- ✅ Validates field data (email format, required fields, etc.)
- ✅ Stores field values in database
- ✅ Retrieves field values via `get_field()`
- ✅ Exposes fields in REST API responses
- ✅ Handles all 25+ field types (text, image, repeater, etc.)

## 🚫 What Happens Without ACF

If ACF is not installed and active:

1. **PHP Fatal Error:**
   ```
   Fatal error: Call to undefined function acf_add_local_field_group()
   ```

2. **Fields Don't Appear:**
   - Schemas load correctly
   - Validation passes
   - But fields don't render in WordPress admin

3. **No Data Storage:**
   - Even if you bypass the error, data won't be stored properly
   - No REST API exposure
   - No field retrieval

## ✅ Correct Installation Order

### Step 1: Install WordPress
```bash
# Your WordPress installation
```

### Step 2: Install ACF (REQUIRED)
```bash
# Free version
wp plugin install advanced-custom-fields --activate

# Or ACF Pro (recommended for repeater, flexible content)
wp plugin install /path/to/acf-pro.zip --activate
```

### Step 3: Verify ACF is Active
```bash
wp plugin list --status=active | grep acf
# Should show: advanced-custom-fields | active
```

### Step 4: Install Schema System
```bash
cp -r wordpress-schema-system wp-content/plugins/
wp plugin activate wordpress-schema-system
```

### Step 5: Verify Both Are Active
```bash
wp plugin list --status=active
# Should show BOTH:
# - advanced-custom-fields
# - wordpress-schema-system
```

## 🔧 Checking Dependencies in Code

The schema system checks for ACF in `ACFFieldGenerator.php`:

```php
public static function register($field_group) {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group($field_group);
    } else {
        // Silent fail - ACF not installed
        error_log('WordPress Schema System: ACF not available');
    }
}
```

If you see this in your logs, ACF is not active!

## 📊 Quick Comparison

| Feature | Schema System | ACF | Both Required? |
|---------|--------------|-----|----------------|
| **Field Definition** | YAML/JSON files | PHP or UI | ✅ |
| **Field Rendering** | ❌ | ✅ | ✅ |
| **Data Storage** | ❌ | ✅ | ✅ |
| **Validation** | Schema structure | Field data | ✅ |
| **TypeScript Types** | ✅ | ❌ | No (optional) |
| **WP-CLI** | ✅ | Limited | No (optional) |
| **AI-Friendly** | ✅ | ❌ | No (optional) |
| **Version Control** | ✅ | Via JSON export | ✅ |

## 🎓 Understanding the Value

### Without Schema System (ACF only)

**Option A: Configure in UI**
```
1. WordPress Admin → Custom Fields
2. Click "Add New Field Group"
3. Click "Add Field"
4. Fill in 20+ settings
5. Click "Add Field" again
6. Repeat for each field
7. Export to JSON for version control
```

**Option B: Write PHP Code**
```php
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key' => 'group_contact',
        'title' => 'Contact Fields',
        'fields' => [
            [
                'key' => 'field_contact_name',
                'label' => 'Name',
                'name' => 'contact_name',
                'type' => 'text',
                'required' => 1,
                // ... 20+ more lines per field
            ],
            [
                'key' => 'field_contact_email',
                // ... another 25 lines
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'contact',
                ],
            ],
        ],
        // ... more configuration
    ]);
}
```

### With Schema System (on top of ACF)

```yaml
# contact.yaml
post_type: contact
label: Contacts
fields:
  contact_name:
    type: text
    label: "Name"
    required: true
  contact_email:
    type: email
    label: "Email"
```

**Then:**
```bash
wp schema register
wp schema export contact  # Generate TypeScript types
```

**The schema system converts your 10-line YAML to the verbose ACF PHP array automatically!**

## 💡 Analogy

Think of it like this:

- **WordPress** = Your house
- **ACF** = The plumbing system
- **Schema System** = A modern control panel for the plumbing

You can't have a control panel without the plumbing!

Or in web development terms:

- **WordPress** = The framework
- **ACF** = jQuery (provides the API)
- **Schema System** = React/Vue (makes it easier to work with)

Just like React still uses JavaScript under the hood, the Schema System uses ACF under the hood.

## 🚨 Common Mistakes

### Mistake 1: Assuming It Replaces ACF
```bash
# WRONG - Will not work!
wp plugin deactivate advanced-custom-fields
wp plugin activate wordpress-schema-system
```

### Mistake 2: Not Checking ACF is Active
```bash
# WRONG - No verification
wp plugin activate wordpress-schema-system
# Fields don't work, why?!
```

```bash
# CORRECT
wp plugin activate advanced-custom-fields
wp plugin list --status=active | grep acf  # Verify!
wp plugin activate wordpress-schema-system
```

### Mistake 3: Installing in Wrong Order
```dockerfile
# WRONG ORDER
COPY wordpress-schema-system /path/to/plugins/
RUN wp plugin activate wordpress-schema-system
# Later...
RUN wp plugin install advanced-custom-fields
```

```dockerfile
# CORRECT ORDER
RUN wp plugin install advanced-custom-fields --activate
COPY wordpress-schema-system /path/to/plugins/
RUN wp plugin activate wordpress-schema-system
```

## ✅ Verification Checklist

Before using the schema system:

- [ ] ACF plugin installed
- [ ] ACF plugin activated
- [ ] Verify: `wp plugin list --status=active | grep acf`
- [ ] Schema system installed
- [ ] Schema system activated
- [ ] Verify: `wp plugin list --status=active | grep schema`
- [ ] Test: Create a simple schema and run `wp schema validate`

## 🎯 Key Takeaways

1. **ACF is REQUIRED** - Not optional, not replaceable
2. **Schema System is a LAYER** - It sits on top of ACF
3. **Both Must Be Active** - Check `wp plugin list`
4. **ACF Provides the Engine** - Schema System provides the interface
5. **Together They're Powerful** - Modern DX + ACF's features

## 📚 Further Reading

- **ACF Documentation**: https://www.advancedcustomfields.com/resources/
- **Schema Format Reference**: See `SCHEMA-FORMAT.md`
- **Integration Guide**: See `docs/SCHEMA-SYSTEM-INTEGRATION.md`
- **Quick Reference**: See `QUICK-REFERENCE.md`

---

## TL;DR

**You MUST have ACF installed and active for this system to work. Period.**

The WordPress Schema System is a modern, AI-friendly wrapper around ACF, not a replacement for it.

```bash
# Install both (in this order)
wp plugin install advanced-custom-fields --activate
wp plugin activate wordpress-schema-system

# Then you're good to go!
```

---

**Still confused? Think of it this way:**

- ACF = Engine 🚗
- Schema System = Modern Dashboard 📱
- You can't drive without an engine, no matter how good the dashboard is!
