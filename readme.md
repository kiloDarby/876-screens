# 🎬 876 Screens

876 Screens is a web-based cinema booking system developed as a student project. The platform allows users to browse movies, view schedules, and purchase tickets (simulation), while administrators and supervisors manage movies, users, and showtimes.

The system follows a mobile-first design approach, ensuring accessibility and usability across a wide range of devices, particularly smartphones.

876 Screens is inspired by real-world online cinema platforms such as AMC Theatres and Palace Amusement, and serves as a Jamaican-focused spinoff tailored to local context and usage.

This project was built to simulate a real-world full-stack web application using PHP, MySQL, and JavaScript, with an emphasis on structured development, role-based access control, and practical system design.

---

## 📌 Project Overview

The goal of this project is to design and develop a **cinema management and ticket booking system** that includes:

- Role-based access control
- Movie scheduling system
- Ticket booking functionality
- Admin and supervisor management tools

The system is designed with a **mobile-first approach** and follows a structured folder architecture for scalability.

---

## 👥 User Roles

The system includes three main roles:

### 👤 User
- Register and login
- Browse movies
- View schedules (Today / This Week / This Month)
- Book tickets

### 🧑‍💼 Supervisor
- Manage movies
- Schedule showtimes

### 🛠️ Admin
- Full system control
- Manage users
- Create supervisors
- Manage and schedule movies

---

## ✨ Features

- 🔐 Authentication system (Login/Register)
- 🎥 Movie management (Create, Edit, Delete)
- 🕒 Showtime scheduling system
- 🎟️ Ticket booking system
- 📅 Dynamic movie schedule (Today / Weekly / Monthly)
- 🧑‍💼 Role-based dashboards
- 📱 Mobile-first responsive design
- 🎨 Custom UI styled with CSS

---

## 🛠️ Tech Stack

**Frontend:**
- HTML5
- CSS3 (Mobile-first design)
- JavaScript (Vanilla JS)

**Backend:**
- PHP (Procedural + modular structure)

**Database:**
- MySQL

**Tools:**
- VS Code
- Git & GitHub
- XAMPP / Localhost environment

---

## 📁 Project Structure
```text
876-SCREENS/
│
├── admin/                         # Admin & Supervisor pages (restricted access)
│   ├── create_user.php
│   ├── manage_movie.php
│   ├── manage_movies.php
│   └── manage_users.php
│
├── app/                           # Core application logic (backend)
│   ├── handlers/                  # Request handlers (process form submissions, DB actions)
│   │   ├── create_user_handler.php
│   │   ├── homepage_handler.php
│   │   ├── login_handler.php
│   │   ├── manage_movie_handler.php
│   │   ├── manage_movies_handler.php
│   │   ├── manage_users_handler.php
│   │   ├── profile_handler.php
│   │   ├── register_handler.php
│   │   └── schedule_handler.php
│   │
│   ├── auth.php                   # Authentication & role checks
│   ├── functions.php             # Global helper functions (URL, redirect, sanitize, etc.)
│   └── session.php               # Session management
│
├── assets/                        # Frontend assets
│   ├── css/
│   │   ├── style.css             # Main styles
│   │   └── typography.css        # Typography system (fonts, sizes)
│   │
│   ├── js/
│   │   ├── register.js           # Registration form validation
│   │   └── script.js             # Global scripts
│   │
│   └── images/                   # Static images
│       ├── favicon/
│       ├── chevron-down.svg
│       ├── hero-banner.jpg
│       └── logo.png
│
├── config/                        # Configuration files
│   ├── app.php                   # App-level settings (base URL, constants)
│   └── db.php                    # Database connection (PDO)
│
├── partials/                      # Reusable UI components
│   ├── flash_messages.php        # Success/error alerts
│   ├── head.php                  # <head> section
│   ├── hero.php                  # Hero/banner section
│   ├── modals.php                # Reusable modals (booking, confirm, etc.)
│   ├── page_header.php           # Navigation/header
│   ├── page_footer.php           # Footer
│   └── scripts.php               # JS includes
│
├── public/                        # Public-facing pages (entry points)
│   ├── index.php                 # Homepage
│   ├── about.php
│   ├── contact.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   ├── profile.php
│   ├── schedule.php
│   ├── forgot_password.php
│   └── get_movie_showtimes.php   # AJAX endpoint (fetch showtimes)
│
├── uploads/                       # User-uploaded content
│   ├── avatars/                  # Profile images
│   └── movie_posters/            # Movie poster uploads
│
├── sql/                           # Database scripts & backups
│   └── (schema, seed files, etc.)
│
├── .vscode/                       # VS Code settings (optional)
├── favicon.ico                    # Site favicon
└── README.md                      # Project documentation
```
---

## ⚙️ How to Run the Project

### 1. Clone the Repository

git clone https://github.com/yourusername/876-screens.git


### 2. Move to Local Server
Place the project inside your server directory:
- XAMPP → `htdocs/`
- WAMP → `www/`

### 3. Setup Database
- Open **phpMyAdmin**
- Create a database (e.g. `876_screens`)
- Import the SQL file from: `/sql/`


### 4. Configure Database Connection
Update: `config/db.php`


Set your:
- Database name
- Username
- Password

### 5. Run the Project
Open in browser: http://localhost/876-screens/public/


---

### 🔐 Initial Admin Setup

On the first run of the system, a default administrator account is available to allow access to the admin dashboard:

Username: admin@mail.com
Password: admin101

### ⚠️ Important:
For security reasons, it is strongly recommended to log in immediately and change this password to a more secure one after first access.

### Failure to update the default credentials may expose the system to unauthorized access.

---


## 🧠 Key Design Decisions

- **Separation of concerns**
  - UI (public/)
  - Logic (app/)
  - Config (config/)

- **Handler-based processing**
  - All form submissions handled in `/app/handlers/`

- **Role-based system**
  - Simplifies permission control

- **Showtime-based pricing**
  - Ticket prices stored per showtime instead of movie for flexibility

---

## ⚠️ Limitations

- No online payment integration (simulation only)
- No seat selection system
- Basic validation (can be improved)
- Limited security (for learning purposes)

---

## 🚀 Future Improvements

- 💳 Payment gateway integration
- 🎟️ Seat selection system
- 📧 Email ticket confirmation
- 🔐 Stronger authentication & security
- 🌐 API integration (for movie data)

---

## 📚 Learning Outcomes

Through this project, I gained experience in:

- Full-stack web development
- Database design and relationships
- Authentication and user roles
- Structuring scalable projects
- Debugging and handling real-world issues

---

## 👨‍🎓 Author

**Kevin Darby** 