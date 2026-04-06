# Code Standards & Contributing Guide

## Code Quality Standards

### PHP Code Standards

#### File Structure
```php
<?php
// 1. Require configuration files
require_once 'config/session.php';
require_once 'config/helpers.php';

// 2. Check authentication
requireRole('citizen');

// 3. Get current user info
$user_id = getUserId();
$user = getCurrentUser();

// 4. Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
}

// 5. Query data
$query = "SELECT * FROM table WHERE id = ?";

// 6. Output HTML
?>
<!DOCTYPE html>
<html>
</html>
```

#### Naming Conventions
- **Variables**: `$snake_case` - e.g., `$user_id`, `$complaint_data`
- **Functions**: `snake_case()` - e.g., `getUserById()`, `formatStatus()`
- **Classes**: `PascalCase` - e.g., `UserManager`, `ComplaintHandler` (if used)
- **Constants**: `UPPER_SNAKE_CASE` - e.g., `MAX_FILE_SIZE`, `DB_HOST`
- **Tables**: `snake_case_lowercase` - e.g., `investigation_reports`
- **Columns**: `snake_case_lowercase` - e.g., `created_at`, `user_id`

#### Security Best Practices

**Never concatenate user input into queries**
```php
// ❌ WRONG - SQL Injection vulnerability
$query = "SELECT * FROM users WHERE email = '" . $_POST['email'] . "'";

// ✅ CORRECT - Use prepared statements
$stmt = $db_conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $_POST['email']);
```

**Always escape output**
```php
// ❌ WRONG - XSS vulnerability
echo $_POST['comment'];

// ✅ CORRECT - Use htmlspecialchars
echo htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
```

**Validate all inputs**
```php
// ✅ CORRECT - Validate before use
$email = sanitizeInput($_POST['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email');
}
```

**Use proper error handling**
```php
// ❌ WRONG - Exposes database structure
die($db_conn->error);

// ✅ CORRECT - User-friendly error message
if (!$result) {
    $_SESSION['error'] = 'An error occurred. Please try again.';
    logActivity($user_id, null, 'error', 'Database error in complaints query');
}
```

#### Indentation and Formatting
- Use 4 spaces for indentation (NOT tabs)
- Line length should not exceed 100 characters
- Always use semicolons at end of statements
- Use single quotes for simple strings, double quotes for interpolation
- Use PSR-12 code style guidelines

```php
// ✅ CORRECT formatting
$query = "SELECT id, name, email FROM users " .
         "WHERE role = ? AND status = 'active' " .
         "ORDER BY name ASC";

$stmt = $db_conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = 'Database preparation failed';
    exit;
}

$stmt->bind_param("s", $role);
$stmt->execute();
$result = $stmt->get_result();
```

### HTML/CSS Standards

#### HTML Structure
```html
<!-- Use semantic HTML5 elements -->
<main>
    <section>
        <!-- Page content -->
    </section>
</main>

<!-- Proper form structure -->
<form method="POST" action="submit.php" enctype="multipart/form-data">
    <div class="form-group">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
    </div>
</form>
```

#### CSS Class Naming
Use BEM (Block Element Modifier) format:
```css
/* Block */
.card {
    padding: 1rem;
}

/* Element */
.card__header {
    font-weight: bold;
}

/* Modifier */
.card--featured {
    border: 2px solid gold;
}
```

### JavaScript Standards

#### Code Style
```javascript
// Use const by default, let if reassignment needed
const MAX_RETRIES = 3;
let attemptCount = 0;

// Use meaningful variable names
const userEmail = document.getElementById('email');
const isValidEmail = validateEmail(email);

// Use arrow functions
const handleSubmit = (event) => {
    event.preventDefault();
    // handle submission
};

// Use template literals
const message = `Hello ${user.name}, your complaint ID is ${complaint.id}`;
```

