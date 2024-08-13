# Energy Pricing Management

## Description

This project simulates the calculation of total energy usage and total price over a specific period. It incorporates various pricing models based on periods, seasons, holidays, peak hours, and other factors. The framework is designed to handle complex pricing scenarios for energy consumption, ensuring accurate and flexible calculations.

## Installation

### Prerequisites

- PHP >= 8.0
- Composer
- MySQL or PostgreSQL

### Clone the Repository

```bash
git clone https://github.com/mubarok-iqbal/energy-rate-management-system.git
cd energy-rate-management-system
```

### Install Dependencies

```bash
composer install
```

### Environment Configuration

Create a .env file from the .env.example

```bash
cp .env.example .env
```

Set up your database configuration in the .env file. Update the following variables with your database credentials:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

### Generate the application key:

```bash
php artisan key:generate
```

Update your .env file with the necessary environment configurations, including database settings, mail settings, and any other required configuration.

### Migrate and Seed the Database

Run migrations to create the database schema:

```bash
php artisan migrate
```

Optionally, run database seeds to populate the database with initial data:

```bash
php artisan db:seed
```

### Run the Application

Start the Laravel development server:

```bash
php artisan serve
Visit http://localhost:8000 in your browser to view the application.
```


### Accessing the Application

Once the application is running, you can access it through your web browser at http://localhost:8000.


## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


## Contact
For questions or support, please contact Muhammad Iqbal Mubarok [mubarok.iqbal@gmail.com](mailto:mubarok.iqbal@gmail.com).


