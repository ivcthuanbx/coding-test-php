## Get Started

This guide will walk you through the steps needed to get this project up and running on your local machine.

### Prerequisites

Before you begin, ensure you have the following installed:

- Docker
- Docker Compose

### Building the Docker Environment

Build and start the containers:

```
docker-compose up -d --build
```

### Installing Dependencies

```
docker-compose exec app sh
composer install
```

### Database Setup

Set up the database:

```
bin/cake migrations migrate
```
```
bin/cake migrations seed
```

### Accessing the Application

The application should now be accessible at http://localhost:34251

## How to check

### Authentication
I also create User Seed data for testing.
- user1@mail.com/123456
- user2@mail.com

All endpoint is attached in root folder *"php-dev-test.postman_collection.json"*

**Login**
- POST: http://localhost:34251/login
- Form data: email & password; Eg: email: user1@mail.com; password: 123456

**Logout**
- POST: http://localhost:34251/logout

### Article Management
**Create Article**
- Require: need to Login first.
- Post: http://localhost:34251/articles/add
- Form data (refer on Postman Collection)

**List all articles**
- Get: http://localhost:34251/articles

**Article detail**
- Get: http://localhost:34251/articles/{id}

**Update Article**
- Require: Login first
- Put: http://localhost:34251/articles/edit/{id}
- Form data (refer on Postman Collection)

**Delete Article**
- Require: need to Login first.
- Delete: http://localhost:34251/articles/{id}
- Form data (refer on Postman Collection)

### Like Feature
***
I created new table "article_likes" to save information as: which user liked article
***
**Like an Article**
- Require: need to Login first.
- Post: http://localhost:34251/articles/like/{id}