#### Error Handling
```javascript
// ✅ CORRECT - Proper error handling
try {
    const response = await fetch('/api/complaints');
    if (!response.ok) {
        throw new Error('Failed to fetch complaints');
    }
    const data = await response.json();
    displayComplaints(data);
} catch (error) {
    console.error('Error:', error);
    showAlert('Error loading complaints', 'error');
}
```

---

## Database Standards

### Query Guidelines
```php
// ✅ ALWAYS use prepared statements
$stmt = $db_conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Use consistent parameter types
// s = string
// i = integer  
// d = double
// b = blob

// ✅ Use meaningful aliases
$query = "SELECT 
            c.id, 
            c.title,
            u.name as citizen_name,
            o.name as officer_name
          FROM complaints c
          LEFT JOIN users u ON c.user_id = u.id
          LEFT JOIN users o ON c.assigned_officer_id = o.id";

// ❌ AVOID using SELECT * (specify columns)
```

### Schema Design Rules
1. **Primary Keys**: Always use `id` as primary key with AUTO_INCREMENT
2. **Foreign Keys**: Reference with `table_name_id` format (e.g., `user_id`, `complaint_id`)
3. **Timestamps**: Always include `created_at` and `updated_at`
4. **Enums**: Use predefined ENUM values for fixed options (status, role)
5. **Indexes**: Create indexes on frequently queried columns

```sql
-- ✅ GOOD table structure
CREATE TABLE complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    status ENUM('submitted', 'under_review', 'approved', 'rejected', 'investigation', 'closed') DEFAULT 'submitted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);
```

---

## File Organization

### Module Structure
```
citizen/
├── dashboard.php           # Main dashboard
├── submit_complaint.php    # New complaint form
├── my_complaints.php       # List user's complaints
├── view_complaint.php      # Detail view
└── profile.php            # User profile

// Each file should include:
// 1. Config files
// 2. Session check
// 3. POST handlers
// 4. Database queries
// 5. HTML output
```

### Config File Usage
All config files should be required at page top:
```php
<?php
require_once '../config/session.php';      // Session management
require_once '../config/helpers.php';      // Helper functions
require_once '../config/database.php';     // Database connection (optional if autoloaded)

requireRole('citizen');  // Check user role
$user_id = getUserId();  // Get current user
```

---

## Comment Standards

### When to Comment
- Complex algorithms or business logic
- Non-obvious why code exists
- Workarounds or hacks
- Security-critical sections

### Comment Format
```php
// ✅ GOOD comments
// Prevent duplicate complaint submissions within 10 minutes
$last_complaint_time = strtotime($user['last_complaint_at']);
$time_elapsed = time() - $last_complaint_time;
if ($time_elapsed < 600) {
    $_SESSION['error'] = 'Please wait before submitting another complaint';
}

// ❌ AVOID obvious comments
$name = $_POST['name'];  // Get name from POST

// ✅ USE for complex sections
/*
 * Multi-line comment for complex functionality
 * Explains the purpose and logic
 * Can span multiple lines
 */
```

---

## Testing Checklist

### Before Pushing Code

#### Functionality Testing
- [ ] Feature works as intended
- [ ] No console errors (F12 → Console)
- [ ] No PHP warnings/errors in logs
- [ ] Database queries execute correctly
- [ ] File uploads work properly

#### Security Testing
- [ ] Input validation working
- [ ] SQL injection attempts blocked
- [ ] XSS attempts blocked
- [ ] Only intended roles can access page
- [ ] Session expires properly

#### Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (if possible)
- [ ] Mobile view (responsive)

#### Performance Testing
- [ ] Page loads within 2 seconds
- [ ] No console warnings about deprecated APIs
- [ ] Database queries are optimized
- [ ] File sizes reasonable

---

## Git Workflow (if using version control)

### Commit Message Format
```
[TYPE] Brief description

[OPTIONAL] Detailed explanation of changes

Fixes: #123
Related: #456
```

