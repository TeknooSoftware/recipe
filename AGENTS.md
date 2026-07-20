# Project: Recipe

## Overview
A PHP library inspired by cooking for creating dynamic algorithms. It allows for highly configurable, middleware-driven workflows (recipes) based on the availability of certain conditions (ingredients).

## Core Concepts

### Recipe
The central object representing the algorithm or workflow. It can be built by requiring certain ingredients and cooking with specific logic.

### Chef
The engine that executes a `Recipe`. It processes the requirements (ingredients) and manages the state/execution flow.

### Ingredient
The inputs or requirements for a recipe. Ingredients can be simple values or more complex objects. They can be:
- **Required**: Must be present for the recipe to proceed.
- **Mergable**: Can be merged into existing ingredients rather than replacing them.
- **Transformable**: Can be modified before being added to the execution context.

### Dish
The final outcome or result of a successfully executed recipe.

### Bowl
A container that holds the current state and ingredients during the cooking process. It supports asynchronous/non-blocking execution through PHP `Fiber`.

### Plan
A sequence of steps that constitute a recipe. It allows for fine-grained control over the execution flow.

### Promise
Represents an asynchronous operation or the eventual result of a step. It handles both successful values and errors.

### CookingSupervisor
An orchestrator that manages the execution of multiple `Bowl` instances, particularly when using PHP Fibers for concurrent/non-blocking execution.

## Key Technical Features

- **Middleware Pattern**: Recipes are composed of steps that act like middleware, transforming the context.
- **Asynchronous Execution**: Native support for PHP `Fiber` within `Promise` and `Bowl` to allow non-blocking, concurrent operations.
- **Highly Configurable**: Designed to be easily integrated via Dependency Injection.
- **Strongly Typed**: Utilizes modern PHP features (PHP 8.1+) including `readonly` properties and strict typing.

## Technical Stack

- **Language**: PHP 8.1+
- **Testing**:
  - [PHPUnit](https://phpunit.de/) (Unit testing)
  - [Behat](https://behat.org/) (Behavior-driven development)
- **Key Dependencies**:
  - `teknoo/immutable`
  - `teknoo/states`

## Development Guidelines

### Coding Standards
- Follow [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
- All files must use `declare(strict_types=1);`.

### Testing Requirements
- All new features/fixes must include corresponding tests.
- Unconfirmed issues must be accompanied by a failing test case.
- Minimum code coverage for new contributions is 90%.

### Running Tests and QA
The project uses a `Makefile` to automate common tasks.

**Testing:**
To run the test suite (PHPUnit and Behat):
```bash
make test
```

**Quality Assurance:**
To run linting, static analysis, and security audits:
```bash
make qa
```

### Workflow
1. Create a new branch for hotfixes or features.
2. Implement changes.
3. Write and run tests.
4. Submit a Pull Request.
