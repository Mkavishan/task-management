# Task Management System

Please follow the below steps to run this application:

1. Download the repository from here: [Git Repo](https://github.com/Mkavishan/task-management.git)

2. Run the following commands inside the project root folder:

    ```bash
    $ php artisan composer install
    $ php artisan migrate
    $ php artisan serve
    ```

Make sure you have PHP, Composer, and Laravel installed on your machine before running the commands.

# API Documentation

### 1. User Registration
- **URL**: `http://127.0.0.1:8000/api/register`
- **Method**: POST
- **Payload**:
  ```json
  {
    "name": "Test",
    "email": "test@example.com",
    "password": "12345678"
  }
  ```

### 2. Login Request
