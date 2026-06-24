# VibeFlow Testing Guide

This document outlines the testing setup for VibeFlow, including both backend API tests (PHPUnit) and frontend tests (Vitest).

## Backend Testing (PHPUnit)

### Setup

Install PHPUnit via Composer:
```bash
composer require --dev phpunit/phpunit
```

### Directory Structure

```
tests/
├── Unit/           # Unit tests for individual components
├── Feature/        # Feature tests for API endpoints
└── bootstrap.php   # PHPUnit bootstrap file
```

### Running Tests

Run all tests:
```bash
./vendor/bin/phpunit
```

Run specific test suite:
```bash
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Feature
```

Run with coverage report:
```bash
./vendor/bin/phpunit --coverage-html coverage/
```

### Writing Unit Tests

Create a new test file in `tests/Unit/`:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function test_something(): void
    {
        $this->assertTrue(true);
    }
}
```

### Writing Feature Tests

Create a new test file in `tests/Feature/`:

```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase
{
    public function test_api_endpoint(): void
    {
        // Test API endpoints
        $this->assertTrue(true);
    }
}
```

## Frontend Testing (Vitest)

### Setup

Install dependencies:
```bash
cd frontend
npm install --save-dev vitest @vitest/ui jsdom @vue/test-utils
```

### Directory Structure

```
frontend/tests/
├── unit/          # Unit tests for components and utilities
├── integration/   # Integration tests
└── setup.ts       # Test setup file
```

### Running Tests

Run all tests:
```bash
cd frontend
npm run test
```

Run tests in watch mode:
```bash
npm run test:watch
```

Run tests with UI:
```bash
npm run test:ui
```

Generate coverage report:
```bash
npm run test:coverage
```

### Writing Unit Tests

Create a new test file in `frontend/tests/unit/`:

```typescript
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MyComponent from '@/components/MyComponent.vue'

describe('MyComponent', () => {
  it('renders properly', () => {
    const wrapper = mount(MyComponent, {
      props: {
        msg: 'Hello Vitest',
      },
    })
    expect(wrapper.text()).toContain('Hello Vitest')
  })
})
```

### Writing Integration Tests

Create a new test file in `frontend/tests/integration/`:

```typescript
import { describe, it, expect, beforeEach } from 'vitest'

describe('My Integration Test', () => {
  beforeEach(() => {
    // Setup before each test
  })

  it('should do something', () => {
    expect(true).toBe(true)
  })
})
```

## Configuration Files

- **Backend**: `phpunit.xml` - PHPUnit configuration
- **Frontend**: `frontend/vitest.config.ts` - Vitest configuration

## Best Practices

### Backend (PHPUnit)
- Keep unit tests focused on single components
- Use feature tests for integration testing
- Mock external dependencies
- Aim for >80% code coverage
- Use descriptive test names

### Frontend (Vitest)
- Test component behavior, not implementation
- Use descriptive test names
- Group related tests with `describe` blocks
- Mock API calls and external dependencies
- Test user interactions, not just rendering

## CI/CD Integration

Add test commands to your CI/CD pipeline:

```yaml
# Example GitHub Actions
- name: Run PHP Tests
  run: ./vendor/bin/phpunit

- name: Run Frontend Tests
  run: cd frontend && npm run test
```

## Coverage Reports

- **PHP**: Generated in `coverage/` directory
- **Frontend**: Generated in `frontend/coverage/` directory

Visit the HTML reports to see detailed coverage information.
