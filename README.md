# QueueLess

**Multi-Service Digital Queue Management System**
Green University of Bangladesh — Department of CSE — Web Programming Lab Project

QueueLess lets students take a virtual queue token for university services (Accounts Office, Library, CSE Department Office, Computer Lab) instead of waiting in a physical line. Students track their live position, and admins manage each queue from a dedicated dashboard.

## Features

- Student registration & login with hashed passwords (bcrypt)
- Live dashboard — updates automatically every 5 seconds, no manual refresh
- One active token per student at a time, enforced server-side
- Live queue tracking (position, now serving, estimated wait) via Ajax (XMLHttpRequest)
- Token cancellation while still waiting
- Full token history per student
- Multi-admin support — each admin is locked to exactly one service
- Live admin dashboard — call next, complete, skip, reset queue, all real-time
- Fully responsive design (mobile, tablet, desktop)

## Tech Stack

HTML, CSS, JavaScript, Ajax (XMLHttpRequest), PHP (procedural), MySQL, XAMPP

## Setup

1. Clone this repo into your XAMPP `htdocs` folder
2. Import the database schema (see `/docs` or ask for `schema.sql`)
3. Start Apache and MySQL in XAMPP
4. Visit `http://localhost/QueueLess/`

## Project Structure

```
QueueLess/
├── index.php, login.php, register.php, dashboard.php, queue.php, history.php, logout.php
├── admin/          - admin login, dashboard, queue control actions
├── ajax/           - live polling endpoints (queueStatus.php, adminStatus.php)
├── includes/       - shared db connection + shared service list
├── css/            - one shared common.css + per-page stylesheets
├── images/         - logo, icons, favicon
└── OPERATING_GUIDE.md - full usage guide for students & admins
```

## Author

Built by Sabbir Hossain Osmani as an individual Web Programming Lab project.
