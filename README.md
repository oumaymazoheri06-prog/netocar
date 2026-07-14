# NetoCar

NetoCar is a Laravel SaaS demo for managing car wash agencies.

It centralizes reservations, clients, employees, services, tickets, payments, reports and branch operations in one dashboard.

## Features

- Agency dashboard with business statistics
- Reservation management
- Ticket workflow tracking
- Data import
- Client, employee, branch and service management
- Payments and report exports
- Role-based access for manager and staff
- Demo mode for portfolio visitors
- Modern landing page and authentication UI

## Tech Stack

- Laravel
- Laravel Fortify
- Livewire / Flux
- Tailwind CSS
- JavaScript
- MySQL
- Vite

## Demo Accounts

The demo environment includes public test accounts with fictitious data.

Manager account:

```txt
demo.manager@netocar.test
```

Staff account:

```txt
demo.staff@netocar.test
```

Password:

```txt
Demo@2026!
```

Demo accounts use fictitious data and are protected in read-only mode, so visitors can explore the product without damaging the demo dataset.

## Challenges & Solutions

### Multi-role SaaS access

The application needed to support different user responsibilities inside the same agency: manager, staff and admin-like access.

Solution: role-based access was implemented around Laravel middleware and dedicated dashboard experiences, so each user only sees the actions and data that match their role.

### Agency data isolation

Because the project simulates a SaaS product, agency data must stay separated. A user from one agency should not access reservations, tickets, clients or employees from another agency.

Solution: tenant isolation middleware and scoped queries were added to keep operational data attached to the authenticated user's agency.

### Public portfolio demo

For a public portfolio, giving real manager credentials would be risky and messy because visitors could modify or delete demo data.

Solution: a demo mode was added with public demo accounts, seeded fictitious data and a read-only protection layer that blocks modification requests for demo users.

### Authentication and expired pages

During login testing, the app sometimes showed a "Page Expired" screen because old CSRF tokens could be reused from cached auth pages.

Solution: authentication views were adjusted to use fresh server-rendered forms, CSRF metadata was added globally, and no-cache headers were applied to Fortify auth pages.

### Import validation

CSV imports needed to be useful without silently creating bad records.

Solution: imports were designed with validation, preview behavior and invalid-row feedback so valid rows can be confirmed while problematic rows are ignored or corrected.

### Landing page clarity

The landing page had to explain a complex operational product without feeling childish or overloaded.

Solution: the page was redesigned with a premium SaaS style, clearer contrast, before/after storytelling, animated but lightweight interactions and stronger calls to action.

## Local Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## Demo Data

To seed demo data:

```bash
php artisan db:seed --class=DemoSeeder
```

## Author

Built by Oumayma Zoheri as a  SaaS project.