### Types
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation
- `style:` Code style/formatting
- `refactor:` Code refactoring
- `test:` Test-related
- `chore:` Maintenance

### Example
```
feat: Add investigation report creation

- Create create_report.php with draft/submit workflow
- Add report table to database schema
- Implement status management for reports
- Add validation for required fields

Fixes: #42
```

---

## Common Mistakes to Avoid

1. **Hardcoded values** - Use constants instead
   ```php
   // ❌ Wrong
   if ($role == 'admin') { }
   
   // ✅ Right
   if ($role == ROLE_ADMIN) { }
   ```

2. **Missing error handling**
   ```php
   // ❌ Wrong
   $result = $db_conn->query($query);
   
   // ✅ Right
   $result = $db_conn->query($query);
   if (!$result) {
       $_SESSION['error'] = 'Database error occurred';
   }
   ```

3. **No input validation**
   ```php
   // ❌ Wrong
   $email = $_POST['email'];
   
   // ✅ Right
   $email = sanitizeInput($_POST['email']);
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       $_SESSION['error'] = 'Invalid email format';
   }
   ```

4. **Inconsistent naming**
   ```php
   // ❌ Wrong
   $userID, $complaint_ID, $USER_NAME
   
   // ✅ Right
   $user_id, $complaint_id, $user_name
   ```

5. **Direct output of database values**
   ```php
   // ❌ Wrong
   echo $user['comment'];
   
   // ✅ Right
   echo htmlspecialchars($user['comment'], ENT_QUOTES, 'UTF-8');
   ```

---

## Performance Optimization Tips

### Database Optimization
- Use SELECT with specific columns, not SELECT *
- Add indexes to WHERE clause columns
- Avoid N+1 query problems
- Use JOIN instead of multiple queries
- Cache frequently accessed data

### PHP Optimization
- Use functions from helpers.php to avoid duplication
- Cache database connections
- Minimize file I/O operations
- Use appropriate data structures

### Frontend Optimization
- Minify CSS and JavaScript for production
- Compress images
- Use CSS sprites for icons
- Enable browser caching
- Lazy load images if needed

---

## Documentation Requirements

Every new feature should include:
1. **In-code comments** - Explain the "why"
2. **Function documentation** - Use PHPDoc format
3. **README updates** - Document user-facing features
4. **Database schema** - Add/update schema.sql

### PHPDoc Format
```php
/**
 * Get user by email address
 *
 * @param string $email The user's email address
 * @return array|null The user data or null if not found
 * @throws PDOException If database query fails
 */
function getUserByEmail($email) {
    // Implementation
}
```

---

## Code Review Checklist

Before submitting code for review:
- [ ] Code follows naming conventions
- [ ] All inputs validated and sanitized
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] Error handling implemented
- [ ] Functions are documented
- [ ] Code is DRY (Don't Repeat Yourself)
- [ ] Tests pass
- [ ] Performance acceptable
- [ ] Database queries optimized
- [ ] User feedback messages clear
- [ ] Logged activities for audit trail

---

## Quick Reference

### Helper Functions Available
```php
// Security
hashPassword($password)
verifyPassword($password, $hash)
sanitizeInput($data)
escapeHtml($text)

// Database
generateReferenceNumber()
generateFIRNumber()
logActivity($user_id, $complaint_id, $action, $description)

// Display
formatDate($date)
formatStatus($status)
formatPriority($priority)

// File Upload
uploadFile($file_array, $upload_dir)
deleteFile($file_path)

// Validation
validateEmail($email)
validatePhone($phone)
```

### Session Functions
```php
isLoggedIn()
hasRole($role)
requireRole($role)
getCurrentUser()
getUserId()
getUserRole()
```

---

**Last Updated**: 2024  
**Version**: 1.0  
**Status**: Active

For questions about code standards, refer to README.md or TROUBLESHOOTING.md
