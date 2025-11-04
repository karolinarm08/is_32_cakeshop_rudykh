Інтернет-магазин кондитерської "Ruby" (ІС-32)

Опис проекту:
Проект є веб-системою "Інтернет-магазин кондитерської", яка дозволяє клієнтам здійснювати онлайн-замовлення продукції, а адміністраторам - управляти товарами та замовленнями.

Поточний стан:
Frontend: Реалізовано інтерактивний HTML/CSS/JS прототип.
Backend: Створено базову структуру коду (скелет) для PHP-додатку.

Структура проекту:
Проект має наступну структуру папок, яка відокремлює frontend-прототип від backend-логіки:

is_32_cakeshop_rudykh/
│
├── .gitignore
├── README.md
│
├── .vscode/
│   └── settings.json
│
├── backend/
│   └── src/
│       │
│       ├── Controllers/
│       │   ├── AuthController.php
│       │   ├── OrderController.php
│       │   └── ProductController.php
│       │
│       ├── Models/        
│       │   ├── Address.php
│       │   ├── AdminUser.php
│       │   ├── Cart.php
│       │   ├── CartItem.php
│       │   ├── Category.php
│       │   ├── Image.php
│       │   ├── Order.php
│       │   ├── OrderItem.php
│       │   ├── Payment.php
│       │   ├── Product.php
│       │   ├── Shipment.php
│       │   └── User.php
│       │
│       ├── Repositories/
│       │   ├── OrderRepository.php
│       │   ├── ProductRepository.php
│       │   └── UserRepository.php
│       │
│       └── Services/
│           ├── AuthService.php
│           ├── OrderService.php
│           └── PaymentService.php
│
├── frontend/
│   ├── about_us.html
│   ├── account.html
│   │── admin_panel.html
│   ├── contacts.html
│   ├── footer.html
│   ├── header.html
│   ├── index.html
│   ├── info.html
│   ├── menu.html
│   ├── news.html
│   ├── news_item.html
│   ├── order.html
│   ├── product.html
│   ├── product_change.html
│   ├── shoppingcart.html
│   │
│   ├── code.js
│   └── styles.css
│   │
│   │
│   │
│   └── image/
│
│
└── tests/


Ролі та розподіл завдань:
Ролі розподілені відповідно до основних бізнес-процесів та архітектури SOLID:

Лємаєва О.М. - Full-stack розробник
Функціонал User та Authentication. Створення базової структури для AuthController (прийом запитів), AuthService (бізнес-логіка реєстрації/входу), UserRepository (робота з базою даних) та пов'язаних моделей (User, AdminUser, Address).

Рудих К.О. - Full-stack розробник
Функціонал Product та Order. Це включає створення базової структури для ProductController, OrderController (прийом запитів), OrderService, PaymentService (бізнес-логіка каталогу, кошика, оформлення замовлення), ProductRepository.

Філіпович Д.О. - Тестувальник (QA)
Відповідає за створення та налаштування тестового оточення (папка tests/). У майбутньому відповідатиме за написання unit-тестів для перевірки коректності роботи всієї створеної бізнес-логіки та моделей даних. А також створено функціонал OrderRepository (робота з БД) та всіх пов'язаних моделей (Product, Category, Image, Cart, CartItem, Order, OrderItem, Payment, Shipment)

Інструкції для запуску:

Backend (PHP):
Потрібен локальний сервер (XAMPP) з підтримкою PHP та MуSQL.
Виконати composer install в папці backend/ (після додавання composer.json).
Налаштувати підключення до бази даних в backend/config/Database.php.

Frontend (HTML/CSS):
Відкрити файли .html у будь-якому браузері.