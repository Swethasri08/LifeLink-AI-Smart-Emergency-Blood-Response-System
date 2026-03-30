# 🤝 Contributing to Blood Donation Management System

Thank you for your interest in contributing to the Blood Donation Management System (BDMS)! This document provides guidelines and information for contributors.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contributing Guidelines](#contributing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Documentation](#documentation)

## 🤝 Code of Conduct

### Our Pledge
We are committed to making participation in this project a harassment-free experience for everyone, regardless of level of experience, gender, gender identity and expression, sexual orientation, disability, personal appearance, body size, race, ethnicity, age, religion, or nationality.

### Our Standards
- Use welcoming and inclusive language
- Be respectful of different viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+ with required extensions
- MySQL/MariaDB 10.4+
- Composer
- Git
- Basic understanding of PHP, MySQL, and web technologies

### Development Environment Setup
1. Fork the repository
2. Clone your fork locally
3. Set up a local development environment
4. Install dependencies
5. Configure your database

## 🛠️ Development Setup

### 1. Clone and Setup
```bash
git clone https://github.com/YOUR-USERNAME/LifeLink-AI-Smart-Emergency-Blood-Response-System.git
cd LifeLink-AI-Smart-Emergency-Blood-Response-System
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
```bash
mysql -u root -p < database_complete.sql
```

### 4. Configuration
Copy and configure the database settings:
```bash
cp config/database.php.example config/database.php
# Edit config/database.php with your database credentials
```

### 5. Start Development Server
```bash
php -S localhost:8000
```

## 📝 Contributing Guidelines

### Types of Contributions
We welcome the following types of contributions:

#### 🐛 Bug Reports
- Use the GitHub issue tracker
- Provide detailed information about the bug
- Include steps to reproduce
- Add screenshots if applicable

#### ✨ Feature Requests
- Open an issue with the "enhancement" label
- Describe the feature and its use case
- Explain why it would be valuable

#### 💻 Code Contributions
- Bug fixes
- New features
- Performance improvements
- Code refactoring
- Documentation improvements

#### 📖 Documentation
- README improvements
- Code comments
- User guides
- API documentation

### Before You Start
1. Check existing issues and pull requests
2. Discuss major changes in an issue first
3. Ensure your contribution aligns with project goals

## 🔀 Pull Request Process

### 1. Create a Branch
```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

### 2. Make Your Changes
- Follow the coding standards
- Add tests for new functionality
- Update documentation as needed
- Keep changes focused and atomic

### 3. Test Your Changes
- Test manually in the application
- Run automated tests (if available)
- Ensure all existing tests pass
- Test on different browsers if UI changes

### 4. Commit Your Changes
```bash
git add .
git commit -m "feat: add new feature description"
# or
git commit -m "fix: resolve issue description"
```

### 5. Push and Create Pull Request
```bash
git push origin feature/your-feature-name
```
Then create a pull request on GitHub.

### Pull Request Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Manual testing completed
- [ ] Automated tests pass
- [ ] Cross-browser tested (if applicable)

## Checklist
- [ ] Code follows project standards
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes (or clearly documented)
```

## 📏 Coding Standards

### PHP Standards
- Follow PSR-12 coding style
- Use meaningful variable and function names
- Add proper comments and documentation
- Keep functions small and focused
- Use type hints where appropriate

### Example:
```php
<?php
/**
 * Process blood donation request
 * 
 * @param int $requestId Request ID
 * @param string $status New status
 * @return bool Success status
 */
public function processRequest(int $requestId, string $status): bool
{
    // Validate inputs
    if (empty($requestId) || empty($status)) {
        return false;
    }
    
    // Process request
    return $this->updateRequestStatus($requestId, $status);
}
```

### Database Standards
- Use lowercase table and column names
- Add proper indexes for performance
- Use foreign key constraints
- Include created_at and updated_at timestamps
- Use descriptive column names

### Frontend Standards
- Use semantic HTML5 tags
- Follow Bootstrap conventions
- Add proper alt text for images
- Ensure accessibility compliance
- Use responsive design principles

### JavaScript Standards
- Use modern ES6+ syntax
- Add proper error handling
- Use meaningful variable names
- Keep functions small and focused
- Add JSDoc comments

## 🧪 Testing Guidelines

### Manual Testing
- Test all user flows
- Verify database operations
- Check error handling
- Test on different screen sizes
- Validate form inputs

### Automated Testing (Future)
- Unit tests for business logic
- Integration tests for API endpoints
- Database tests
- Frontend component tests

### Testing Checklist
- [ ] Login/logout functionality
- [ ] User registration
- [ ] Database operations
- [ ] Form validation
- [ ] Error handling
- [ ] Responsive design
- [ ] Cross-browser compatibility

## 📚 Documentation

### Code Documentation
- Add PHPDoc blocks for functions and classes
- Comment complex logic
- Document database schema changes
- Update README for new features

### User Documentation
- Update user guides
- Add screenshots for new features
- Update installation instructions
- Document configuration options

### API Documentation
- Document all API endpoints
- Include request/response examples
- Document error codes
- Add authentication requirements

## 🏷️ Issue Labels

- `bug` - Bug reports
- `enhancement` - Feature requests
- `documentation` - Documentation issues
- `good first issue` - Good for newcomers
- `help wanted` - Community help needed
- `priority: high` - High priority issues
- `priority: medium` - Medium priority issues
- `priority: low` - Low priority issues

## 🎯 Release Process

1. **Development**: Features developed on feature branches
2. **Testing**: Thorough testing and code review
3. **Integration**: Merged to develop branch
4. **Release**: Created from develop branch
5. **Deployment**: Deployed to production

## 📞 Getting Help

- **GitHub Issues**: For bug reports and feature requests
- **Discussions**: For general questions and ideas
- **Email**: For private or security-related issues

## 🙏 Recognition

Contributors will be recognized in:
- README.md contributors section
- Release notes
- Annual contributor highlights
- Special contributor badges

## 📄 License

By contributing to this project, you agree that your contributions will be licensed under the same license as the project.

---

Thank you for contributing to the Blood Donation Management System! Your contributions help save lives by improving blood donation management. 🩸❤️
