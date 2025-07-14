# Vendor Management System v2

A full-featured web-based Vendor Management System built with **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**.  
Version 2 introduces a **clear separation between frontend and backend**, improved modularity, and better user experience.

---

## 🚀 Features

### 🔐 Authentication
- User login/logout with session handling
- Role-based access control (Vendor & Admin)

### 🧑‍💼 Vendor Profile
- Edit profile details (email, contact info)
- Upload profile images with size/type validation

### 📦 Product Management
- Add, edit, delete products
- View product lists by vendor

### 🛒 Purchase Management
- Track purchases and calculate totals
- Display transaction summaries

### 📂 Clean Project Structure
- Separated `frontend/` and `backend/` directories
- HTML templates use `{{placeholders}}` dynamically replaced by PHP

### ⚠️ Alert Handling
- Real-time feedback for success and error messages
- Simple UI alerts built with custom CSS

---

## 📁 Project Structure

```
vendormanagementsystem/
│
├── backend/
│   ├── connection/         # Database connection logic
│   ├── verify/             # Access control (verifyuser.php)
│   ├── vendor/             # Vendor-specific PHP logic
│   ├── admin/              # Admin-side backend (future ready)
│   └── uploads/            # Uploaded images
│
├── frontend/
│   ├── body/               # Main UI components & styles
│   ├── header/             # Header UI & nav
│   ├── vendor/             # Vendor HTML templates
│   ├── admin/              # Admin templates (optional/future)
│   └── assets/             # CSS and images
│
├── index.html              # Entry point / landing page
└── README.md
```

---

## 🛠️ Technologies Used

- **Backend:** PHP 8+, MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Database:** MySQL (with sample schema)
- **Hosting:** Local (WAMP/XAMPP recommended)

---

## 🧪 Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/dsamithmendis/vendormanagementsystem.git
   ```

2. **Set Up the Database**
   - Create a MySQL database (e.g., `vendor_db`)
   - Import the provided SQL schema (coming soon or manually created)

3. **Configure Connection**
   - Edit `backend/connection/connect.php` with your DB credentials

4. **Run the App**
   - Place the project in your web server's root (e.g., `htdocs/`)
   - Start Apache & MySQL using XAMPP or WAMP
   - Visit `http://localhost/vendormanagementsystem/frontend/vendor/index.html`

---

## 📸 Screenshots (Coming Soon)
- Dashboard UI
- Profile Edit Form
- Product Listing
- Success/Error Alerts

---

## 🤝 Contributing

Contributions are welcome!  
Feel free to submit issues, suggest improvements, or fork the repo to extend functionality.

---

## 📄 License

This project is open-source and free to use for educational or personal use. Contact me for commercial licensing.

---

## 🙋‍♂️ Author

**Samith Mendis**  
📫 [Connect on LinkedIn](https://linkedin.com/in/dsamithmendis)  
🔗 [View Repository](https://github.com/dsamithmendis/vendormanagementsystem)