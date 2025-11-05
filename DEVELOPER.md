# Developer Documentation

Development guidelines and procedures for andW Fixed Footer plugin.

## Development Environment Setup

### Prerequisites

- **PHP**: 7.4 or higher with `php-cli`
- **WordPress**: 5.0+ development environment
- **Composer**: For dependency management (optional)
- **Node.js**: For build tools and automation (optional)

### Required Tools

1. **PHPCS (PHP CodeSniffer)**
   ```bash
   composer global require "squizlabs/php_codesniffer=*"
   composer global require wp-coding-standards/wpcs
   phpcs --config-set installed_paths /path/to/wpcs
   ```

2. **WordPress Plugin Check**
   - Install via WordPress admin: [Plugin Check](https://wordpress.org/plugins/plugin-check/)
   - Or use WP-CLI: `wp plugin install plugin-check --activate`

3. **PHP Lint**
   ```bash
   # Built into PHP, no installation required
   php -l filename.php
   ```

## Code Quality Validation

### PHPCS (WordPress Coding Standards)

```bash
# Check entire plugin
phpcs --standard=WordPress andw-fixed-footer.php

# Check with detailed reporting
phpcs --standard=WordPress --report=full andw-fixed-footer.php

# Auto-fix minor issues
phpcbf --standard=WordPress andw-fixed-footer.php
```

### Plugin Check Tool

```bash
# Via WP-CLI
wp plugin-check run andw-fixed-footer

# Via WordPress admin
# Go to Tools > Plugin Check > Select Plugin > Run Check
```

### PHP Syntax Check

```bash
# Check main plugin file
php -l andw-fixed-footer.php

# Check all PHP files
find . -name "*.php" -exec php -l {} \;
```

## Testing Procedures

### Manual Testing Checklist

#### Functional Testing
- [ ] Plugin activation/deactivation
- [ ] Settings page accessibility (`Settings > Fixed Footer`)
- [ ] Font Awesome detection functionality
- [ ] Button configuration (colors, icons, links)
- [ ] Address bar configuration
- [ ] Close button functionality
- [ ] Responsive behavior (mobile/desktop)
- [ ] Scroll-based show/hide behavior

#### Security Testing
- [ ] Nonce verification on form submissions
- [ ] Capability checks (`manage_options`)
- [ ] Input sanitization testing
- [ ] Output escaping verification
- [ ] Direct file access prevention

#### Compatibility Testing
- [ ] WordPress 5.0+ compatibility
- [ ] PHP 7.4+ compatibility
- [ ] Theme compatibility testing
- [ ] Plugin conflict testing
- [ ] Mobile browser testing (iOS Safari, Android Chrome)

### Browser Testing Matrix

| Browser | Version | Status |
|---------|---------|--------|
| Chrome (Android) | Latest | ✅ Primary |
| Safari (iOS) | Latest | ✅ Primary |
| Chrome (Desktop) | Latest | ✅ Secondary |
| Firefox (Desktop) | Latest | ✅ Secondary |
| Edge | Latest | ✅ Secondary |

## Build and Deployment

### Pre-release Checklist

1. **Code Quality**
   ```bash
   # Run all checks
   php -l andw-fixed-footer.php
   phpcs --standard=WordPress andw-fixed-footer.php
   wp plugin-check run andw-fixed-footer
   ```

2. **Version Management**
   - Update version in `andw-fixed-footer.php` header
   - Update `ANDW_FIXED_FOOTER_VERSION` constant
   - Update `Stable tag` in `readme.txt`
   - Add entry to `CHANGELOG.md`

3. **Documentation**
   - Update `README.md` if needed
   - Verify `readme.txt` accuracy
   - Check technical documentation

### WordPress.org Submission Process

#### Preparation Steps

1. **Package Creation**
   ```bash
   # Create distribution package (exclude development files)
   git archive --format=zip --output=../andw-fixed-footer.zip --prefix=andw-fixed-footer/ HEAD
   ```

2. **Final Validation**
   ```bash
   # Extract and test the package
   unzip andw-fixed-footer.zip
   cd andw-fixed-footer
   php -l andw-fixed-footer.php
   ```

3. **Submission Requirements**
   - [ ] Unique plugin name/slug
   - [ ] Complete `readme.txt` with proper formatting
   - [ ] All code follows WordPress standards
   - [ ] No security vulnerabilities
   - [ ] No trademark violations
   - [ ] GPL-compatible licensing

#### Submission Process

1. **Initial Submission**
   - Visit [WordPress.org Plugin Submission](https://wordpress.org/plugins/developers/add/)
   - Upload ZIP package
   - Fill out submission form
   - Wait for review (typically 1-2 weeks)

2. **Review Process**
   - Monitor email for review feedback
   - Address any review comments promptly
   - Resubmit if requested
   - Plugin slug reservation upon approval

3. **SVN Repository Setup**
   ```bash
   # Checkout SVN repository (post-approval)
   svn checkout https://plugins.svn.wordpress.org/andw-fixed-footer

   # Upload files to trunk
   svn add trunk/*
   svn commit -m "Initial plugin submission"

   # Create release tag
   svn copy trunk tags/0.2.1
   svn commit -m "Tagging version 0.2.1"
   ```

## CI/CD Implementation Plan

### Phase 1: Basic Automation

#### GitHub Actions Setup

```yaml
# .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: [7.4, 8.0, 8.1, 8.2]

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}

    - name: PHP Lint
      run: php -l andw-fixed-footer.php

    - name: PHPCS
      run: |
        composer global require squizlabs/php_codesniffer
        composer global require wp-coding-standards/wpcs
        phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
        phpcs --standard=WordPress andw-fixed-footer.php
```

#### Automated Checks

- **PHP Lint**: Syntax validation across PHP versions
- **PHPCS**: WordPress coding standards compliance
- **File Structure**: Verify required files exist
- **Version Consistency**: Check version number alignment

### Phase 2: Advanced Automation

#### Automated Testing

```yaml
# WordPress integration testing
- name: WordPress Integration Test
  run: |
    # Setup WordPress test environment
    bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
    # Run PHPUnit tests (when implemented)
    phpunit
```

#### Automated Deployment

```yaml
# Deploy to WordPress.org SVN
- name: Deploy to WordPress.org
  if: startsWith(github.ref, 'refs/tags/')
  run: |
    # Automated SVN deployment script
    bash bin/deploy-to-svn.sh
  env:
    SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
    SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
```

### Phase 3: Quality Monitoring

#### Automated Quality Checks

- **Security Scanning**: Automated vulnerability detection
- **Performance Testing**: Page load impact measurement
- **Compatibility Testing**: Multi-version WordPress testing
- **Cross-browser Testing**: Automated browser compatibility

#### Monitoring and Alerts

- **Download Statistics**: WordPress.org metrics tracking
- **Error Reporting**: User error feedback collection
- **Performance Monitoring**: Real-world usage metrics

## Release Management

### Version Numbering

Following [Semantic Versioning](https://semver.org/):

- **MAJOR**: Incompatible API changes
- **MINOR**: New functionality (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Process

1. **Development** (feature branches)
2. **Code Review** (pull requests)
3. **Quality Assurance** (automated + manual testing)
4. **Release Candidate** (staging deployment)
5. **Production Release** (WordPress.org deployment)

### Hotfix Process

1. **Critical Issue Identification**
2. **Immediate Fix Development**
3. **Emergency Testing**
4. **Expedited Release**
5. **Post-Release Monitoring**

## Contributing Guidelines

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use proper function prefixing (`andw_fixed_footer_`)
- Include PHPDoc comments for all functions
- Maintain security best practices

### Git Workflow

```bash
# Feature development
git checkout -b feature/new-feature
git add .
git commit -m "Add new feature: description"
git push origin feature/new-feature

# Create pull request for review
```

### Code Review Checklist

- [ ] Code follows WordPress standards
- [ ] Security best practices implemented
- [ ] Proper error handling
- [ ] Documentation updated
- [ ] Tests passing
- [ ] No breaking changes

## Troubleshooting

### Common Development Issues

#### PHPCS Errors
```bash
# View detailed error report
phpcs --standard=WordPress --report=full andw-fixed-footer.php

# Common fixes
phpcbf --standard=WordPress andw-fixed-footer.php
```

#### Plugin Check Failures
- Review the specific error messages
- Check WordPress.org plugin guidelines
- Verify all security practices

#### Version Conflicts
- Ensure version numbers match across all files
- Update CHANGELOG.md appropriately
- Test with clean WordPress installation

### Support Resources

- [WordPress Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Plugin Review Guidelines](https://make.wordpress.org/plugins/handbook/)
- [WordPress.org Support Forums](https://wordpress.org/support/)