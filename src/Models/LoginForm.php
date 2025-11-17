<?php

namespace App\Models;

class LoginForm
{
    private string $firstName = '';
    private string $secondName = '';
    private string $email = '';
    private string $password = '';

    private array $errors = [];

    public function loadData(array $data): void
    {
        $this->setFirstName($data['first_name'] ?? '');
        $this->setSecondName($data['second_name'] ?? '');
        $this->setEmail($data['email'] ?? '');
        $this->setPassword($data['password'] ?? '');
    }

    // === SETTERS (control how data is stores) ===
    public function setFirstName(string $name): void
    {
        // Trim whitespace
        $name = trim($name);

        // Remove tags
        $name = strip_tags($name);

        // Normalize spaces
        $name = preg_replace('/\s+/', ' ', $name);

        $this->firstName = $name;
    }

    public function setSecondName(string $secondName): void
    {
        // Trim whitespace
        $secondName = trim($secondName);

        // Remove tags
        $secondName = strip_tags($secondName);

        // Normalize spaces
        $secondName = preg_replace('/\s+/', ' ', $secondName);

        $this->secondName = $secondName;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $this->email = strtolower($email);
    }


    public function setPassword(string $password): void
    {
        $password = trim($password);

        if (!empty($password)) {
            // Hash the password securely
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $this->password = '';
        }
    }

    // === GETTERS (control how data is accessed) ===
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getSecondName(): string
    {
        return $this->secondName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    // === VALIDATION ===

    public function validate(): bool
    {
        $this->errors = [];

        if (empty($this->firstName)) {
            $this->errors['first_name'] = 'First name is required.';
        }

        if (empty($this->secondName)) {
            $this->errors['second_name'] = 'Second name is required.';
        }

        if (empty($this->email)) {
            $this->errors['email'] = 'Email is required.';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format.';
        }

        if (empty($this->password)) {
            $this->errors['password'] = 'Password is required.';
        }

        return empty($this->errors);
    }

    // Expose errors safely
    public function getErrors(): array
    {
        return $this->errors;
    }
}
