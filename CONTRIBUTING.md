# 🤝 CONTRIBUTING GUIDELINES
## Sistem Absensi SMKN 1 Punggelan

*Panduan untuk berkontribusi pada project ini*

---

## 📋 **TABLE OF CONTENTS**
1. [Getting Started](#getting-started)
2. [Development Workflow](#development-workflow)
3. [Code Standards](#code-standards)
4. [Testing](#testing)
5. [Commit Guidelines](#commit-guidelines)
6. [Pull Request Process](#pull-request-process)
7. [Issue Reporting](#issue-reporting)
8. [Code Review Process](#code-review-process)

---

## 🚀 **GETTING STARTED**

### **Prerequisites**
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- Git
- SQLite or PostgreSQL

### **Setup Development Environment**
```bash
# Clone repository
git clone https://github.com/idiarso4/absensi-face-recognition.git
cd absensi-face-recognition

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Setup Filament
php artisan shield:install
php artisan shield:generate --all --panel=admin --no-interaction

# Build assets
npm run dev
```

### **Verify Setup**
```bash
# Start development server
php artisan serve

# Visit these URLs to verify:
# - http://localhost:8000 (Welcome page)
# - http://localhost:8000/register (Registration)
# - http://localhost:8000/admin/login (Admin panel)
```

---

## 🔄 **DEVELOPMENT WORKFLOW**

### **1. Choose an Issue**
- Check [TODO.md](TODO.md) for available tasks
- Look at GitHub Issues for bug reports and feature requests
- Comment on the issue to indicate you're working on it

### **2. Create a Branch**
```bash
# For features
git checkout -b feature/description-of-feature

# For bug fixes
git checkout -b bugfix/description-of-bug

# For hotfixes
git checkout -b hotfix/description-of-fix
```

### **3. Make Changes**
- Follow the code standards outlined below
- Write tests for new functionality
- Update documentation if needed
- Ensure all tests pass

### **4. Commit Changes**
```bash
# Stage your changes
git add .

# Commit with descriptive message
git commit -m "feat: add user profile management

- Add profile edit form
- Implement avatar upload
- Add validation rules
- Update user model

Closes #123"
```

### **5. Push and Create Pull Request**
```bash
# Push your branch
git push origin feature/your-feature-name

# Create Pull Request on GitHub
# Fill out the PR template
# Request review from maintainers
```

---

## 📏 **CODE STANDARDS**

### **PHP Standards**
- Follow PSR-12 coding standards
- Use type hints for method parameters and return values
- Add PHPDoc comments for classes, methods, and properties
- Use meaningful variable and method names

```php
/**
 * Create a new attendance record
 *
 * @param array $data
 * @return Attendance
 * @throws ValidationException
 */
public function createAttendance(array $data): Attendance
{
    // Implementation
}
```

### **Laravel Conventions**
- Use Eloquent relationships properly
- Implement proper validation in Form Requests
- Use Resource classes for API responses
- Follow RESTful routing conventions

### **JavaScript/Vue Standards**
- Use ES6+ syntax
- Follow Vue.js style guide
- Use meaningful component and variable names
- Add JSDoc comments for complex functions

### **CSS/Tailwind Standards**
- Use Tailwind utility classes
- Follow mobile-first responsive design
- Maintain consistent spacing and colors
- Use CSS custom properties for theme variables

---

## 🧪 **TESTING**

### **Testing Requirements**
- Write tests for all new features
- Maintain >80% code coverage
- Test both happy path and error scenarios
- Use descriptive test method names

### **Running Tests**
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/UserRegistrationTest.php

# Run tests in parallel
php artisan test --parallel
```

### **Test Structure**
```php
// Feature test example
class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'is_approved' => false,
        ]);
        $response->assertSessionHas('status');
    }
}
```

---

## 📝 **COMMIT GUIDELINES**

### **Commit Message Format**
```
type(scope): description

[optional body]

[optional footer]
```

### **Types**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### **Examples**
```bash
# Feature commit
feat(auth): add email verification system

- Add email verification routes
- Create verification email template
- Implement verification logic
- Add email_verified_at column

# Bug fix commit
fix(attendance): resolve GPS validation error

Fix issue where GPS coordinates were not properly validated
when user location is outside office radius.

Closes #456

# Documentation commit
docs(api): update attendance endpoint documentation

Add missing parameters and response examples
for attendance creation endpoint.
```

### **Commit Best Practices**
- Use present tense ("add" not "added")
- Keep first line under 50 characters
- Use detailed body for complex changes
- Reference issue numbers when applicable
- Squash related commits before merging

---

## 🔄 **PULL REQUEST PROCESS**

### **PR Template**
When creating a PR, fill out this template:

```markdown
## Description
Brief description of the changes made.

## Type of Change
- [ ] Bug fix (non-breaking change)
- [ ] New feature (non-breaking change)
- [ ] Breaking change
- [ ] Documentation update

## Checklist
- [ ] My code follows the project's style guidelines
- [ ] I have performed a self-review of my own code
- [ ] I have added tests that prove my fix/feature works
- [ ] I have updated the documentation
- [ ] My changes generate no new warnings
- [ ] All tests pass locally

## Screenshots (if applicable)
Add screenshots of UI changes.

## Additional Notes
Any additional information or context.
```

### **PR Review Process**
1. **Automated Checks**: CI/CD runs tests and linting
2. **Code Review**: At least one maintainer reviews the code
3. **Testing**: Reviewer tests the functionality
4. **Approval**: PR approved and merged
5. **Deployment**: Changes deployed to staging/production

### **PR Size Guidelines**
- Keep PRs small and focused
- Maximum 500 lines of code per PR
- Split large features into multiple PRs
- Use draft PRs for work-in-progress

---

## 🐛 **ISSUE REPORTING**

### **Bug Reports**
When reporting bugs, include:

```markdown
## Bug Description
Clear and concise description of the bug.

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. See error

## Expected Behavior
What should happen.

## Actual Behavior
What actually happens.

## Environment
- OS: [e.g., Ubuntu 22.04]
- Browser: [e.g., Chrome 91]
- PHP Version: [e.g., 8.2]
- Laravel Version: [e.g., 11.x]

## Additional Context
Screenshots, error logs, etc.
```

### **Feature Requests**
For feature requests, include:

```markdown
## Feature Summary
Brief description of the feature.

## Problem Statement
What problem does this solve?

## Proposed Solution
How should it work?

## Alternatives Considered
Other solutions you've considered.

## Additional Context
Mockups, examples, etc.
```

---

## 👁️ **CODE REVIEW PROCESS**

### **Review Checklist**
- [ ] Code follows project standards
- [ ] Tests are included and passing
- [ ] Documentation is updated
- [ ] No security vulnerabilities
- [ ] Performance considerations addressed
- [ ] Database changes are properly migrated

### **Review Comments**
- Be constructive and respectful
- Explain reasoning behind suggestions
- Provide examples when possible
- Focus on code quality and maintainability

### **Review Guidelines for Reviewers**
- Review within 24-48 hours
- Test the changes locally when possible
- Check for edge cases and error handling
- Verify database changes
- Ensure proper validation and security

---

## 🎯 **CONTRIBUTION AREAS**

### **Beginner Friendly**
- Writing tests
- Updating documentation
- Fixing typos and small bugs
- Improving UI/UX
- Adding translations

### **Intermediate**
- Implementing new features
- API development
- Database optimization
- Performance improvements
- Security enhancements

### **Advanced**
- Architecture decisions
- Complex algorithms
- Third-party integrations
- DevOps and deployment
- System design

---

## 📚 **RESOURCES**

### **Learning Resources**
- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [PHP Standards](https://www.php-fig.org/psr/psr-12/)
- [Testing Best Practices](https://phpunit.readthedocs.io/)

### **Tools**
- [PHPStorm](https://www.jetbrains.com/phpstorm/) - IDE
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debugging
- [Postman](https://www.postman.com/) - API testing
- [GitHub Desktop](https://desktop.github.com/) - Git GUI

---

## 📞 **GETTING HELP**

### **Communication Channels**
- **GitHub Issues**: For bugs and feature requests
- **GitHub Discussions**: For questions and general discussion
- **Email**: idiarsosimbang@gmail.com for urgent matters

### **Response Times**
- Issues: Acknowledged within 24 hours
- PR Reviews: Within 48 hours
- Urgent Security Issues: Immediate response

---

## 🎉 **RECOGNITION**

Contributors will be recognized through:
- GitHub contributor statistics
- Mention in release notes
- Attribution in documentation
- Special recognition for significant contributions

---

## 📋 **CODE OF CONDUCT**

### **Our Standards**
- Be respectful and inclusive
- Focus on constructive feedback
- Maintain professional communication
- Respect differing viewpoints
- Show empathy towards other community members

### **Unacceptable Behavior**
- Harassment or discriminatory language
- Personal attacks or insults
- Trolling or disruptive comments
- Publishing private information
- Any other unethical behavior

---

*Thank you for contributing to Sistem Absensi SMKN 1 Punggelan! Your contributions help make this system better for the school community.*