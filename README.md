_# Job Board API
This repository contains a REST API built with Symfony for a job board. The API includes functionality for user authentication, company and job management, job search and filtering, applicant management, and job application management. The API also includes reporting functionality, allowing clients to view lists of applicants for a job posting and lists of jobs applied for by an applicant.

## Requirments

* PHP 8.1 or higher
* Composer
* XAMPP
* Symfony CLI 

## Instalation

* Clone or download the source code for the REST API from the GitHub repository.
* Run composer install in the project directory to install the required dependencies.
* Create a new MySQL database 
* Create a file .env.local and add your database connection ```DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app4"```
* Create the tables with the Symfony CLI ```php bin/console doctrine:migrations:migrate```
* Run the application with the Symfony CLI ```symfony server:start```
* Generate the JWT security with Symfony CLI ```bin/console lexik:jwt:generate-keypair```
* You can use any REST API client, such as Postman or Insomnia, to interact with the API

## Entities and Endpoints

The entities included in the API are:

### User
Includes default user fields generated by Symfony.

### Company
Includes name, description, location, job posts, and contact information.
* POST /api/v1/companies: Create a company
* GET /api/v1/companies: Get all companies
* GET /api/v1/companies/{id}: Get a company by ID
* PUT /api/v1/companies/{id}: Update a company by ID
* DELETE /api/v1/companies/{id}: Delete a company by ID

### Job
Includes title, description, required skills, experience, applicants, company, and other relevant details.
* POST /api/v1/jobs: Create a job
* GET /api/v1/jobs/{id}: Get a job by ID
* GET /api/v1/jobs: Get jobs with optional filter parameters
* PUT /api/v1/jobs/{id}: Update a job by ID
* DELETE /api/v1/jobs/{id}: Delete a job by ID

### Applicant
Includes name, contact information, job preferences, and jobs applied.
* POST /api/v1/applicants: Create an applicant
* GET /api/v1/applicants: Get all applicants
* GET /api/v1/applicants/{id}: Get an applicant by ID
* PUT /api/v1/applicants/{id}: Update an applicant by ID
* DELETE /api/v1/applicants/{id}: Delete an applicant by ID

### JobApplication
Includes applicant and job applied.
* POST /api/v1/jobs_applications: Create a Job Application
* GET /api/v1/jobs_applications: Get all Job Applications
* GET /api/v1/jobs_applications/{id}: Get a Job Application by ID
* GET /api/v1/jobs_applications/filter-by-applicant/{applicantId}: Filter Job Applications by Applicant ID.
* GET /api/v1/jobs_applications/filter-by-job/{jobId}: Filter Job Applications by Job ID.
* PUT /api/v1/jobs_applications/{id}: Update a Job Application by ID
* DELETE /api/v1/jobs_applications/{id}: Delete a Job Application by ID

## API Documentation
The API documentation is generated using the Swagger PHP library and is available at http://localhost:8000/api/doc. The documentation includes details on the available endpoints, parameters, and responses.

