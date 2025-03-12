# Flag Forge Package

[![Build Status](https://img.shields.io/github/actions/workflow/status/brann-meius/flag-forge/ci.yml)](https://github.com/brann-meius/flag-forge/actions)
[![codecov](https://codecov.io/gh/brann-meius/flag-forge/graph/badge.svg?token=0XH7AAKHS2)](https://codecov.io/gh/brann-meius/flag-forge)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/d6238aceb07d402f8742d4b0597a5ba7)](https://app.codacy.com/gh/brann-meius/flag-forge/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![License](https://img.shields.io/github/license/brann-meius/flag-forge)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-blue)](https://www.php.net/)

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Getting Started](#getting-started)
- [Installation](#installation)
- [Usage](#usage)
  - [Working with FlagManager](#working-with-flagmanager)
- [Database Integration Example](#database-integration-example)
  - [Table Schema: chat_user](#table-schema-chat_user)
  - [Sample Records](#sample-records)
  - [Example Query: Checking a Specific Permission](#example-query-checking-a-specific-permission)
  - [Example PHP Code Using PDO](#example-php-code-using-pdo)
- [API Reference](#api-reference)
- [Support](#support)
- [License](#license)

## Overview

The `meius/flag-forge` package provides an intuitive API for defining and managing bitwise enumerations in PHP. By
assigning power-of-two values to each flag, the package allows you to combine multiple flags using bitwise operators and
efficiently check individual flags using simple methods.

## Requirements

- PHP >= 8.1

## Getting Started

To get started with the `meius/flag-forge` package, follow the installation instructions below and check out the usage
examples.

## Installation

1. Install the package via Composer:
    ```bash
    composer require meius/flag-forge
    ```

## Usage

Below is an example enum and how to work with the FlagManager.

```php
 enum Permission: int implements Bitwiseable 
 {
     case SendMessages = 1 << 0; // 1
     case DeleteMessages = 1 << 1; // 2
     case AddUsers = 1 << 2; // 4
     case RemoveUsers = 1 << 3; // 8
     case PinMessages = 1 << 4; // 16
     case ManageChat = 1 << 5; // 32
     case ManageModerators = 1 << 6; // 64
 }
 ```

### Working with FlagManager

```php
 use Meius\FlagForge\FlagManager;

 $manager = new FlagManager();
 $manager->getMask(); // Initial mask: 0
 
 // ----------------------------------------------------------------------
 // ADD FLAGS
 // ----------------------------------------------------------------------
 $manager->add(Permission::SendMessages) // - The bit corresponding to SendMessages is not set, so it gets added.
     ->add(Permission::AddUsers) // - The bit for AddUsers is not set, so it is added.
     ->add(Permission::AddUsers) // - Already set.
     ->add(Permission::PinMessages) // - The bit for PinMessages is not set, so it is added.
     ->getMask(); // Expected mask: 21 (SendMessages=1, AddUsers=4, PinMessages=16: 1+4+16=21)
 
 
 // ----------------------------------------------------------------------
 // COMBINE FLAGS
 // ----------------------------------------------------------------------
 $manager->combine(
     Permission::SendMessages, // Already set.
     Permission::DeleteMessages, // Not set; will be added.
     Permission::AddUsers // Already set.
 )->getMask(); // Expected mask: 23 (SendMessages=1, DeleteMessages=2, AddUsers=4, PinMessages=16: 21+2=23)
 
 // ----------------------------------------------------------------------
 // REMOVE FLAGS
 // ----------------------------------------------------------------------
 $manager->remove(Permission::AddUsers) // - Remove AddUsers: bit is set, so it will be removed.
     ->remove(Permission::ManageModerators) // - Remove ManageModerators: bit is not set, so nothing changes.
     ->getMask(); // Expected mask: 19
 
 // ----------------------------------------------------------------------
 // TOGGLE FLAGS
 // ----------------------------------------------------------------------
 $manager->toggle(
     Permission::SendMessages, // Bit is set; toggled off.
     Permission::DeleteMessages, // Bit is set; toggled off.
     Permission::AddUsers, // Bit is not set; toggled on.
     Permission::RemoveUsers // Bit is not set; toggled on.
 )->getMask(); // Expected mask: 28

 // ----------------------------------------------------------------------
 // CHECK FLAGS
 // ----------------------------------------------------------------------
 $manager->has(Permission::SendMessages); // false
 $manager->has(Permission::AddUsers); // true
 $manager->doesntHave(Permission::SendMessages); // true
 $manager->doesntHave(Permission::AddUsers); // false
 
 // ----------------------------------------------------------------------
 // ITERATE OVER ACTIVE FLAGS
 // ----------------------------------------------------------------------
 foreach ($manager as $flag) {
     /**
      * Example output:
      * Active flag: AddUsers (4)
      * Active flag: RemoveUsers (8)
      * Active flag: PinMessages (16)
      */
     echo "Active flag: " . $flag->name . " (" . $flag->value . ")" . PHP_EOL;
 }
 
 // ----------------------------------------------------------------------
 // CLEAR FLAGS
 // ----------------------------------------------------------------------
 $manager->clear(); // Expected mask: 0
 ```

## Database Integration Example

FlagForge can be easily integrated with a database. The following example demonstrates how to store and retrieve a flag
mask using PDO.

### Database Schema and Usage Example

The following section describes the `chat_user` table schema used to store user permissions as a bitmask,
provides a few sample records, and shows an example of how to query the database to check a specific permission.

### Table Schema: chat_user

| Column      | Data Type        | Constraints            | Description                            |
|-------------|------------------|------------------------|----------------------------------------|
| id          | UUID             | PRIMARY KEY            | Unique identifier for the record.      |
| chat_id     | UUID             | FOREIGN KEY, NOT NULL  | Identifier of the chat.                |
| user_id     | UUID             | FOREIGN KEY, NOT NULL  | Identifier of the user.                |
| permissions | UNSIGNED TINYINT | NOT NULL, DEFAULT (17) | Bitmask representing user permissions. |

### Sample Records

Below are a few example rows inserted into the `chat_user` table:

| id                                   | chat_id   | user_id   | permissions |
|--------------------------------------|-----------|-----------|-------------|
| 11111111-1111-1111-1111-111111111111 | chat-1234 | user-1234 | 21          |
| 22222222-2222-2222-2222-222222222222 | chat-1234 | user-5678 | 23          |
| 33333333-3333-3333-3333-333333333333 | chat-4321 | user-9876 | 5           |

### Example Query: Checking a Specific Permission

Suppose you want to check if a specific user in a chat has the "SendMessages" permission.
Assume that the `SendMessages` permission corresponds to the bit value `1` (i.e. `1 << 0`).

The following SQL query uses the bitwise AND operator to verify that the permission bit is set:

```sql
 SELECT *
 FROM chat_user
 WHERE chat_id = :chat_id
   AND user_id = :user_id
   AND (permissions & :flag) = :flag;
 ```

### Example PHP Code Using PDO

Below is an example of how you might execute the above query using a PDO instance (assumed to be available as `$pdo`):

```php
 use Meius\FlagForge\FlagManager;
 
 /**
 * @var PDO $pdo
 * @var FlagManager $manager
 * @var string $chatId Chat ID to query.
 * @var string $userId User ID to query.
 */
 
 $manager->add(Permission::SendMessages);

 // Prepare the SQL statement
 $stmt = $pdo->prepare('
     SELECT *
     FROM chat_user
     WHERE chat_id = :chat_id
       AND user_id = :user_id
       AND (permissions & :flag) = :flag
 ');

 // Execute the query with the parameters
 $stmt->execute([
     ':chat_id' => $chatId,
     ':user_id' => $userId,
     ':flag' => $manager,
 ]);

 // Fetch the result
 $result = $stmt->fetch(PDO::FETCH_ASSOC);

 if ($result) {
     echo "User has the SendMessages permission.";
 } else {
     echo "User does NOT have the SendMessages permission.";
 }
 ```

## API Reference

#### FlagManager

- `add(Bitwiseable $flag): self` — Adds a flag to the current mask.
- `remove(Bitwiseable $flag): self` — Removes a flag from the current mask.
- `combine(Bitwiseable ...$flags): self` — Combines multiple flags into the current mask.
- `toggle(Bitwiseable ...$flags): self` — Toggles specified flags.
- `clear(): self` — Clears all flags in the current mask.
- `has(Bitwiseable $flag): bool` — Checks if the specified flag is present.
- `doesntHave(Bitwiseable $flag): bool` — Checks if the specified flag is not present.
- `getMask(): int` — Returns the current mask value.
- `toArray(): array` — Returns an array representation of the current mask.

## Support

For support, please open an issue on the [GitHub repository](https://github.com/brann-meius/flag-forge/issues).

### Contributing

We welcome contributions to the `meius/flag-forge` library. To contribute, follow these steps:

1. **Fork the Repository**: Fork the repository on GitHub and clone it to your local machine.
2. **Create a Branch**: Create a new branch for your feature or bugfix.
3. **Write Tests**: Write tests to cover your changes.
4. **Run Tests**: Ensure all tests pass by running `phpunit`.
5. **Submit a Pull Request**: Submit a pull request with a clear description of your changes.

For more details, refer to the [CONTRIBUTING.md](CONTRIBUTING.md) file.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).