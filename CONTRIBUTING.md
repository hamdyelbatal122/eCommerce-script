# 🤝 Contributing Guide

Thank you for wanting to contribute to ECommerce Marketplace! We welcome contributions from everyone.

---

## 📋 Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Focus on the code, not the person
- Help others when possible

---

## 🚀 How to Contribute

### 1. Fork the Repository
```bash
git clone https://github.com/yourusername/ecommerce-script.git
cd ecommerce-script
```

### 2. Create a Feature Branch
```bash
git checkout -b feature/your-feature-name
```

### 3. Make Changes
```bash
# Create your feature or fix
# Write clean, documented code
# Test thoroughly
```

### 4. Commit Changes
```bash
git add .
git commit -m "Add: Brief description of changes"
```

### 5. Push to Your Fork
```bash
git push origin feature/your-feature-name
```

### 6. Create Pull Request
- Go to GitHub repository
- Click "Compare & pull request"
- Write clear PR description
- Submit for review

---

## 📝 Commit Message Format

```
Type: Brief description

Detailed explanation if needed.

Issue: #123 (if applicable)
```

### Types:
- **Add**: New feature
- **Fix**: Bug fix
- **Docs**: Documentation
- **Style**: Formatting
- **Refactor**: Code cleanup
- **Test**: Test additions
- **Chore**: Maintenance

### Examples:
```
Add: User profile editing feature
Fix: Database connection timeout issue
Docs: Update README with examples
Style: Format code to PSR-12
Refactor: Simplify validation logic
Test: Add unit tests for User model
Chore: Update dependencies
```

---

## 🎨 Code Standards

### 1. PHP Code Style (PSR-12)
```php
<?php
// Namespace and imports at top
namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

// Class declaration
class User extends BaseModel
{
    // Properties
    protected $table = 'users';
    
    // Methods with proper indentation
    public function getActive()
    {
        return $this->where('status', 1)->get();
    }
}
```

### 2. Naming Conventions
- **Classes**: `PascalCase` (UserController)
- **Methods/Functions**: `camelCase` (getUserData)
- **Variables**: `camelCase` ($userName)
- **Constants**: `UPPER_CASE` (DB_HOST)

### 3. Comments & Documentation
```php
/**
 * Get all active users
 * 
 * @return array Array of users
 */
public function getActiveUsers()
{
    // Implementation
}
```

### 4. HTML/CSS
```html
<!-- Clear, readable HTML -->
<div class="card shadow-sm">
    <h3 class="card-title">Title</h3>
    <p class="card-text">Content</p>
</div>
```

### 5. JavaScript
```javascript
// Clear, documented code
function getUserData(userId) {
    // Implementation
    return {/* ... */};
}
```

---

## ✅ Before Submitting PR

- [ ] Code follows PSR-12 style
- [ ] All comments are clear
- [ ] No console.log() statements
- [ ] No hardcoded values
- [ ] No unnecessary files included
- [ ] Tests pass (if applicable)
- [ ] Documentation updated
- [ ] README updated if needed
- [ ] Works locally before pushing

---

## 🧪 Testing

### Create Tests
```php
// Test your code thoroughly
$item = (new Item())->find(1);
assert($item['name'] === 'Test Item');
```

### Manual Testing
1. Test on local environment
2. Test on different browsers
3. Test on mobile
4. Test error cases
5. Verify database operations

---

## 📚 Documentation

### Update README if:
- Adding new features
- Changing setup process
- Adding new routes
- Changing configuration

### Format:
- Use markdown
- Include code examples
- Clear and concise
- Add screenshots if UI changes

---

## 🔍 PR Review Process

1. **Automated Checks**
   - Code style validation
   - File changes review
   - Conflicts check

2. **Manual Review**
   - Code quality
   - Best practices
   - Logic correctness
   - Documentation

3. **Testing**
   - Feature functionality
   - Edge cases
   - Performance impact
   - Database migrations

4. **Approval & Merge**
   - Address feedback
   - Request re-review if needed
   - Merge when approved

---

## 🐛 Reporting Bugs

### Create Issue with:
- **Title**: Clear, descriptive
- **Description**: What happened
- **Steps to Reproduce**: How to recreate
- **Expected**: What should happen
- **Actual**: What happened
- **Environment**: PHP version, OS, browser
- **Screenshots**: If applicable

### Example:
```
Title: User can't upload large files

Description:
When uploading files larger than 5MB, 
the upload fails silently.

Steps:
1. Create new item
2. Try to upload 10MB image
3. Click submit

Expected:
File uploads or shows error

Actual:
Form submits but file not uploaded

Environment:
PHP 7.4.3, Ubuntu 20.04, Chrome 90
```

---

## 💡 Suggesting Features

- Describe the feature clearly
- Explain the use case
- Show examples if possible
- Be open to discussion

---

## 📦 New Dependencies

Before adding dependencies:
- Discuss in issue first
- Consider stability
- Check security record
- Minimize external deps

---

## 🎯 Areas We Need Help

- [ ] Tests & test coverage
- [ ] Performance optimization
- [ ] Documentation improvement
- [ ] UI/UX enhancements
- [ ] Security hardening
- [ ] Mobile optimization
- [ ] API improvements
- [ ] Internationalization

---

## 📞 Getting Help

- **Questions**: Create issue with [QUESTION] tag
- **Discussion**: Check existing issues
- **Chat**: GitHub discussions
- **Email**: Contact maintainer

---

## 🏆 Recognition

Contributors will be:
- Added to CONTRIBUTORS file
- Thanked in releases
- Given credit in docs
- Recognized on GitHub

---

## 📋 License

By contributing, you agree that:
- Your contributions are licensed under MIT
- You have right to grant this license
- You grant patent rights if applicable

---

## 🎓 Learning Resources

- **PHP**: https://www.php.net/manual/
- **MVC**: https://en.wikipedia.org/wiki/Model–view–controller
- **PSR-12**: https://www.php-fig.org/psr/psr-12/
- **Git**: https://git-scm.com/book/
- **GitHub**: https://guides.github.com/

---

## ❓ FAQ

**Q: Can I contribute if I'm a beginner?**
A: Absolutely! Start with documentation or small fixes.

**Q: How long does review take?**
A: Usually 1-7 days depending on complexity.

**Q: Can I work on multiple features?**
A: Yes, separate branch for each feature.

**Q: Do I need to add tests?**
A: Appreciated but not required initially.

**Q: How are conflicts resolved?**
A: Maintainer may request changes or rebase.

---

## 🚀 Thank You!

Every contribution makes ECommerce Marketplace better. 

**Happy Coding!** 💻

---

*For questions, create an issue or contact maintainers.*
