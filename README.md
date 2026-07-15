# NetoCar

NetoCar is a Laravel SaaS application created to help car wash agencies manage their daily operations from one place.

It brings together reservations, customers, employees, services, tickets, payments, reports, and branch activity in a single dashboard.

## Main Features

- Dashboard with agency statistics
- Reservation management
- Ticket and service status tracking
- Customer, employee, branch, and service management
- CSV data import with validation
- Payment tracking and report exports
- Role-based access for managers and staff
- Public demo mode with fictitious data
- Responsive landing page and authentication interface

## Tech Stack

**Backend:** Laravel, PHP, Laravel Fortify, MySQL  
**Frontend:** Livewire, Flux, Tailwind CSS, JavaScript  
**Tools:** Vite, Composer, npm, Git, GitHub

## What I Worked On

I worked on both the backend and frontend of the application.

I built the dashboard, reservation and ticket workflows, customer and employee management, branch and service management, payments, reports, role-based access, CSV import, and the public demo mode.

I also designed the landing page and authentication interface.

## Main Challenges

### Role-based access

Managers and staff do not have the same responsibilities, so I created separate permissions and interfaces for each role using Laravel middleware.

### Agency data isolation

Each agency must only access its own customers, reservations, tickets, and employees. I used scoped queries and tenant middleware to keep agency data separated.

### Public demo protection

Visitors should be able to explore the application without deleting or changing the demo data. I added public demo accounts with fictitious data and read-only protection.

### CSV import

Imported data can contain missing or invalid rows. I added validation, preview, and error feedback before saving the records.

## Demo Accounts

The demo accounts use fictitious data and are protected in read-only mode.


Manager: demo.manager@netocar.test
Staff: demo.staff@netocar.test
Password: Demo@2026!

### screenshots 

###Landing page

<img width="2504" height="1316" alt="Screenshot 2026-07-15 193907" src="https://github.com/user-attachments/assets/0ed0c777-d24a-4b55-9ad6-c9d8a172fd84" />

<img width="2521" height="1342" alt="Screenshot 2026-07-15 194028" src="https://github.com/user-attachments/assets/83979197-47d5-455d-899c-0d37dac1af24" />

<img width="2534" height="1349" alt="Screenshot 2026-07-15 194101" src="https://github.com/user-attachments/assets/a5c3aafe-714a-419f-834e-59079b58344d" />

<img width="2532" height="1344" alt="Screenshot 2026-07-15 194147" src="https://github.com/user-attachments/assets/921d64a8-ad5d-4668-94e0-dbfbd0c4e356" />

<img width="2523" height="1421" alt="Screenshot 2026-07-15 195508" src="https://github.com/user-attachments/assets/849567b7-064b-441d-b31b-3d97b7d766ba" />

<img width="2528" height="1368" alt="Screenshot 2026-07-15 194214" src="https://github.com/user-attachments/assets/c53a8f3d-e667-47b5-b364-538a57f87799" />

## Login inteface

<img width="2542" height="1368" alt="Screenshot 2026-07-15 194308" src="https://github.com/user-attachments/assets/0a79f30a-68ce-4950-a9b6-b7b98048ba49" />

## Manager account 

<img width="2495" height="1359" alt="Screenshot 2026-07-15 194437" src="https://github.com/user-attachments/assets/88f6b73a-9b97-4d0a-b605-e178fbe729f2" />

<img width="2511" height="1347" alt="Screenshot 2026-07-15 194511" src="https://github.com/user-attachments/assets/056c7cef-75a4-4c8c-94e1-260cd7b1ebb5" />

<img width="2492" height="1445" alt="Screenshot 2026-07-15 194531" src="https://github.com/user-attachments/assets/789f2e21-8153-418c-97c0-5a8ef88e4474" />

## Billing
<img width="2028" height="1433" alt="Screenshot 2026-07-15 194737" src="https://github.com/user-attachments/assets/8a5a6944-95c8-4a24-96a7-5578e78770ee" />

## Import center 
<img width="2051" height="1242" alt="Screenshot 2026-07-15 194759" src="https://github.com/user-attachments/assets/688822e8-d124-4b32-9313-55316730fda1" />

## Stats
<img width="2054" height="1258" alt="Screenshot 2026-07-15 194834" src="https://github.com/user-attachments/assets/699bb247-3bf7-4966-be10-9355d33ed4d8" />

## Employee account  
<img width="2544" height="1431" alt="Screenshot 2026-07-15 195011" src="https://github.com/user-attachments/assets/b6392c7e-465a-402f-ab27-0a977f287aa3" />

<img width="2003" height="1387" alt="Screenshot 2026-07-15 195209" src="https://github.com/user-attachments/assets/d531abc4-ac00-43a9-96d3-1b6e1f9ad53e" />

<img width="2089" height="1331" alt="Screenshot 2026-07-15 195227" src="https://github.com/user-attachments/assets/6f98e114-1467-4d4c-9368-40750e931d20" />






